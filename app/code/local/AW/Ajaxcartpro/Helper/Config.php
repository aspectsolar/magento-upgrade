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


class AW_Ajaxcartpro_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_PROGRESS_ANIMATION            = 'ajaxcartpro/general/progressanimation';
    const GENERAL_CART_ANIMATION                = 'ajaxcartpro/general/cartanimation';
    const GENERAL_ACTIVITY_INDICATOR            = 'ajaxcartpro/general/activityindicator';
    const GENERAL_SHOW_PROGRESS_ANIMATION       = 'ajaxcartpro/general/showprogressanimation';
    const GENERAL_OPTIONS_POPUP_DISPLAY_WITH    = 'ajaxcartpro/general/optionspopupdisplaywith';

    const ADD_PRODUCT_CONFIRMATION_CONTENT      = 'ajaxcartpro/addproductconfirmation/content';
    const ADD_PRODUCT_CONFIRMATION_ENABLED      = 'ajaxcartpro/addproductconfirmation/enabled';
    const ADD_PRODUCT_CONFIRMATION_COUNTDOWN    = 'ajaxcartpro/addproductconfirmation/countdown';

    const REMOVE_PRODUCT_CONFIRMATION_CONTENT   = 'ajaxcartpro/removeproductconfirmation/content';
    const REMOVE_PRODUCT_CONFIRMATION_ENABLED   = 'ajaxcartpro/removeproductconfirmation/enabled';
    const REMOVE_PRODUCT_CONFIRMATION_COUNTDOWN = 'ajaxcartpro/removeproductconfirmation/countdown';

    public function getGeneralProgressAnimation($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_PROGRESS_ANIMATION, $store);
    }

    public function getGeneralCartAnimation($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_CART_ANIMATION, $store);
    }

    public function getGeneralActivityIndicator($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_ACTIVITY_INDICATOR, $store);
    }

    public function getGeneralShowProgressAnimation($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_SHOW_PROGRESS_ANIMATION, $store);
    }

    public function getGeneralOptionsPopupDisplayWith($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_OPTIONS_POPUP_DISPLAY_WITH, $store);
    }

    public function getAddProductConfirmationContent($store = null)
    {
        return Mage::getStoreConfig(self::ADD_PRODUCT_CONFIRMATION_CONTENT, $store);
    }

    public function getAddProductConfirmationEnabled($store = null)
    {
        return Mage::getStoreConfig(self::ADD_PRODUCT_CONFIRMATION_ENABLED, $store);
    }

    public function getAddProductConfirmationCountdown($store = null)
    {
        return Mage::getStoreConfig(self::ADD_PRODUCT_CONFIRMATION_COUNTDOWN, $store);
    }

    public function getRemoveProductConfirmationContent($store = null)
    {
        return Mage::getStoreConfig(self::REMOVE_PRODUCT_CONFIRMATION_CONTENT, $store);
    }

    public function getRemoveProductConfirmationEnabled($store = null)
    {
        return Mage::getStoreConfig(self::REMOVE_PRODUCT_CONFIRMATION_ENABLED, $store);
    }

    public function getRemoveProductConfirmationCountdown($store = null)
    {
        return Mage::getStoreConfig(self::REMOVE_PRODUCT_CONFIRMATION_COUNTDOWN, $store);
    }
}