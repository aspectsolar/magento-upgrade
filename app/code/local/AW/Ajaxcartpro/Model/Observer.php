<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxcartpro
 * @version    3.2.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ajaxcartpro_Model_Observer
{
    public function beforeRenderLayout($observer)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if ($request->getParam('awacp', false)) {
            $layout = Mage::app()->getFrontController()->getAction()->getLayout();
            $response = Mage::getModel('ajaxcartpro/response');

            $parts = $request->getParam('block');
            if (is_array($parts)) {
                $actionData = Zend_Json::decode(stripslashes($request->getParam('actionData', '[]')));
                $renderer = Mage::getModel('ajaxcartpro/renderer')->setActionData($actionData);
                try {
                    $html = $renderer->renderPartsFromLayout($layout, $parts);
                    $response->setBlock($html);
                } catch(AW_Ajaxcartpro_Exception $e) {
                    $response->addError($e->getMessage());
                } catch(Exception $e) {
                    $response->addError($e->getMessage());
                    Mage::logException($e);
                }
            }
            $this->_sendResponse($response);
        }
    }

    public function sendResponseBefore($observer)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if ($request->getParam('awacp', false)) {
            $response = Mage::getModel('ajaxcartpro/response');
            $messages = $this->_getErrorMessages();
            if ( count($messages) > 0 ) {
                if ( $url = $this->_getRedirectUrl($response) ) {
                    $response->setRedirectTo($url);
                    $response->addMsg($messages);
                } else {
                    $response->addError($messages);
                }
            }
            $actionData = $this->_collectActionData();
            $response->setData('action_data', $actionData);

            $this->_sendResponse($response);
        }
    }

    public function loadLayoutBefore($observer)
    {
        $controllerAction = $observer->getAction();
        $layout = $observer->getLayout();
        //add wysiwyg on system config section
        if ($controllerAction->getFullActionName() === 'adminhtml_system_config_edit' &&
            $controllerAction->getRequest()->getParam('section', false) === 'ajaxcartpro') {
            $layout->getUpdate()->addHandle('editor');
        }
        //remove ACP from checkout (cart page is exception)
        if (
            strpos($controllerAction->getFullActionName(), 'checkout_') === 0 &&
            strpos($controllerAction->getFullActionName(), 'checkout_cart') === false
        ) {
            /**
             * compatibility with AW_Betterthankyoupage
             */
            if (Mage::helper('ajaxcartpro')->isExtensionEnabled('AW_Betterthankyoupage') && (
                    strpos($controllerAction->getFullActionName(), 'checkout_onepage_success') !== false ||
                    strpos($controllerAction->getFullActionName(), 'checkout_multishipping_success') !== false
                )
            ) {
                return;
            }
            $layout->getUpdate()->addHandle('remove_ajaxcartpro');
        }
    }

    //REMOVE FROM CART HACK!
    public function salesQuoteRemoveItem($observer)
    {
        $quoteItem = $observer->getQuoteItem();
        if (Mage::registry('awacp_removed_product_id')) {
            return;
        }
        Mage::register('awacp_removed_product_id', $quoteItem->getProductId());
    }


    public function checkoutCartProductAddAfter($observer)
    {
        $product = $observer->getProduct();
        //Check on need disaply non required options
        $this->_checkOnNonRequiredOptionsExists($product);

        //ADD TO CART HACK
        if (Mage::registry('awacp_added_product_id')) {
            return;
        }
        Mage::register('awacp_added_product_id', $product->getId());
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @throws Mage_Core_Exception
     * @return $this
     */
    protected function _checkOnNonRequiredOptionsExists(Mage_Catalog_Model_Product $product)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this;
        }
        if (!$request->getParam('awacp', false)) {
            return $this;
        }
        if ($request->getParam('awacp-options-form', false)) {
            return $this;
        }

        $promo = Mage::helper('ajaxcartpro/promo')->validate(
            $product->getId(), AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::ADD_VALUE
        );
        $isCanShowPopupOnlyForRequiredOptions = Mage::helper('ajaxcartpro/config')->getGeneralOptionsPopupDisplayWith();
        if (null !== $promo) {
            $isCanShowPopupOnlyForRequiredOptions = $promo->getOptionsRequiredOnly() == '1'?true:false;
        }
        if (null === Mage::registry('aw_acp_only_required_options_for_popup')) {
            Mage::register('aw_acp_only_required_options_for_popup', $isCanShowPopupOnlyForRequiredOptions);
        }
        if ($isCanShowPopupOnlyForRequiredOptions) {
            return $this;
        }

        if ($product->getProductOptionsCollection()->getSize() <= 0) {
            return $this;
        }
        Mage::getSingleton('checkout/session')->setUseNotice(true);
        throw new Mage_Core_Exception(
            Mage::helper('ajaxcartpro')->__('Please specify the product option(s)')
        );
    }

    private function _sendResponse($body)
    {
        $response = Mage::app()->getResponse();
        $response->clearBody();
        $response->setHttpResponseCode(200);
        //remove location header from response
        $headers = $response->getHeaders();
        $response->clearHeaders();
        foreach ($headers as $header) {
            if ($header['name'] !== 'Location') {
                $response->setHeader($header['name'], $header['value'], $header['replace']);
            }
        }
        $response->sendHeaders();
        echo $body->toJson();
        exit(0);
    }

    private function _getRedirectUrl()
    {
        $request = Mage::app()->getFrontController()->getRequest();
        $action = Mage::app()->getFrontController()->getAction();

        $isCanShowOptionsPopupWithoutRequiredOptions = !Mage::registry('aw_acp_only_required_options_for_popup');
        if ($action instanceof Mage_Checkout_CartController && $request->getActionName() === 'add') {
            $productId = (int)$request->getParam('product', false);
            if (!$productId) {
                return false;
            }
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$product->isGrouped() && !$product->getTypeInstance(true)->hasRequiredOptions($product)
                && !$isCanShowOptionsPopupWithoutRequiredOptions) {
                return false;
            }
            $url = Mage::helper('ajaxcartpro/catalog')->getProductUrl(
                $product, array('_query' => array('options' => 'cart'))
            );
            return $url;

        } else if ($action instanceof Mage_Wishlist_IndexController && $request->getActionName() === 'cart') {
            $itemId = (int)$request->getParam('item', false);
            if (!$itemId) {
                return false;
            }
            $item = Mage::getModel('wishlist/item')->load($itemId);
            $productId = $item->getProductId();
            if (!$productId) {
                return false;
            }
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$product->isGrouped() && !$product->getTypeInstance(true)->hasRequiredOptions($product)
                && !$isCanShowOptionsPopupWithoutRequiredOptions) {
                return false;
            }
            $url = Mage::getUrl('wishlist/index/configure', array('id' => $itemId));
            return $url;
        }

        return false;
    }

    private function _getErrorMessages()
    {
        $allMessages = array_merge(
            $this->_getErrorMessagesFromSession(Mage::getSingleton('checkout/session')),
            $this->_getErrorMessagesFromSession(Mage::getSingleton('wishlist/session')),
            $this->_getErrorMessagesFromSession(Mage::getSingleton('catalog/session'))
        );
        return $allMessages;
    }

    private function _getErrorMessagesFromSession($session)
    {
        $messages = $session->getMessages(true);
        $sessionMessages = array_merge(
            $messages->getItems(Mage_Core_Model_Message::ERROR),
            $messages->getItems(Mage_Core_Model_Message::WARNING),
            $messages->getItems(Mage_Core_Model_Message::NOTICE)
        );
        return $sessionMessages;
    }

    private function _collectActionData()
    {
        $actionData = array();
        $promo = null;
        if (!is_null(Mage::registry('awacp_removed_product_id'))) {
            $actionData['removed_product'] = Mage::registry('awacp_removed_product_id');
            $promo = Mage::helper('ajaxcartpro/promo')->validate(
                $actionData['removed_product'], AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::REMOVE_VALUE
            );
        } else if (!is_null(Mage::registry('awacp_added_product_id'))) {
            $actionData['added_product'] = Mage::registry('awacp_added_product_id');
            $promo = Mage::helper('ajaxcartpro/promo')->validate(
                $actionData['added_product'], AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::ADD_VALUE
            );
        }
        if (null !== $promo) {
            $actionData['confirmation_enabled'] = $promo->getData('show_dialog') == '1'?true:false;
            $actionData['counter_begin_from'] = (int)$promo->getData('close_dialog_after');
        }
        return $actionData;
    }

}
