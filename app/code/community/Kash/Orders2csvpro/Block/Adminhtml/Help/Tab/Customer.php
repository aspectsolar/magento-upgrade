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

class Kash_Orders2csvpro_Block_Adminhtml_Help_Tab_Customer extends Mage_Adminhtml_Block_Widget_Grid
{
	const XPATH_CONFIG_SETTINGS_ORDER_ID		= 'orders2csvpro/settings/order_id';
	const XPATH_CONFIG_SETTINGS_INVOICE_ID				= 'orders2csvpro/settings/invoice_id';

	public function __construct()
	{
		parent::__construct();
		$this->setId('helpCustomerGrid');
		$this->setFilterVisibility(false);
		$this->setPagerVisibility(false);
	}

	protected function _prepareCollection()
	{
		$collection = new Varien_Data_Collection();
		$orderId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_ORDER_ID);
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

		if(!$order->getData() && count($order->getData())==0){
			$invoiceId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_INVOICE_ID);
			$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
			$order = $invoice->getOrder();
			if(!$order->getData() && count($order->getData())==0){
				$row = new Varien_Object(array('key'=>"order_data_?", 'value'=>'Selected order not present',
	      						'object'=>'Order data'));
				$collection->addItem($row);
				$this->setCollection($collection);
				return parent::_prepareCollection();
			}
		}

		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		foreach ($customer->getData() as $key => $value) {
			if(!is_string($value)) $value = print_r($value, true);
			$row = new Varien_Object(array('key'=>"customer_data_$key", 'value'=>htmlentities($value),
      						'object'=>'Customer data'));
			$collection->addItem($row);
		}

		$customerBilling = $customer->getDefaultBillingAddress();
		if(!empty($customerBilling)){
			foreach ($customerBilling->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"customer_billing_data_$key", 'value'=>htmlentities($value),
	            						'object'=>'Customer default Billing data'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"customer_billing_data_?", 'value'=>'Customer do not have default billing data',
      	      						'object'=>'Customer default Billing data'));
			$collection->addItem($row);
		}

		$customerShipping = $customer->getDefaultShippingAddress();
		if(!empty($customerShipping)){
			foreach ($customerShipping->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"customer_shipping_data_$key", 'value'=>htmlentities($value),
	            						'object'=>'Customer default Shipping data'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"customer_shipping_data_?", 'value'=>'Customer do not have default shipping data',
      	      						'object'=>'Customer default Shipping data'));
			$collection->addItem($row);
		}

		// 	  $replacements = array (
		//      		'order_customer_group'=>'$order->getCustomerGroupId())->getCode()',
		// 			'order_shipping_description'=>'$order->getShippingDescription()',
		// 			'order_payment_block'=>'preg_replace(\'{{pdf_row_separator}}\', \'<br />\', Mage::helper(\'payment\')->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf())',
		// 			'order_base_total_due'=>'$order->getBaseTotalDue()',
		// 			'order_created_full'=>'$order->getCreatedAtFormated("full")',
		// 			'order_created_long'=>'$order->getCreatedAtFormated("long")',
		// 			'order_created_medium'=>'$order->getCreatedAtFormated("medium")',
		// 			'order_created_short'=>'$order->getCreatedAtFormated("short")',
		// 			'order_email_customer_note'=>'$order->getEmailCustomerNote()',
		// 			'order_is_not_virtual'=>'$order->getIsNotVirtual()',
		// 			'order_real_id'=>'$order->getRealOrderId()',
		// 			//'order_shipping_carrier_code'=>'$order->getShippingCarrier()->getCarrierCode()',
		// 			'order_status_label'=>'$order->getStatusLabel()',
		// 			'order_store_url'=>'$order->getStore()->getUrl()',
		// 			'order_store_base_url'=>'$order->getStore()->getBaseUrl()',
		//     		'order_num_invoices' => '$order->hasInvoices()',
		//     		'order_num_shipments' => '$order->hasShipments()',
		//     		'order_num_creditmemos' => '$order->hasCreditmemos()'
		//     		);

		//       foreach ($replacements as $key => $value) {
		//   		ob_start();
		// 		$evaValue = eval("return ($value);");
		// 		ob_end_clean();
		 
		// 	  	$row = new Varien_Object(array('key'=>$key, 'value'=>htmlentities($evaValue),
		//       						'object'=>'Order '));
		// 		$collection->addItem($row);
		//       }

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

		$this->addExportType('*/*/exportCustomerCsv', Mage::helper('orders2csvpro')->__('CSV'));
		$this->addExportType('*/*/exportCustomerXml', Mage::helper('orders2csvpro')->__('XML'));

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