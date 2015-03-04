<?php
/**
* Kash Orders2csvpro Module
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to Henrik Kier <info@kash.com> so we can send you a copy immediately.
*
* @category   Kash
* @package    Kash_Orders2csvpro
* @copyright  Copyright (c) 2012 Kash (http://kash.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* @author     Henrik Kier <info@kash.com>
* */
class Kash_Orders2csvpro_Block_Adminhtml_File_Edit_Tab_Generel extends Mage_Adminhtml_Block_Widget_Form
{

	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('generel_fieldset', array('legend'=>Mage::helper('orders2csvpro')->__('Element information')));
		 
		$fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('orders2csvpro')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
		));

		$fieldset->addField('is_active', 'select', array(
          'label'     => Mage::helper('orders2csvpro')->__('Status'),
          'name'      => 'is_active',
          'required'  => true,
          'values'    => array(
		array(
                  'value'     => 1,
                  'label'     => Mage::helper('orders2csvpro')->__('Enabled'),
		),

		array(
                  'value'     => 2,
                  'label'     => Mage::helper('orders2csvpro')->__('Disabled'),
		),
		),
		));
		
		$fieldset->addField('type', 'select', array(
				'label'     => Mage::helper('orders2csvpro')->__('Type'),
				'name'      => 'type',
				'required'  => true,
				'values'    => array(
						array(
								'value'     => 1,
								'label'     => Mage::helper('orders2csvpro')->__('Order'),
						),
						array(
								'value'     => 2,
								'label'     => Mage::helper('orders2csvpro')->__('Invoice'),
						),
						array(
								'value'     => 3,
								'label'     => Mage::helper('orders2csvpro')->__('Shipment'),
						),
						array(
								'value'     => 4,
								'label'     => Mage::helper('orders2csvpro')->__('Creditmemo'),
						),
				),
		));		

// 		$fieldset->addField('num_formatting', 'select', array(
// 	          'label'     => Mage::helper('orders2csvpro')->__('Price formatting'),
// 	          'name'      => 'num_formatting',
// 	          'required'  => true,
// 	          'values'    => array(
// 					array(
// 	                  'value'     => 1,
// 	                  'label'     => Mage::helper('orders2csvpro')->__('None - (ex. 12231245)'),
// 					),
// 					array(
// 	                  'value'     => 2,
// 	                  'label'     => Mage::helper('orders2csvpro')->__('Decimal - (ex. 1223.1245)'),
// 					),
// 					array(
// 	                  'value'     => 3,
// 	                  'label'     => Mage::helper('orders2csvpro')->__('Text - (ex. $1,223.12)'),
// 					),
// 			),
// 		));
		
		$statusValues[] = array(
				'label' => Mage::helper('orders2csvpro')->__('Do not change'),
				'value' => null
		);
		$orderConfig = Mage::getModel('sales/order_config');
		foreach ($orderConfig->getStatuses() as $code => $label) {
			$statusValues[] = array(
					'label' => 'Order - '.$label,
					'value' => 'order,'.$code
			);
		}
		
		$invoiceConfig = Mage::getModel('sales/order_invoice')->getStates();
		foreach ($invoiceConfig as $code => $label) {
			$statusValues[] = array(
					'label' => 'Invoice - '.$label,
					'value' => 'invoice,'.$code
			);
		}
		
		$creditConfig = Mage::getModel('sales/order_creditmemo')->getStates();
		foreach ($creditConfig as $code => $label) {
			$statusValues[] = array(
					'label' => 'Creditmemo - '.$label,
					'value' => 'credit,'.$code
			);
		}
		
		$fieldset->addField('status_after', 'select', array(
				'label'     => Mage::helper('orders2csvpro')->__('Change to this status after manual run'),
				'name'      => 'status_after',
				'required'  => false,
				'values'    => $statusValues
		));
		
		$fieldset->addField('delimiter', 'text', array(
				'label'     => Mage::helper('orders2csvpro')->__('Delimiter'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'delimiter',
				'value'		=> ',',
		));

		$fieldset->addField('enclosure', 'text', array(
				'label'     => Mage::helper('orders2csvpro')->__('Enclosure'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'enclosure',
				'value'		=> '"',
		));
		
		$fieldset->addField('saveas', 'hidden', array(
	        'name'      => 'saveas',
	        'value'     => '0',
		));

		if ( Mage::getSingleton('adminhtml/session')->getOrders2csvproData() )
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getOrders2csvproData());
			Mage::getSingleton('adminhtml/session')->setFileData(null);
		} elseif ( Mage::registry('orders2csvpro_data') ) {
			$form->setValues(Mage::registry('orders2csvpro_data')->getData());
		}
		return parent::_prepareForm();
	}
}