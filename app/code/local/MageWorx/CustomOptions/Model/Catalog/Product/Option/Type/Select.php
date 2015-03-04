<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Advanced Product Options extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @author     MageWorx Dev Team
 */

class MageWorx_CustomOptions_Model_Catalog_Product_Option_Type_Select extends Mage_Catalog_Model_Product_Option_Type_Select {
    /**
     * Return Price for selected option
     *
     * @param string $optionValue Prepared for cart option value
     * @return float
     */
    public function getOptionPrice($optionValue, $basePrice, $qty = 0, $optionQty = 1, $store=null) {
        $option = $this->getOption();
        $result = 0;                

        if (!$this->_isSingleSelection()) {
            foreach(explode(',', $optionValue) as $value) {
                if ($opValue = $option->getValueById($value)) {                    
                    $optionQty = (!is_array($optionQty)?$optionQty:$optionQty[$value]);
                    if ($option->getCustomoptionsIsOnetime()) $optionTotalQty =  $optionQty; else $optionTotalQty = $optionQty * $qty;
                    // apply tier price
                    list($optionPrice, $optionPriceType) = Mage::helper('customoptions')->getOptionTierPriceAndType($opValue, $optionTotalQty, $store);
                    $result += $this->_getCustomOptionsChargableOptionPrice(
                        $optionPrice,
                        $optionPriceType == 'percent',
                        $basePrice,
                        $qty,
                        $option->getCustomoptionsIsOnetime(),
                        $optionQty
                    );
                } else {
                    if ($this->getListener()) {
                        $this->getListener()
                                ->setHasError(true)
                                ->setMessage(
                                    Mage::helper('catalog')->__('Some of the products below do not have all the required options. Please remove them and add again with all the required options.')
                                );
                        break;
                    }
                }
            }
        } elseif ($this->_isSingleSelection()) {
            if ($opValue = $option->getValueById($optionValue)) {
                if ($option->getCustomoptionsIsOnetime()) $optionTotalQty =  $optionQty; else $optionTotalQty = $optionQty * $qty;
                list($optionPrice, $optionPriceType) = Mage::helper('customoptions')->getOptionTierPriceAndType($opValue, $optionTotalQty, $store);
                $result = $this->_getCustomOptionsChargableOptionPrice(
                    $optionPrice,
                    $optionPriceType == 'percent',
                    $basePrice,
                    $qty,
                    $option->getCustomoptionsIsOnetime(),
                    $optionQty
                );
            } else {
                if ($this->getListener()) {
                    $this->getListener()
                            ->setHasError(true)
                            ->setMessage(
                                Mage::helper('catalog')->__('Some of the products below do not have all the required options. Please remove them and add again with all the required options.')
                            );
                }
            }
        }

        return $result;
    }
    
    protected function _getCustomOptionsChargableOptionPrice($price, $isPercent, $basePrice, $qty = 1, $customoptionsIsOnetime = 0, $optionQty = 1) {        
        if ($customoptionsIsOnetime) $sub = $qty; else $sub = 1;
        if ($sub==0 || $price==0 || $optionQty==0) return 0;        
        if ($isPercent) {
            if ($basePrice==0) return 0;
            return ($basePrice * $price * $optionQty / 100) / $sub;
        } else {
            return $price * $optionQty / $sub;
        }
    }

}