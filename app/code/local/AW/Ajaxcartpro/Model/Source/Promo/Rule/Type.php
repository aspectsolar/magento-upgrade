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


class AW_Ajaxcartpro_Model_Source_Promo_Rule_Type
{
    const ADD_LABEL    = 'Add To Cart';
    const REMOVE_LABEL = 'Remove From Cart';

    const ADD_VALUE    = 1;
    const REMOVE_VALUE = 2;

    public function toOptionArray()
    {
        return array(
            self::ADD_VALUE  => Mage::helper('ajaxcartpro')->__(self::ADD_LABEL),
            self::REMOVE_VALUE  => Mage::helper('ajaxcartpro')->__(self::REMOVE_LABEL),
        );
    }

    public function getOptionByValue($value)
    {
        $options = $this->toOptionArray();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}