<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.3.1
 * @license:     lMnQrjiQcxfPlbdwpFhsKslnzVPfVqO45IEZa4tVHs
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_RecoverController extends Mage_Core_Controller_Front_Action
{
    public function cartAction()
    {
        $code = (string) $this->getRequest()->getParam('code');
        $id   = (int) $this->getRequest()->getParam('id');
        
        $history = Mage::getModel('adjcartalert/history')->load($id);
        if (!$history->getId() || $history->getRecoverCode() != $code){
            $this->_redirect('/');
            return;
        }

        $s = Mage::getSingleton('customer/session');
        if ($s->isLoggedIn()){
            if ($history->getCustomerId() == $s->getCustomerId()){
                $this->_redirectToCart($history);
                return;
            }
            else 
                $s->logout();
        }
        // customer. login
        if ($history->getCustomerId()){
            $customer = Mage::getModel('customer/customer')->load($history->getCustomerId());
            if ($customer->getId())
                $s->setCustomerAsLoggedIn($customer);
        }
        
        $this->_redirectToCart($history);
    }
    
    /**
     * @param AdjustWare_Cartalert_Model_History $history
     */
    protected function _redirectToCart($history){
        if (!is_null($history)){
            $history->setRecoveredAt(now());
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $history->setRecoveredFrom($_SERVER['REMOTE_ADDR']);
            }
            if (Mage::getStoreConfig('catalog/adjcartalert/stop_after_visit')){
                $cartalert = Mage::getResourceModel('adjcartalert/cartalert')
                    ->cancelAlertsFor($history->getCustomerEmail());
            }
            $history->save();
        }
  
        Mage::dispatchEvent('adjustware_cartalert_cart_recovery', array('quote'=>Mage::getModel('sales/quote')->load($history->getQuoteId())));   

        $quoteId = $history->getQuoteId();
        if($history->getCustomerId() == 0){
            if($history->getQuoteIsActive() == 0){
                $orderId = Mage::getResourceModel('adjcartalert/history')->getOrderId($quoteId);
                $this->_reorderGuest($orderId);
                return;
            }

            $quote = Mage::getModel('sales/quote')->load($history->getQuoteId());
            if ($quote){
                Mage::getSingleton('checkout/session')->replaceQuote($quote);
            }

            $this->_redirect('checkout/cart');
            return;
        }
        
        if($history->getQuoteIsActive() == 0){
            $reorderUrl = Mage::getResourceModel('adjcartalert/history')->getReorderUrl($quoteId);
            $this->_redirect($reorderUrl);
        }

        $this->_redirect('checkout/cart');
        return;
    }

    /**
     * @param string | integer $orderId
     */
    protected function _reorderGuest($orderId)
    {        
        if(!$this->_canLoadOrder($orderId)){
            $this->_redirect('/');
            return;
        }

        $order = Mage::registry('current_order');
        $cart = Mage::getSingleton('checkout/cart');
        /* @var $cart Mage_Checkout_Model_Cart */

        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (Mage_Core_Exception $e){
                if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                    Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                }
                else {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
                $this->_redirect('/');
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e,
                    Mage::helper('checkout')->__('Cannot add the item to shopping cart.')
                );
                $this->_redirect('checkout/cart');
            }
        }
        $cart->save();
        $this->_redirect('checkout/cart');
    }

    /**
     * @param string | integer $orderId
     */
    protected function _canLoadOrder($orderId)
    {
        if (!$orderId) {
            $this->_redirect('/');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if(in_array($order->getState(), $availableStates, $strict = true)){
        Mage::register('current_order', $order);
        } else {
            $this->_redirect('/');
            return false;
        }
    }
}