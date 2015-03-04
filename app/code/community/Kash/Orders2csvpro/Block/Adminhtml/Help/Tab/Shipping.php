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

class Kash_Orders2csvpro_Block_Adminhtml_Help_Tab_Shipping extends Mage_Adminhtml_Block_Widget_Grid
{
	const XPATH_CONFIG_SETTINGS_SHIPPING_ID				= 'orders2csvpro/settings/shipping_id';
	const XPATH_CONFIG_SETTINGS_SHIPPING_PRODUCT_ID		= 'orders2csvpro/settings/shipping_product_id';

	public function __construct()
	{
		parent::__construct();
		$this->setId('helpShippingGrid');
		$this->setFilterVisibility(false);
		$this->setPagerVisibility(false);
	}

	protected function _prepareCollection()
	{
		$collection = new Varien_Data_Collection();
		$shippingId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_SHIPPING_ID);
		$shipping = Mage::getModel('sales/order_shipment')->loadByIncrementId($shippingId);

		if(!$shipping->getData() && count($shipping->getData())==0){
			$row = new Varien_Object(array('key'=>"shipping_data_?", 'value'=>'Selected shipment not present',
      						'object'=>'Shipping data'));
			$collection->addItem($row);
			$this->setCollection($collection);
			return parent::_prepareCollection();
		}

		foreach ($shipping->getData() as $key => $value) {
			if(!is_string($value)) $value = print_r($value, true);
			$row = new Varien_Object(array('key'=>"shipping_data_$key", 'value'=>htmlentities($value),
      						'object'=>'Shipping data'));
			$collection->addItem($row);
		}

		if($shipping->getShippingAddress()){
			foreach ($shipping->getShippingAddress()->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"shipping_shipping_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Shipping Shipping'));
				$collection->addItem($row);
			}
			$row = new Varien_Object(array('key'=>"shipping_shipping_country_name", 'value'=>$shipping->getShippingAddress()->getCountryModel()->getName(),
	      	      						'object'=>'Shipping Shipping'));
			$collection->addItem($row);
		}else{
			$row = new Varien_Object(array('key'=>"shipping_shipping_data_?", 'value'=>'none avalible on shipment',
                        						'object'=>'Shipping Shipping'));
			$collection->addItem($row);
		}

		if($shipping->getBillingAddress()){
			foreach ($shipping->getBillingAddress()->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"shipping_billing_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Shipping Billing'));
				$collection->addItem($row);
			}
			$row = new Varien_Object(array('key'=>"shipping_billing_country_name", 'value'=>$shipping->getBillingAddress()->getCountryModel()->getName(),
	      	      						'object'=>'Shipping Billing'));
			$collection->addItem($row);
		}else{
			$row = new Varien_Object(array('key'=>"shipping_billing_data_?", 'value'=>'none avalible on shipment',
                              						'object'=>'Shipping Billing'));
			$collection->addItem($row);
		}
		
		$trackCollection = $shipping->getTracksCollection(); 
		if ($trackCollection->getItems()){
			$trackItems = $trackCollection->getItems();
			$lastShippingTrack = end($trackItems);
			foreach ($lastShippingTrack->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"shipping_track_last_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Shipping last Track'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"shipping_track_last_data_?", 'value'=>'none avalible on shipment',
                              						'object'=>'Shipping last Track'));
			$collection->addItem($row);
		}

		if($shipping->getCommentsCollection() && $shipping->getCommentsCollection()->getItems()){
			foreach (end($shipping->getCommentsCollection()->getItems())->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"shipping_comments_last_data_$key", 'value'=>htmlentities($value),
						'object'=>'Shipping last Comment'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"shipping_comments_last_data_?", 'value'=>'none avalible on shipment',
					'object'=>'Shipping last Comment'));
			$collection->addItem($row);
		}
		
		$replacements = array (
		);

		foreach ($replacements as $key => $value) {
			ob_start();
			$evaValue = @eval("return ($value);");
			ob_end_clean();
			 
			if(!is_string($value)) $value = print_r($value, true);
			$row = new Varien_Object(array('key'=>$key, 'value'=>htmlentities($evaValue),
      						'object'=>'Shipping '));
			$collection->addItem($row);
		}
		 
		$shippingItemId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_SHIPPING_PRODUCT_ID);
		$shippingItem = Mage::helper('orders2csvpro')->getItemByProductId($shippingItemId, $shipping->getItemsCollection());
		if(!empty($shippingItem)){
			foreach ($shippingItem->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"shipping_item_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Shipping item'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"shipping_item_data_?", 'value'=>'The selected product do not exsist in shipment',
	      						'object'=>'Shipping item'));
			$collection->addItem($row);
		}

// 		if(Mage::helper('orders2csvpro')->getTotalsListForDisplaying($shipping, $shipping->getOrder())){
// 			foreach (Mage::helper('orders2csvpro')->getTotalsListForDisplaying($shipping, $shipping->getOrder()) as $value){
// 				$mainKey = $value['source_field'];
// 				foreach ($value as $key => $subValue){
// 					if(!is_string($value)) $value = print_r($value, true);
// 					$row = new Varien_Object(array('key'=>"shipping_totals_".$mainKey."_".$key, 'value'=>htmlentities($subValue),
// 	      						'object'=>'Shipping Totals'));
// 					$collection->addItem($row);
// 				}
// 			}
// 		}else{
// 			$row = new Varien_Object(array('key'=>"shipping_totals_?", 'value'=>'none avalible on Shipping',
//                   						'object'=>'Shipping Totals'));
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

		$this->addExportType('*/*/exportShippingCsv', Mage::helper('orders2csvpro')->__('CSV'));
		$this->addExportType('*/*/exportShippingXml', Mage::helper('orders2csvpro')->__('XML'));

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