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

class Kash_Orders2csvpro_Block_Adminhtml_Help_Tab_Productbundle extends Mage_Adminhtml_Block_Widget_Grid
{
	const XPATH_CONFIG_SETTINGS_ORDER_ID						= 'orders2csvpro/settings/order_id';
	const XPATH_CONFIG_SETTINGS_ORDER_PRODUCTBUNDLE_ID		= 'orders2csvpro/settings/order_productbundle_id';
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

		$orderItemId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_ORDER_PRODUCTBUNDLE_ID);
		$orderItem = Mage::helper('orders2csvpro')->getItemByProductId($orderItemId, $order->getItemsCollection());
		if(!$orderItem || (!$orderItem->getData() && count($orderItem->getData())==0)){
			$row = new Varien_Object(array('key'=>"order_item_data_?", 'value'=>'Selected order item not present',
      						'object'=>'Order item data'));
			$collection->addItem($row);
			$this->setCollection($collection);
			return parent::_prepareCollection();
		}

		$options = array();
		$children = $orderItem->getChildrenItems();
		foreach ($children as $child){
			$bundleAttr = $this->getItemOptions($child);
			$optionId = $bundleAttr['option_id'];
			if(!array_key_exists($optionId, $options)){
				$options[$optionId] = $bundleAttr;
			}else{
				$options[$optionId]['price'] = $options[$optionId]['price'] + $bundleAttr['price'];
			}
			$options[$optionId]['value'][] = $child;
			break;
		}

		if ($options) {
			foreach ($options as $optionGroup) {
				foreach ($optionGroup as $key => $value) {
					if($key != "value"){
						if(!is_string($value)) $value = print_r($value, true);
						$row = new Varien_Object(array('key'=>"item_bundle_group_data_$key", 'value'=>htmlentities($value),
			      						'object'=>'Item Bundle group data'));
						$collection->addItem($row);
					}
				}
				foreach ($optionGroup['value'] as $optionItem){
					foreach ($optionItem->getData() as $key => $value) {
						if($key != "product_options"){
							if(!is_string($value)) $value = print_r($value, true);
							$row = new Varien_Object(array('key'=>"item_bundle_data_$key", 'value'=>htmlentities($value),
				      						'object'=>'Item Bundle data'));
							$collection->addItem($row);
						}
					}
					
					if($optionItem->getData('gift_message_id')>0){
						$giftMessage = Mage::getSingleton('giftmessage/message')->load($optionItem->getData('gift_message_id'));
						foreach ($giftMessage->getData() as $key => $value) {
							if(!is_string($value)) $value = print_r($value, true);
							$row = new Varien_Object(array('key'=>"item_bundle_gift_data_$key", 'value'=>htmlentities($value),
									'object'=>'Item Bundle gift messages'));
							$collection->addItem($row);
						}
					}else{
						$row = new Varien_Object(array('key'=>"item_bundle_gift_data_?", 'value'=>'none avalible on item',
								'object'=>'Item Bundle gift messages'));
						$collection->addItem($row);
					}						
				}
			}
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
				$result = array_merge($result, $options['bundle_options']
				);
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