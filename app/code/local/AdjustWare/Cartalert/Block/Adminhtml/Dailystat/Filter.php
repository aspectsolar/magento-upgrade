<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.3.1
 * @license:     lMnQrjiQcxfPlbdwpFhsKslnzVPfVqO45IEZa4tVHs
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Block_Adminhtml_Dailystat_Filter extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/index');
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'abandone_cart_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

        $dateFormatIso = '%Y-%m-%d';//Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('period_type', 'select', array(
            'name' => 'period_type',
            'options' => array(
                'day'   => Mage::helper('adjcartalert')->__('Day'),
                'month' => Mage::helper('adjcartalert')->__('Month'),
                'year'  => Mage::helper('adjcartalert')->__('Year')
            ),
            'label' => Mage::helper('adjcartalert')->__('Period'),
            'title' => Mage::helper('adjcartalert')->__('Period')
        ));

        $fieldset->addField('from', 'date', array(
            'name'      => 'from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('adjcartalert')->__('From'),
            'title'     => Mage::helper('adjcartalert')->__('From'),
            'required'  => true
        ));

        $fieldset->addField('to', 'date', array(
            'name'      => 'to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('adjcartalert')->__('To'),
            'title'     => Mage::helper('adjcartalert')->__('To'),
            'required'  => true
        ));

        $fieldset->addField('submit', 'submit', array(
            'name'      => 'submit',
            
            'title'     => Mage::helper('adjcartalert')->__('Show Report'),
            'value'     => Mage::helper('adjcartalert')->__('Show Report'),
            'class'     => 'form-button',
        ));        
        
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
}