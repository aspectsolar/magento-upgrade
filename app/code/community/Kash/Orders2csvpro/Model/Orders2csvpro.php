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
class Kash_Orders2csvpro_Model_Orders2csvpro extends Mage_Core_Model_Abstract
{
    const XPATH_CONFIG_SETTINGS_IS_ACTIVE		= 'orders2csvpro/settings/is_active';
	const XPATH_CONFIG_SETTINGS_FILE_ID        	= 'orders2csvpro/settings/file';
	const ENCLOSURE = '"';
    const DELIMITER = ',';

    //sending the csv file as attachment in email
    public function sendEmailAction($schedule, $cvsText, $fileStructur, $fileName = null) {
    	
    	if($fileName == null){
    		$fileName = str_replace(' ', '_', $fileStructur->getTitle());
    		$fileName .= '_'.date("Ymd_His").'.csv';
    	}
    	
    	$mailTemplate = Mage::getModel('core/email_template');
    	$mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
    	$mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
    	$mailTemplate->setTemplateSubject($schedule->getTitle());
    	
    	if($schedule->getAttached() == 1){
	    	$mailTemplate->getMail()->createAttachment(
		    	$cvsText,
		    	Zend_Mime::TYPE_OCTETSTREAM,
		    	Zend_Mime::DISPOSITION_ATTACHMENT,
		    	Zend_Mime::ENCODING_BASE64,
	    		$fileName
	    	);
    	}else{
    		$mailTemplate->setTemplateText($cvsText);
    	}
    	
    	//Save file in export
    	$fp = fopen(Mage::getBaseDir('export').'/'.$fileName, 'w');
    	fwrite($fp, $cvsText);
    	fclose($fp);
    	 
    	$mailTemplate->send($schedule->getEmail());
    }
    
    /**
    * Cron function being called
    *
    */
    public function cronRun($test = false, $scheduleId = null){
    	if(Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_IS_ACTIVE) == 0 && $scheduleId == null){
    		return;
    	}
    	
    	if($scheduleId === null){
    		$schedules = Mage::getModel('orders2csvpro/schedule')->getActiveSchedules();
    		$test = false;
    	}else{
    		$schedules[] = Mage::getModel('orders2csvpro/schedule')->load($scheduleId);
    	}
    	
    	foreach($schedules as $schedule){
    		$doRun = false;
    		if($schedule->getPeriode() == 1 || $test){
    			//Send hourly
    			$doRun = true;
    		}elseif(date("G")==0 && $schedule->getPeriode() == 2){
    			//Send dayli
    			$doRun = true;    			 
    		}elseif(date('w') == 0 && date("G")==0 && $schedule->getPeriode() == 3){
    			//Send weekly
    			$doRun = true;    			 
    		}elseif(date('j') == 1 && date("G")==0 && $schedule->getPeriode() == 4){
    			//Send monthly
    			$doRun = true;    			 
    		}elseif((date('n') == 1 || date('n') == 4 || date('n') == 7 || date('n') == 10) && date('j') == 1 && date("G")==0 && $schedule->getPeriode() == 5){
    			//Send Quarterly 
    			$doRun = true;    			 
    		}elseif((date('n') == 1 || date('n') == 7 ) && date('j') == 1 && date("G")==0 && $schedule->getPeriode() == 6){
    			//Send Semi-annually 
    			$doRun = true;    			 
    		}elseif(date('z') == 0 && date("G")==0 && $schedule->getPeriode() == 7){
    			//Send Annually 
    			$doRun = true;    			 
    		}
    		
    		if($doRun){
    			$fileStructur = Mage::getModel('orders2csvpro/file')->load($schedule->getFileId());
    			
    			switch ($fileStructur->getType()){
    				case 2:
    					$elementList = Mage::getModel('orders2csvpro/runs')->getAllInvoicesNotRun($schedule);
    					break;
    				case 3:
    					$elementList = Mage::getModel('orders2csvpro/runs')->getAllShipmentsNotRun($schedule);
    					break;
    				case 4:
    					$elementList = Mage::getModel('orders2csvpro/runs')->getAllCreditmemosNotRun($schedule);
    					break;
    				default:
    					$elementList = Mage::getModel('orders2csvpro/runs')->getAllOrdersNotRun($schedule);
    			}
    			
    			if(count($elementList)>0){
    			    switch ($fileStructur->getType()){
	    				case 2:
	    					$cvsText = Mage::getModel('orders2csvpro/csvgenerator_invoice')->getCsv($elementList, $fileStructur, $schedule, $test);
	    					break;
	    				case 3:
	    					$cvsText = Mage::getModel('orders2csvpro/csvgenerator_shipment')->getCsv($elementList, $fileStructur, $schedule, $test);
	    					break;
	    				case 4:
	    					$cvsText = Mage::getModel('orders2csvpro/csvgenerator_creditmemo')->getCsv($elementList, $fileStructur, $schedule, $test);
	    					break;
	    				default:
	    					$cvsText = Mage::getModel('orders2csvpro/csvgenerator_order')->getCsv($elementList, $fileStructur, $schedule, $test);
	    			}
    				
	    			$this->sendEmailAction($schedule, $cvsText, $fileStructur);
	    			$schedule->setLastRun(Mage::getSingleton('core/date')->gmtDate());
	    			$schedule->save();
	    			
	    			Mage::log("Orders2CSV PRO - Schedule ".$schedule->getTitle()." has run with ".count($elementList)." elements", Zend_Log::INFO);
    			}else{
    				Mage::log("Orders2CSV PRO - Schedule ".$schedule->getTitle()." has NOT run - No new elements", Zend_Log::INFO);
    			}
    		}
    	}
    }
}
?>