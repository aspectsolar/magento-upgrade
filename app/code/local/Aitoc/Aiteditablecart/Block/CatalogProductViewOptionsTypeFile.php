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

class Aitoc_Aiteditablecart_Block_CatalogProductViewOptionsTypeFile extends Mage_Catalog_Block_Product_View_Options_Type_File
{
    // override parent
    public function getFormatedPrice()
    {
        if ($option = $this->getOption()) {
$nPrice = $option->getPrice(true); // 1.4 fix
            return $this->_formatPrice(array(
                'is_percent' => ($option->getPriceType() == 'percent') ? true : false,
//                'pricing_value' => $option->getPrice(true)
                'pricing_value' => Mage::helper('aiteditablecart')->getOptionPrice($option->getProduct(), $option->getPriceType(), $option->getPrice(), true)
            ));
        }
        return '';
    }
    
}