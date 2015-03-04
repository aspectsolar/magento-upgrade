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

class Aitoc_Aiteditablecart_Model_Rewrite_FrontBundleProductType extends Mage_Bundle_Model_Product_Type
{
    // override parent    
    
    public function getOptionsCollection($product = null)
    {
// start aitoc code

if (!$this->getStoreFilter($product))
{
    $this->setStoreFilter($product->getStoreId(), $product); // ait
}

// finish aitoc code
        
        return parent::getOptionsCollection($product);
    }    
}