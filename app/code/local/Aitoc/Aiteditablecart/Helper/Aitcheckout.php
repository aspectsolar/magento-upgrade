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
class Aitoc_Aiteditablecart_Helper_Aitcheckout extends Mage_Core_Helper_Abstract
{
    protected $_isEnabled = null;

    /**
     * Check is module exists and enabled in global config.
	 * @param string $moduleName full module name
     * @return boolean
     */
    public function isModuleEnabled($moduleName = null)
    {
        if (!Mage::getConfig()->getNode('modules/Aitoc_Aitcheckout')) {
            return false;
        }

        $isActive = Mage::getConfig()->getNode('modules/Aitoc_Aitcheckout/active');
        if (!$isActive || !in_array((string)$isActive, array('true', '1'))) {
            return false;
        }
        return true;
    }

    /**
     * Check whether the OPCB module is active or not
     * @return boolean
     */
    public function isEnabled()
    {
        if($this->_isEnabled === null)
        {
            $this->_isEnabled = ($this->isModuleEnabled('Aitoc_Aitcheckout') && !Mage::helper('aitcheckout')->isDisabled());
        }
        return $this->_isEnabled;
    }

    public function isEnabledAndCompact()
    {
        return ($this->isEnabledAndNotOutsideCart() && Mage::helper('aitcheckout')->isCompactDesign());
    }

    public function isEnabledAndNotOutsideCart()
    {
        return ($this->isEnabled() && !Mage::helper('aitcheckout')->isShowCheckoutOutsideCart());
    }
	
	/**
     * Choose shipping block template depending on OPCB module
     * @return string
     */
    public function switchShipping()
    {
        if ($this->isEnabledAndNotOutsideCart())
        {
            return '';
        }
        return "checkout/cart/shipping.phtml";
    }

    /**
     * Choose coupon block template depending on OPCB module
     * @return string
     */
    public function switchCoupon()
    {
        if ($this->isEnabledAndCompact())
        {
            return '';
        }
        return "checkout/cart/coupon.phtml";
    }

    /**
     * Choose top checkout button template depending on OPCB module
     * @return string
     */
    public function switchTopLink()
    {
        if ($this->isEnabledAndCompact())
        {
            return '';
        }
        return "checkout/onepage/link.phtml";
    }

    /**
     * Choose cross-sell block template depending on OPCB module
     * @return string
     */
    public function switchCrosssell()
    {
        if ($this->isEnabledAndCompact())
        {
            return '';
        }
        return "checkout/cart/crosssell.phtml";
    }
}