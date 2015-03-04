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
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog inventory module observer
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Aitoc_Aiteditablecart_Model_Rewrite_CatalogInventoryObserver extends Mage_CatalogInventory_Model_Observer
{
    
    public function onRemoveItems($observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote()
            || $quoteItem->getQuote()->getIsSuperMode()) {
            return $this;
        }
        
        
        $quoteItemId = $quoteItem->getId();
        if ( ($options = $quoteItem->getQtyOptions()) ) {
            $stockItem = $quoteItem->getProduct()->getStockItem();
            
            foreach ($options as $option) {
                $stockItem = $option->getProduct()->getStockItem();
                
                $productId = $option->getProduct()->getId();
                
                if ( isset($this->_checkedQuoteItems[$productId]['qty']) && in_array($quoteItemId, $this->_checkedQuoteItems[$productId]['items']) ) {
                    unset($this->_checkedQuoteItems[$productId]);
                }
                if ( isset($this->_checkedProductsQty[$productId]) ) {
                    unset($this->_checkedProductsQty[$productId]);
                }
            }
        }else{
            $stockItem = $quoteItem->getProduct()->getStockItem();
            /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                Mage::throwException(Mage::helper('cataloginventory')->__('The stock item for Product is not valid.'));
            }
            
            $productId = $quoteItem->getProduct()->getId();
            
            if ( isset($this->_checkedQuoteItems[$productId]['qty']) && in_array($quoteItemId, $this->_checkedQuoteItems[$productId]['items']) ) {
                unset($this->_checkedQuoteItems[$productId]);
            }
            if ( isset($this->_checkedProductsQty[$productId]) ) {
                unset($this->_checkedProductsQty[$productId]);
            }
            

        }
        
    }
    
}