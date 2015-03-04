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

class Aitoc_Aiteditablecart_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getOptionPrice($oProduct, $sPriceType, $nPriceValue, $flag=false)
    {
        if ($flag && $sPriceType == 'percent') {

            if ($nSpecialPrice = $oProduct->getSpecialPrice())
            {
                $basePrice = $nSpecialPrice;
            }
            else 
            {
                $basePrice = $oProduct->getPrice();
            }

            $price = $basePrice*($nPriceValue/100);
            return $price;
        }

        return $nPriceValue;
    }   
}