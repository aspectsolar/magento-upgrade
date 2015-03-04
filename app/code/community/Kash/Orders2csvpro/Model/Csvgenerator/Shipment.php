<?php
/**
 * Magento
 *
 * NOTICE
 *
 * This source file a part of Kash_Orders2csvpro extension
 * all rights to this modul belongs to kash.com
 *
 * @category    Kash
 * @package     Kash_Orders2csvpro
 * @copyright   Copyright (c) 2011 kash (http://www.kash.com)
 */

class Kash_Orders2csvpro_Model_Csvgenerator_Shipment extends Kash_Orders2csvpro_Model_Csvgenerator_Abstract
{
	var $current_shipment;
	const XPATH_CONFIG_SETTINGS_SHIPMENT_FILE_ID        	= 'orders2csvpro/settings/file_shipment';
	
	public function getCsv($shipments = array(), $fileStructur = null, $schedule = null, $test = false)
	{
		if($this->_isActive() == 0 && $fileStructur == null){
			return null;
		}
	
		$cvsText = "";
		$fp = null;
	
		if($fileStructur == null){
			$fileStructur = Mage::getModel('orders2csvpro/file')->load(Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_SHIPMENT_FILE_ID));
		}
	
		if($schedule == null){
			$fileName = str_replace(' ', '_', $fileStructur->getTitle());
			$fileName .= '_'.date("Ymd_His").'.csv';
			$fp = fopen(Mage::getBaseDir('export').'/shipment_'.$fileName, 'w');
			$this->writeTopRow($fileStructur, $fp);
		}elseif($schedule->getShowHeader()==1){
			$cvsText .= $this->writeTopRow($fileStructur);
		}
	
		foreach ($shipments as $shipment) {
			if ( !$shipment instanceof Mage_Sales_Model_Order_Shipment) {
				$shipment = Mage::getModel('sales/order_shipment')->load($shipment);
			}
				
			$order = $shipment->getOrder();
			$this->current_shipment = $shipment;
	
			if($schedule != null && $test === false) {
				//put in the orders that has been processed in orders2cvs_runs
				$run = Mage::getModel('orders2csvpro/runs');
				$run->setOrderId($order->getId());
				$run->setShipmentId($shipment->getId());
				$run->setScheduleId($schedule->getId());
				$run->save();
			}
				
			$cvsText .= $this->writeLines($shipment, $shipment->getAllItems(), $order, $fileStructur, $fp);
		}
		//print_r($invoices);
		//exit;
		if($schedule == null){
			fclose($fp);
			return $fileName;
		}else{
			return $cvsText;
		}
	}

	public function replaceText($order, $text, $obj){
		$replacements = array (
    		'#{{shipping_data_(.*?)}}#s' => '$item->getData($matches[1])',
    		'#{{shipping_shipping_data_(.*?)}}#s' => 'is_object($item->getShippingAddress())?$item->getShippingAddress()->getData($matches[1]):""',
    		'#{{shipping_shipping_country_name}}#s' => 'is_object($item->getShippingAddress())?$item->getShippingAddress()->getCountryModel()->getName():""', 
    		'#{{shipping_billing_data_(.*?)}}#s' => 'is_object($item->getBillingAddress())?$item->getBillingAddress()->getData($matches[1]):""',
    		'#{{shipping_comments_last_data_(.*?)}}#s' => 'is_object(end($item->getCommentsCollection()->getItems()))?end($item->getCommentsCollection()->getItems())->getData($matches[1]):""',
    		'#{{shipping_billing_country_name}}#s' => 'is_object($item->getBillingAddress())?$item->getBillingAddress()->getCountryModel()->getName():""', 
    		'#{{shipping_track_last_data_(.*?)}}#s' => 'is_object(end($item->getTracksCollection()->getItems()))?end($item->getTracksCollection()->getItems())->getData($matches[1]):""'
		);
		$currentShipping = $obj->current_shipment;
		 
		foreach ($replacements as $key => $value) {
			$obj->callbackParms = array($value, $currentShipping);
			$regCount = 0;
			$text = preg_replace_callback($key, array(&$obj, 'parent::checkReplaceResult'), $text, -1, $regCount);
			if($regCount>0){
				return $text;
			}
		}
		if(preg_match ('#{{shipping_totals_(.*?)}}#s',$text, $match) == 1){
			list($source_field, $totalElement) = explode('_', $match[1], 2);
			$total = Mage::helper('orders2csvpro')->getTotalForDisplaying($currentShipping, $order, $source_field);
			$value = 'is_array($item)?$item[$matches[1]]:""';
			if(isset($total)){
				$obj->callbackParms = array($value, $total);
				$regCount = 0;
				$text = preg_replace_callback('#{{shipping_totals_'.$source_field.'_(.*?)}}#s', array(&$obj, 'checkReplaceResult'), $text, -1, $regCount);
				if($regCount>0){
					return $text;
				}
			}
		}

		$text = parent::replaceText($order, $text, $obj);
		return $text;
	}


	public function replaceItemText($item, $text, $obj){
		$replacements = array ('#{{shipping_item_data_(.*?)}}#s' => '$item->getData($matches[1])');

		foreach ($replacements as $key => $value) {
			$obj->callbackParms = array($value, $item);
			$regCount = 0;
			$text = preg_replace_callback($key, array(&$obj, 'parent::checkReplaceResult'), $text, -1, $regCount);
			if($regCount>0){
				return $text;
			}
		}
		$text = parent::replaceItemText($item, $text, $obj);
		return $text;
	}

	public function replaceBundleItemText($item, $text, $obj){
		$itemShipping = null;
		foreach ($obj->current_shipment->getItemsCollection() as $iitem) {
			if($iitem->getData('order_item_id') == $item->getId()){
				$itemShipping = $iitem;
				continue;
			}
		}
		 
		$replacements = array ('#{{shipping_item_data_(.*?)}}#s' => '$item->getData($matches[1])');
		foreach ($replacements as $key => $value) {
			$obj->callbackParms = array($value, $itemShipping);
			$regCount = 0;
			$text = preg_replace_callback($key, array(&$obj, 'parent::checkReplaceResult'), $text, -1, $regCount);
			if($regCount>0){
				return $text;
			}
		}
		$text = parent::replaceBundleItemText($item, $text, $obj);
		return $text;
	}

	public function isBundleItemIn($item){
		foreach ($this->current_shipment->getItemsCollection() as $iitem) {
			if($iitem->getData('order_item_id') == $item->getId()){
				return true;
			}
		}
		return false;
	}
}