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


class AW_Ajaxcartpro_Model_Resource_Promo extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_serializableFields   = array(
        'rule_actions_serialized' => array(null, array())
    );

    protected function _construct()
    {
        $this->_init('ajaxcartpro/promo', 'rule_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (is_array($object->getStoreIds()) && in_array(0, $object->getStoreIds())) {
            $object->setStoreIds(0);
        } else {
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }
        if (is_array($object->getCustomerGroups())) {
            $object->setCustomerGroups(implode(',', $object->getCustomerGroups()));
        }
        return parent::_beforeSave($object);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->addData($object->getRuleActionsSerialized());
        switch ($object->getType()) {
            case AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::ADD_VALUE:
                if ($object->getUseConfigOptionsRequiredOnly()) {
                    $object->setOptionsRequiredOnly(
                        Mage::helper('ajaxcartpro/config')->getGeneralOptionsPopupDisplayWith()
                    );
                }
                if ($object->getUseConfigPopupContent()) {
                    $object->setPopupContent(Mage::helper('ajaxcartpro/config')->getAddProductConfirmationContent());
                }
                if ($object->getUseConfigShowDialog()) {
                    $object->setShowDialog(Mage::helper('ajaxcartpro/config')->getAddProductConfirmationEnabled());
                }
                if ($object->getUseConfigCloseDialogAfter()) {
                    $object->setCloseDialogAfter(
                        Mage::helper('ajaxcartpro/config')->getAddProductConfirmationCountdown()
                    );
                }
                break;
            case AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::REMOVE_VALUE:
                if ($object->getUseConfigPopupContent()) {
                    $object->setPopupContent(Mage::helper('ajaxcartpro/config')->getRemoveProductConfirmationContent());
                }
                if ($object->getUseConfigShowDialog()) {
                    $object->setShowDialog(Mage::helper('ajaxcartpro/config')->getRemoveProductConfirmationEnabled());
                }
                if ($object->getUseConfigCloseDialogAfter()) {
                    $object->setCloseDialogAfter(
                        Mage::helper('ajaxcartpro/config')->getRemoveProductConfirmationCountdown()
                    );
                }
                break;
            default:
        }
        return parent::_afterLoad($object);
    }
}