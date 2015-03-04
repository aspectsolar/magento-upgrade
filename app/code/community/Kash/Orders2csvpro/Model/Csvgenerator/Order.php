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

class Kash_Orders2csvpro_Model_Csvgenerator_Order extends Kash_Orders2csvpro_Model_Csvgenerator_Abstract
{
	const XPATH_CONFIG_SETTINGS_FILE_ID        	= 'orders2csvpro/settings/file';
	
	public function getCsv($orders = array(), $fileStructur = null, $schedule = null, $test = false)
	{
		if($this->_isActive() == 0 && $fileStructur == null){
			return null;
		}

		$cvsText = "";
		$fp = null;
		
		if($fileStructur == null){
			$fileStructur = Mage::getModel('orders2csvpro/file')->load(Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_FILE_ID));
		}
		
		if($schedule == null){
			$fileName = str_replace(' ', '_', $fileStructur->getTitle());
			$fileName .= '_'.date("Ymd_His").'.csv';
			$fp = fopen(Mage::getBaseDir('export').'/order_'.$fileName, 'w');
			$this->writeTopRow($fileStructur, $fp);
		}elseif($schedule->getShowHeader()==1){
			$cvsText .= $this->writeTopRow($fileStructur);
		}		
		
		foreach ($orders as $order) {
			if ( !$order instanceof Mage_Sales_Model_Order) {
				$order = Mage::getModel('sales/order')->load($order);
			}
			
			if($schedule != null && $test === false) {
				//put in the orders that has been processed in orders2cvs_runs
				$run = Mage::getModel('orders2csvpro/runs');
				$run->setOrderId($order->getId());
				$run->setScheduleId($schedule->getId());
				$run->save();
				if($schedule->getStatusAfter() != null){
					list($type, $status) = explode(",", $schedule->getStatusAfter());
					if(strcasecmp($type, 'order') === 0){
						$order->setState($status, true, Mage::helper('orders2csvpro')->__('Changed by Orders2CSV schedule run'), false);
						$order->save();
					}
				}
			}elseif($test === false){
				if($fileStructur->getStatusAfter() != null){
					list($type, $status) = explode(",", $fileStructur->getStatusAfter());
					if(strcasecmp($type, 'order') === 0){
						$order->setState($status, true, Mage::helper('orders2csvpro')->__('Changed by Orders2CSV manual run'), false);
						$order->save();
					}
				}
			}				
			
			$cvsText .= $this->writeLines($order, $order->getItemsCollection(), $order, $fileStructur, $fp);
		}
		if($this->_isDevMode()){
			print_r($cvsText);
			exit;
		}
		if($schedule == null){
			fclose($fp);
    		return $fileName;
		}else{
			return $cvsText;
		}
	}

	public function isBundleItemIn($item){
		return true;
	}
}