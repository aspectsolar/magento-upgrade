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

class Kash_Orders2csvpro_Block_Adminhtml_Help_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid
{
	const XPATH_CONFIG_SETTINGS_ORDER_ID				= 'orders2csvpro/settings/order_id';
	const XPATH_CONFIG_SETTINGS_ORDER_PRODUCT_ID		= 'orders2csvpro/settings/order_product_id';
	const XPATH_CONFIG_SETTINGS_INVOICE_ID				= 'orders2csvpro/settings/invoice_id';

	public function __construct()
	{
		parent::__construct();
		$this->setId('helpProductGrid');
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

		$orderItemId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_ORDER_PRODUCT_ID);
		$orderItem = Mage::helper('orders2csvpro')->getItemByProductId($orderItemId, $order->getItemsCollection());
		if(!$orderItem || (!$orderItem->getData() && count($orderItem->getData())==0)){
			$row = new Varien_Object(array('key'=>"item_data_?", 'value'=>'Selected order item not present',
      						'object'=>'Item data'));
			$collection->addItem($row);
			$this->setCollection($collection);
			return parent::_prepareCollection();
		}

		foreach ($orderItem->getData() as $key => $value) {
			if($key != "product_options"){
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"item_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Item data'));
				$collection->addItem($row);
			}
		}
		
		if($orderItem->getData('gift_message_id')>0){
			$giftMessage = Mage::getSingleton('giftmessage/message')->load($orderItem->getData('gift_message_id'));
			foreach ($giftMessage->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"item_gift_data_$key", 'value'=>htmlentities($value),
						'object'=>'Item gift messages'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"item_gift_data_?", 'value'=>'none avalible on item',
					'object'=>'Item gift messages'));
			$collection->addItem($row);
		}
		
		foreach (Mage::getModel('catalog/product')->load($orderItem->getData('product_id'))->getData() as $key => $value) {
			if(!is_string($value)) $value = print_r($value, true);
			$row = new Varien_Object(array('key'=>"product_data_$key", 'value'=>htmlentities($value),
      						'object'=>'Product data'));
			$collection->addItem($row);
		}

		$replacements = array (
    		'item_status' => '$orderItem->getStatus()'
		);

		foreach ($replacements as $key => $value) {
			ob_start();
			$evaValue = eval("return ($value);");
			ob_end_clean();
			 
			if(!is_string($value)) $value = print_r($value, true);
			$row = new Varien_Object(array('key'=>$key, 'value'=>htmlentities($evaValue),
      						'object'=>'Item'));
			$collection->addItem($row);
		}

		$options = $this->getItemOptions($orderItem);
		if(!empty($options)){
			foreach ($options as $option) {
				foreach ($option as $key => $value) {
					if(!is_string($value)) $value = print_r($value, true);
					$row = new Varien_Object(array('key'=>"item_option_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Item option data'));
					$collection->addItem($row);					
				}
				if(!empty($option['label'])){
					if(!empty($option['value'])){
						$labelKey = str_replace(' ', '_', strtolower(trim($option['label'])));
						if(!is_string($value)) $value = print_r($value, true);
						$row = new Varien_Object(array('key'=>"item_option_value_$labelKey", 'value'=>htmlentities($value),
								'object'=>'Item option data'));
						$collection->addItem($row);						
					}
				}
			}
		}else{
			$row = new Varien_Object(array('key'=>"item_option_data_?", 'value'=>'Selected product do not have options',
	      						'object'=>'Item option data'));
			$collection->addItem($row);
		}

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

		$this->addExportType('*/*/exportProductCsv', Mage::helper('orders2csvpro')->__('CSV'));
		$this->addExportType('*/*/exportProductXml', Mage::helper('orders2csvpro')->__('XML'));

		return parent::_prepareColumns();
	}

	/**
	 * Get all option values from a general item
	 *
	 * @param order_item $item
	 * @return array of options
	 */
	public function getItemOptions($item) {
		$result = array();
		if ($options = $item->getProductOptions()) {
			if (isset($options['options'])) {
				$result = array_merge($result, $options['options']);
			}
			if (isset($options['additional_options'])) {
				$result = array_merge($result, $options['additional_options']);
			}
			if (isset($options['attributes_info'])) {
				$result = array_merge($result, $options['attributes_info']);
			}
			if (isset($options['bundle_options'])) {
				$result = array_merge($result, $options['bundle_options']);
			}
			if (isset($options['bundle_selection_attributes'])) {
				$result = array_merge($result, unserialize($options['bundle_selection_attributes']));
			}
		}
		return $result;
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