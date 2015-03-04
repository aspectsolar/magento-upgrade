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

class Kash_Orders2csvpro_Model_Csvgenerator_Creditmemo extends Kash_Orders2csvpro_Model_Csvgenerator_Abstract
{
	public $current_creditmemo;
	const XPATH_CONFIG_SETTINGS_CREDITMEMO_FILE_ID        	= 'orders2csvpro/settings/file_creditmemo';
	
	public function getCsv($creditmemos = array(), $fileStructur = null, $schedule = null, $test = false)
	{
		if($this->_isActive() == 0 && $fileStructur == null){
			return null;
		}
	
		$cvsText = "";
		$fp = null;
	
		if($fileStructur == null){
			$fileStructur = Mage::getModel('orders2csvpro/file')->load(Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_CREDITMEMO_FILE_ID));
		}
	
		if($schedule == null){
			$fileName = str_replace(' ', '_', $fileStructur->getTitle());
			$fileName .= '_'.date("Ymd_His").'.csv';
			$fp = fopen(Mage::getBaseDir('export').'/creditmemo_'.$fileName, 'w');
			$this->writeTopRow($fileStructur, $fp);
		}elseif($schedule->getShowHeader()==1){
			$cvsText .= $this->writeTopRow($fileStructur);
		}
	
		foreach ($creditmemos as $creditmemo) {
			if ( !$creditmemo instanceof Mage_Sales_Model_Order_Creditmemo) {
				$creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemo);
			}
				
			$order = $creditmemo->getOrder();
			$this->current_creditmemo = $creditmemo;
	
			if($schedule != null && $test === false) {
				//put in the orders that has been processed in orders2cvs_runs
				$run = Mage::getModel('orders2csvpro/runs');
				$run->setOrderId($order->getId());
				$run->setCreditmemoId($creditmemo->getId());
				$run->setScheduleId($schedule->getId());
				$run->save();
				if($schedule->getStatusAfter() != null){
					list($type, $status) = explode(",", $schedule->getStatusAfter());
					if(strcasecmp($type, 'order') === 0){
						$order->setState($status, true, Mage::helper('orders2csvpro')->__('Changed by Orders2CSV schedule run'), false);
						$order->save();
					}elseif(strcasecmp($type, 'credit') === 0){
						$creditmemo->setState($status);
						$creditmemo->addComment(Mage::helper('orders2csvpro')->__('State changed by Orders2CSV schedule run'));
						$creditmemo->save();
					}
				}
			}elseif($test === false){
				if($fileStructur->getStatusAfter() != null){
					list($type, $status) = explode(",", $fileStructur->getStatusAfter());
					if(strcasecmp($type, 'order') === 0){
						$order->setState($status, true, Mage::helper('orders2csvpro')->__('Changed by Orders2CSV manual run'), false);
						$order->save();
					}elseif(strcasecmp($type, 'credit') === 0){
						$creditmemo->setState($status);
						$creditmemo->addComment(Mage::helper('orders2csvpro')->__('State changed by Orders2CSV manual run'));
						$creditmemo->save();
					}
				}
			}	
				
			$cvsText .= $this->writeLines($creditmemo, $creditmemo->getAllItems(), $order, $fileStructur, $fp);
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
    		'#{{creditmemo_data_(.*?)}}#s' => '$item->getData($matches[1])',
    		'#{{creditmemo_shipping_data_(.*?)}}#s' => 'is_object($item->getShippingAddress())?$item->getShippingAddress()->getData($matches[1]):""',
    		'#{{creditmemo_shipping_country_name}}#s' => 'is_object($item->getShippingAddress())?$item->getShippingAddress()->getCountryModel()->getName():""', 
    		'#{{creditmemo_billing_data_(.*?)}}#s' => 'is_object($item->getBillingAddress())?$item->getBillingAddress()->getData($matches[1]):""',
    		'#{{creditmemo_billing_country_name}}#s' => 'is_object($item->getBillingAddress())?$item->getBillingAddress()->getCountryModel()->getName():""', 
    		'#{{creditmemo_comments_last_data_(.*?)}}#s' => 'is_object(end($item->getCommentsCollection()->getItems()))?end($item->getCommentsCollection()->getItems())->getData($matches[1]):""'
		);
		$currentCreditmemo = $obj->current_creditmemo;
		 
		foreach ($replacements as $key => $value) {
			$obj->callbackParms = array($value, $currentCreditmemo);
			$regCount = 0;
			$text = preg_replace_callback($key, array(&$obj, 'parent::checkReplaceResult'), $text, -1, $regCount);
			if($regCount>0){
				return $text;
			}
		}
		if(preg_match ('#{{creditmemo_totals_(.*?)}}#s',$text, $match) == 1){
			list($source_field, $totalElement) = explode('_', $match[1], 2);
			$total = Mage::helper('orders2csvpro')->getTotalForDisplaying($currentCreditmemo, $order, $source_field);
			$value = 'is_array($item)?$item[$matches[1]]:""';
			if(isset($total)){
				$obj->callbackParms = array($value, $total);
				$regCount = 0;
				$text = preg_replace_callback('#{{creditmemo_totals_'.$source_field.'_(.*?)}}#s', array(&$obj, 'checkReplaceResult'), $text, -1, $regCount);
				if($regCount>0){
					return $text;
				}
			}
		}

		$text = parent::replaceText($order, $text, $obj);
		return $text;
	}


	public function replaceItemText($item, $text, $obj){
		$replacements = array ('#{{creditmemo_item_data_(.*?)}}#s' => '$item->getData($matches[1])');
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
		$itemCreditmemo = null;
		foreach ($obj->current_creditmemo->getItemsCollection() as $iitem) {
			if($iitem->getData('order_item_id') == $item->getId()){
				$itemCreditmemo = $iitem;
				continue;
			}
		}

		$replacements = array ('#{{creditmemo_item_data_(.*?)}}#s' => '$item->getData($matches[1])');
		foreach ($replacements as $key => $value) {
			$obj->callbackParms = array($value, $itemCreditmemo);
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
		foreach ($this->current_creditmemo->getItemsCollection() as $iitem) {
			if($iitem->getData('order_item_id') == $item->getId()){
				return true;
			}
		}
		return false;
	}
}