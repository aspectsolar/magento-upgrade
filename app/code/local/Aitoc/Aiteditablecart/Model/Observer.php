<?php
/**
 * Shopping Cart Editor
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aiteditablecart
 * @version      2.1.2
 * @license:     sJyHIXeWlUU1XYytqPDfVrCdIMO1jSQ6obarjJbRep
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aiteditablecart_Model_Observer
{
    /**
	 * @refactor Split by logical parts
	 */
    public function processCartUpdateBefore($observer)
    {
        $cart = $observer->getEvent()->getCart();
        $data = $observer->getEvent()->getInfo();

        $optionsData = array();
        $supAttrData = array();
        $bundOptData = array();
        $bundQtyData = array();
        $downlodData = array();
        $qtyData = array();
        $aitProductsListsData = array();
        $allowDelete =true;
        $hasOptions = false;
        $productIds = array();
        $errors = array();

        foreach ($data as $itemId => $itemInfo) {
            if (!$this->_cartItemsWithoutOptions($itemInfo)) {
                $hasOptions = true;
            }
        }
        if(!$hasOptions) return true;

        foreach ($data as $itemId => $itemInfo) {
            $notGrouped = true;
            $item = $cart->getQuote()->getItemById($itemId);

            if (!$this->_cartItemsWithoutOptions($itemInfo)) {
                if ($item) {
                    try {
                        $qtyData = $this->_setQtyData($qtyData, $itemId, $itemInfo);
                        $this->_checkQty($item, $itemId, $qtyData);
                        foreach($item->getProduct()->getOptions() as $_option){
                            $userValues = $itemInfo['cart_options'];
                            if (isset($userValues[$_option->getId()])) {
                                $userValues[$_option->getId()] = is_array($userValues[$_option->getId()]) ? array_filter($userValues[$_option->getId()]) : $userValues[$_option->getId()];
                                $_option->groupFactory($_option->getType())
                                    ->setOption($_option)
                                    ->setProduct($item->getProduct())
                                    ->setProcessMode("full")
                                    ->validateUserValue($userValues);
                            }
                        }
                    }
                    catch(Mage_Core_Exception $e) {
                        $errors[] = $e->getMessage();
                        $allowDelete = false;
                    }

                    $productId = $item->getProductId();
                    $aitProductsListsData = $this->_setAitProductListOptions($aitProductsListsData, $item);

                    if (!Mage::registry('ait_cart_edit')) { // fix for Aiteditablecart module
                        Mage::register('ait_cart_edit', true); // fix for AdjustWare_Giftreg module
                    }
                }
                else {
                    $productId = $itemInfo['cart_product_id'];
                }

                $optionsData = $this->_setOptionsData($optionsData, $itemInfo, $itemId, 'cart_options');
                $supAttrData = $this->_setOptionsData($supAttrData, $itemInfo, $itemId, 'super_attribute');
                $bundOptData = $this->_setOptionsData($bundOptData, $itemInfo, $itemId, 'bundle_option');
                $bundQtyData = $this->_setOptionsData($bundQtyData, $itemInfo, $itemId, 'bundle_option_qty');
                $downlodData = $this->_setOptionsData($downlodData, $itemInfo, $itemId, 'downloadable_links');
            } 
            elseif (!empty($itemInfo['qty'])) {
                if ($item) {
                    if($item->getProductType() != 'grouped') {
                        $qtyData = $this->_setQtyData($qtyData, $itemId, $itemInfo);
                        try {
                            $this->_checkQty($item, $itemId, $qtyData);
                        }
                        catch(Mage_Core_Exception $e) {
                            $errors[] = $e->getMessage();
                            $allowDelete = false;
                        }
                        $productId = $item->getProductId();
                        $aitProductsListsData = $this->_setAitProductListOptions($aitProductsListsData, $item);
                    }
                    else {
                        $notGrouped = false;
                    }
                }
            }
            if($notGrouped) {
                $productIds[$itemId] = $productId;
            }    
        }

        if($allowDelete) {
            foreach ($data as $itemId => $itemInfo) {
                $item = $cart->getQuote()->getItemById($itemId);
                if($item->getProductType() != 'grouped') {
                    Mage::dispatchEvent('checkout_cart_remove_items_before', array('item' => $item));
                    $cart->getQuote()->removeItem($itemId);
                }
            }
        }
        else {
            Mage::throwException(implode("\n", $errors));
        }
        
        foreach ($productIds as $itemId => $productId) {
            if ($productId) {
                $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load($productId);

                if ($product->getId()) {
                    $aitProductsListsListId = empty($aitProductsListsData[$itemId]) ? null : $aitProductsListsData[$itemId];
                    $product->setIsAitocProductList($aitProductsListsListId);

                    $aRequestOptions = $this->_setRequestOptionsData($optionsData, $itemId);
                    $aRequestSupAttr = $this->_setRequestData($supAttrData, $itemId);
                    $aRequestBunData = $this->_setRequestData($bundOptData, $itemId);
                    $aRequestBundQty = $this->_setRequestData($bundQtyData, $itemId);
                    $aRequestDownlod = $this->_setRequestData($downlodData, $itemId);

                    if (!empty($qtyData[$itemId]) && $allowDelete) {
                        $params = array(
                            'product'           => $productId,
                            'qty'               => $qtyData[$itemId],
                            'options'           => $aRequestOptions,
                            'super_attribute'   => $aRequestSupAttr,
                            'bundle_option'     => $aRequestBunData,
                            'bundle_option_qty' => $aRequestBundQty,
                            'links'             => $aRequestDownlod,
                        );
                            
                        if (Mage::registry('aitoc_cart_options_item')) {
                            Mage::unregister('aitoc_cart_options_item');
                        }
                            
                        $item = $cart->getQuote()->getItemById($itemId);
                        Mage::register('aitoc_cart_options_item', $item);

                        Mage::dispatchEvent('aitoc_editablecart_product_add', array('product'=>$product, 'cart' => $cart));
                        $cart->addProduct($product, $params);
                    }

                    if ( (version_compare(Mage::getVersion(),'1.5.0.0','>') &&  version_compare(Mage::getVersion(),'1.7.0.0','<')) )
                    {
                        $item = $cart->getQuote()->getItemByProduct($product);
                        Mage::dispatchEvent('checkout_cart_remove_items_before', array('item'=>$item));
                    }
                }
            }
        }
    }

    /**
     * Collect specific type options for quote item
     * @param array $dataArray array of options
     * @param array $itemInfo quote item data
     * @param int $itemId quote item id
     * @param string $type type of item options
     * @return array
     */
    private function _setOptionsData($dataArray, $itemInfo, $itemId, $type)
    {
        if (!empty($itemInfo[$type])) {
            $dataArray[$itemId][] = $itemInfo[$type];
        }
        return $dataArray;
    }

    /**
     * Set requested custom options for quote item
     * @param array $optionsData custom options for quote item
     * @param int $itemId quote item id
     * @return array
     */
    private function _setRequestOptionsData($optionsData, $itemId)
    {
        $requestOptionsData = array();
        if (!empty($optionsData[$itemId])) {
            foreach ($optionsData[$itemId] as $aData) {
                if ($aData) {
                    foreach ($aData as $iOptionId => $mValue) {
                        if ($mValue and is_array($mValue) AND isset($mValue[0]) AND $mValue[0] == 0) {
                            unset($mValue[0]);
                        }

                        if ($mValue != array(0 => 0)) {
                            $requestOptionsData[$iOptionId] = $mValue;
                        }
                    }
                }
            }
        }
        return $requestOptionsData;
    }

    /**
     * Set requested options for quote item
     * @param array $dataArray specific type options for quote item
     * @param int $itemId quote item id
     * @return array
     */
    private function _setRequestData($dataArray, $itemId)
    {
        $requestData = array();
        if (!empty($dataArray[$itemId])) {
            foreach ($dataArray[$itemId] as $aData) {
                if ($aData) {
                    foreach ($aData as $iOptionId => $sValue) {
                        $requestData[$iOptionId] = $sValue;
                    }
                }
            }
        }
        return $requestData;
    }

    /**
     * Check if quote items have any options
     * @param array $itemInfo quote item data
     * @return bool
     */
    private function _cartItemsWithoutOptions($itemInfo)
    {
        return (empty($itemInfo['cart_options']) && empty($itemInfo['super_attribute']) && empty($itemInfo['bundle_option']) && empty($itemInfo['downloadable_links']));
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     * @param array $productsListsData array of product lists options
     * @return array
     */
    private function _setAitProductListOptions($productsListsData, $item)
    {
        $aitProductsListsOption = $item->getOptionByCode('aitproductslists');
        $productsListsData[$item->getId()] = ($aitProductsListsOption instanceof Mage_Sales_Model_Quote_Item_Option) ? $aitProductsListsOption->getValue() : null;
        return $productsListsData;
    }

    /**
     * Set quantity info for quote item
     * @param array $qtyData array of items qty
     * @param int $itemId quote item id
     * @param array $itemInfo quote item data
     * @return array
     */
    private function _setQtyData($qtyData, $itemId, $itemInfo)
    {
        $qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
        $qtyData[$itemId] = $qty;
        return $qtyData;
    }
	
	private function _checkQty($item, $itemId, $qtyData)
    {
        $oldQty = $item->getQty();
        $item->setQtyToAdd($qtyData[$itemId]);
        $item->setQty($qtyData[$itemId]);

        if ($item->getHasError()) {
            $message = $item->getMessage();
            $item->setQtyToAdd($oldQty);
            $item->setQty($oldQty);
        }

        if (!empty($message)) {
            Mage::throwException($message);
        }
        return true;
    }
}