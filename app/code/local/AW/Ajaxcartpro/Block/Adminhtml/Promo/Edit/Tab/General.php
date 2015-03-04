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


class AW_Ajaxcartpro_Block_Adminhtml_Promo_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('General Information');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $promoModel = Mage::registry('current_acp_promo');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('general_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $this->__('General Information')));
        $fieldset->addField(
            'name', 'text',
            array(
                 'name'     => 'name',
                 'label'    => $this->__('Rule Name'),
                 'title'    => $this->__('Rule Name'),
                 'required' => true,
            )
        );

        $fieldset->addField(
            'type', 'select',
            array(
                 'label'              => $this->__('Action'),
                 'title'              => $this->__('Action'),
                 'name'               => 'type',
                 'required'           => true,
                 'options'            => Mage::getModel('ajaxcartpro/source_promo_rule_type')->toOptionArray(),
            )
        );

        $fieldset->addField(
            'description', 'textarea',
            array(
                 'name'  => 'description',
                 'label' => $this->__('Description'),
                 'title' => $this->__('Description'),
                 'style' => 'height: 100px;',
            )
        );

        $fieldset->addField(
            'is_active', 'select',
            array(
                 'label'    => $this->__('Status'),
                 'title'    => $this->__('Status'),
                 'name'     => 'is_active',
                 'required' => true,
                 'options'  => array(
                     '1' => $this->__('Active'),
                     '0' => $this->__('Inactive'),
                 ),
            )
        );

        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_ids', 'hidden', array('name' => 'store_ids[]'));
        } else {
            $fieldset->addField(
                'store_ids', 'multiselect',
                array(
                     'name'     => 'store_ids[]',
                     'label'    => $this->__('Store View'),
                     'title'    => $this->__('Store View'),
                     'required' => true,
                     'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                )
            );
        }

        $fieldset->addField(
            'customer_groups', 'multiselect',
            array(
                 'name'     => 'customer_groups[]',
                 'label'    => $this->__('Customer Groups'),
                 'title'    => $this->__('Customer Groups'),
                 'required' => true,
                 'values'   => Mage::getResourceModel('customer/group_collection')->toOptionArray()
            )
        );

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField(
            'from_date', 'date',
            array(
                 'name'         => 'from_date',
                 'label'        => $this->__('From Date'),
                 'title'        => $this->__('From Date'),
                 'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                 'format'       => $dateFormatIso
            )
        );

        $fieldset->addField(
            'to_date', 'date',
            array(
                 'name'         => 'to_date',
                 'label'        => $this->__('To Date'),
                 'title'        => $this->__('To Date'),
                 'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                 'format'       => $dateFormatIso
            )
        );

        $fieldset->addField(
            'priority', 'text',
            array(
                 'name'  => 'priority',
                 'label' => $this->__('Priority'),
            )
        );
        $form->setValues($promoModel->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}