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

class Kash_Orders2csvpro_Block_Adminhtml_Help_Tab_Invoice extends Mage_Adminhtml_Block_Widget_Grid
{
	const XPATH_CONFIG_SETTINGS_INVOICE_ID				= 'orders2csvpro/settings/invoice_id';
	const XPATH_CONFIG_SETTINGS_INVOICE_PRODUCT_ID		= 'orders2csvpro/settings/invoice_product_id';

	public function __construct()
	{
		parent::__construct();
		$this->setId('helpInvoiceGrid');
		$this->setFilterVisibility(false);
		$this->setPagerVisibility(false);
	}

	protected function _prepareCollection(){
		 
		$collection = new Varien_Data_Collection();
		$invoiceId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_INVOICE_ID);
		$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
		 
		if(!$invoice->getData() && count($invoice->getData())==0){
			$row = new Varien_Object(array('key'=>"invoice_data_?", 'value'=>'Selected invoice not present',
      						'object'=>'Invoice data'));
			$collection->addItem($row);
			$this->setCollection($collection);
			return parent::_prepareCollection();
		}

		foreach ($invoice->getData() as $key => $value) {
			if(!is_string($value)) $value = print_r($value, true);
			$row = new Varien_Object(array('key'=>"invoice_data_$key", 'value'=>htmlentities($value),
      						'object'=>'Invoice data'));
			$collection->addItem($row);
		}

		if($invoice->getShippingAddress()){
			foreach ($invoice->getShippingAddress()->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"invoice_shipping_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Invoice Shipping'));
				$collection->addItem($row);
			}
			$row = new Varien_Object(array('key'=>"invoice_shipping_country_name", 'value'=>$invoice->getShippingAddress()->getCountryModel()->getName(),
	      	      						'object'=>'Invoice Shipping'));
			$collection->addItem($row);
		}else{
			$row = new Varien_Object(array('key'=>"invoice_shipping_data_?", 'value'=>'none avalible on invoice',
                  						'object'=>'Invoice Shipping'));
			$collection->addItem($row);
		}

		if($invoice->getBillingAddress()){
			foreach ($invoice->getBillingAddress()->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"invoice_billing_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Invoice Billing'));
				$collection->addItem($row);
			}
			$row = new Varien_Object(array('key'=>"invoice_billing_country_name", 'value'=>$invoice->getBillingAddress()->getCountryModel()->getName(),
	      	      						'object'=>'Invoice Billing'));
			$collection->addItem($row);
		}else{
			$row = new Varien_Object(array('key'=>"invoice_billing_data_?", 'value'=>'none avalible on invoice',
                        						'object'=>'Invoice Billing'));
			$collection->addItem($row);
		}

		if($invoice->getCommentsCollection() && $invoice->getCommentsCollection()->getItems()){
			foreach (end($invoice->getCommentsCollection()->getItems())->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"invoice_comments_last_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Invoice last Comment'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"invoice_comments_last_data_?", 'value'=>'none avalible on invoice',
      						'object'=>'Invoice last Comment'));
			$collection->addItem($row);
		}

		$replacements = array (
    		'invoice_order_increment_ids' => '$invoice->getOrderIncrementId()',
    		'invoice_state_names' => '$invoice->getStateName()',
    		'invoice_was_pay_calleds' => '$invoice->wasPayCalled()'
		);

		foreach ($replacements as $key => $value) {
			ob_start();
			$evaValue = eval("return ($value);");
			ob_end_clean();
			 
			if(!is_string($value)) $value = print_r($value, true);
			$row = new Varien_Object(array('key'=>$key, 'value'=>htmlentities($evaValue),
      						'object'=>'Invoice '));
			$collection->addItem($row);
		}
		 
		$invoiceItemId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_INVOICE_PRODUCT_ID);
		$invoiceItem = Mage::helper('orders2csvpro')->getItemByProductId($invoiceItemId, $invoice->getItemsCollection());
		if(!empty($invoiceItem)){
			foreach ($invoiceItem->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"invoice_item_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Invoice item'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"invoice_item_data_?", 'value'=>"The selected product do not exsist in invoice",
      						'object'=>'Invoice item'));
			$collection->addItem($row);
		}
		 
// 		if(Mage::helper('orders2csvpro')->getTotalsListForDisplaying($invoice, $invoice->getOrder())){
// 			foreach (Mage::helper('orders2csvpro')->getTotalsListForDisplaying($invoice, $invoice->getOrder()) as $value){
// 				$mainKey = $value['source_field'];
// 				foreach ($value as $key => $subValue){
// 					if(!is_string($value)) $value = print_r($value, true);
// 					$row = new Varien_Object(array('key'=>"invoice_totals_".$mainKey."_".$key, 'value'=>htmlentities($subValue),
// 	      						'object'=>'Invoice Totals'));
// 					$collection->addItem($row);
// 				}
// 			}
// 		}else{
// 			$row = new Varien_Object(array('key'=>"invoice_totals_?", 'value'=>'none avalible on invoice',
//                   						'object'=>'Invoice Totals'));
// 			$collection->addItem($row);
// 		}

		 
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('object', array(
	      'header'    => Mage::helper('orders2csvpro')->__('Object'),
	      'align'     =>'left',
	      'index'     => 'object',
	      'sortable'  => false,
    	  'type'      => 'text',
          'width'     => '20%'
		));
		$this->addColumn('variable', array(
          'header'    => Mage::helper('orders2csvpro')->__('Key'),
          'align'     =>'left',
          'index'     => 'key',
	      'sortable'  => false,
    	  'type'      => 'text',
          'width'     => '40%'
		));
		$this->addColumn('value', array(
          'header'    => Mage::helper('orders2csvpro')->__('Exampel value'),
          'align'     =>'left',
          'index'     => 'value',
	      'sortable'  => false,
    	  'type'      => 'text',
          'width'     => '40%'
		));

		$this->addExportType('*/*/exportInvoiceCsv', Mage::helper('orders2csvpro')->__('CSV'));
		$this->addExportType('*/*/exportInvoiceXml', Mage::helper('orders2csvpro')->__('XML'));

		return parent::_prepareColumns();
	}

	
	/**
	* Get row edit url
	*
	* @return string
	*/
	public function getRowUrl($row)
	{
		return false;
		//return $this->getUrl('*/*/edit', array('type'=>$row->getId()));
	}
}