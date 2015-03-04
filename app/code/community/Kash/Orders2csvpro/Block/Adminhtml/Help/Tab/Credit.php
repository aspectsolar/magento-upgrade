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

class Kash_Orders2csvpro_Block_Adminhtml_Help_Tab_Credit extends Mage_Adminhtml_Block_Widget_Grid
{
	const XPATH_CONFIG_SETTINGS_CREDITMEMO_ID				= 'orders2csvpro/settings/creditmemo_id';
	const XPATH_CONFIG_SETTINGS_CREDITMEMO_PRODUCT_ID		= 'orders2csvpro/settings/creditmemo_product_id';

	public function __construct()
	{
		parent::__construct();
		$this->setId('helpCreditGrid');
		$this->setFilterVisibility(false);
		$this->setPagerVisibility(false);
	}

	protected function _prepareCollection()
	{
		$collection = new Varien_Data_Collection();
		$creditId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_CREDITMEMO_ID);
		$credit = Mage::getModel('sales/order_creditmemo')->load($creditId);

		if(!$credit->getData() && count($credit->getData())==0){
			$row = new Varien_Object(array('key'=>"creditmemo_data_?", 'value'=>'Selected creditmemo not present',
      						'object'=>'Creditmemo data'));
			$collection->addItem($row);
			$this->setCollection($collection);
			return parent::_prepareCollection();
		}

		foreach ($credit->getData() as $key => $value) {
			if(!is_string($value)) $value = print_r($value, true);
			$row = new Varien_Object(array('key'=>"creditmemo_data_$key", 'value'=>htmlentities($value),
      						'object'=>'Creditmemo data'));
			$collection->addItem($row);
		}

		if($credit->getShippingAddress()){
			foreach ($credit->getShippingAddress()->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"creditmemo_shipping_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Creditmemo Shipping'));
				$collection->addItem($row);
			}
			$row = new Varien_Object(array('key'=>"creditmemo_shipping_country_name", 'value'=>$credit->getShippingAddress()->getCountryModel()->getName(),
	      	      						'object'=>'Creditmemo Shipping'));
			$collection->addItem($row);
		}else{
			$row = new Varien_Object(array('key'=>"icreditmemo_shipping_data_?", 'value'=>'none avalible on creditmemo',
      						'object'=>'Creditmemo Shipping'));
			$collection->addItem($row);
		}

		if($credit->getBillingAddress()){
			foreach ($credit->getBillingAddress()->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"creditmemo_billing_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Creditmemo Billing'));
				$collection->addItem($row);
			}
			$row = new Varien_Object(array('key'=>"creditmemo_billing_country_name", 'value'=>$credit->getBillingAddress()->getCountryModel()->getName(),
	      	      						'object'=>'Creditmemo Billing'));
			$collection->addItem($row);
		}else{
			$row = new Varien_Object(array('key'=>"icreditmemo_billing_data_?", 'value'=>'none avalible on creditmemo',
      						'object'=>'Creditmemo Billing'));
			$collection->addItem($row);
		}

		if($credit->getCommentsCollection() && $credit->getCommentsCollection()->getItems()){
			foreach (end($credit->getCommentsCollection()->getItems())->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"creditmemo_comments_last_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Creditmemo last Comment'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"creditmemo_comments_last_data_?", 'value'=>'none avalible on creditmemo',
      						'object'=>'Creditmemo last Comment'));
			$collection->addItem($row);
		}

		$replacements = array (
		);

		foreach ($replacements as $key => $value) {
			ob_start();
			$evaValue = eval("return ($value);");
			ob_end_clean();
			 
			if(!is_string($value)) $value = print_r($evaValue, true);
			$row = new Varien_Object(array('key'=>$key, 'value'=>htmlentities($evaValue),
      						'object'=>'Invoice '));
			$collection->addItem($row);
		}
		 
		$creditItemId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_CREDITMEMO_PRODUCT_ID);
		$creditItem = Mage::helper('orders2csvpro')->getItemByProductId($creditItemId, $credit->getItemsCollection());
		if(!empty($creditItem)){
			foreach ($creditItem->getData() as $key => $value) {
				if(!is_string($value)) $value = print_r($value, true);
				$row = new Varien_Object(array('key'=>"creditmemo_item_data_$key", 'value'=>htmlentities($value),
	      						'object'=>'Creditmemo item'));
				$collection->addItem($row);
			}
		}else{
			$row = new Varien_Object(array('key'=>"creditmemo_item_data_?", 'value'=>"The selected product do not exsist in creditmemo",
      						'object'=>'Creditmemo item'));
			$collection->addItem($row);
		}

// 		if(Mage::helper('orders2csvpro')->getTotalsListForDisplaying($credit, $credit->getOrder())){
// 			foreach (Mage::helper('orders2csvpro')->getTotalsListForDisplaying($credit, $credit->getOrder()) as $value){
// 				$mainKey = $value['source_field'];
// 				foreach ($value as $key => $subValue){
// 					if(!is_string($value)) $value = print_r($value, true);
// 					$row = new Varien_Object(array('key'=>"creditmemo_totals_".$mainKey."_".$key, 'value'=>htmlentities($subValue),
// 	      						'object'=>'Creditmemo Totals'));
// 					$collection->addItem($row);
// 				}
// 			}
// 		}else{
// 			$row = new Varien_Object(array('key'=>"creditmemo_totals_?", 'value'=>'none avalible on creditmemo',
//                   						'object'=>'Creditmemo Totals'));
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

		$this->addExportType('*/*/exportCreditCsv', Mage::helper('orders2csvpro')->__('CSV'));
		$this->addExportType('*/*/exportCreditXml', Mage::helper('orders2csvpro')->__('XML'));

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