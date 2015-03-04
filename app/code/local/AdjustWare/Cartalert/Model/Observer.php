<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.3.1
 * @license:     lMnQrjiQcxfPlbdwpFhsKslnzVPfVqO45IEZa4tVHs
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * Cartalert module observer
 *
 * @author Adjustware
 */
class Adjustware_Cartalert_Model_Observer
{
    public function createCartalerts()
    {
        $curDate = date('Y-m-d H:i:s');
        $this->_createCartalerts(AdjustWare_Cartalert_Model_Cartalert_Cart::CARTALERT_INSTANCE_TYPE, $curDate);
        if(Mage::helper('adjcartalert')->isAbandonedOrdersEnabled())
        {
            $this->_createCartalerts(AdjustWare_Cartalert_Model_Cartalert_Order::CARTALERT_INSTANCE_TYPE, $curDate);
        }
        $this->sendCartalerts();
        
        return $this;
    }

    /**
     * @param string $instance
     * @param string $curDate
     */
    protected function _createCartalerts($instance, $curDate)
    {
        $cartalertInstance = Mage::getModel('adjcartalert/cartalert_' . $instance);
        $cartalertInstance->generate($curDate);
    }

    public function runStat()
    {
        Mage::getModel('adjcartalert/cronstat')->cron();
        return $this;
    }    
    
    public function sendCartalerts()
    {
        if (!Mage::getStoreConfig('catalog/adjcartalert/sending_enabled'))
            return $this;
        $collection = Mage::getModel('adjcartalert/cartalert')->getCollection()
            ->addReadyForSendingFilter() 
            ->setPageSize(50)
            ->setCurPage(1)
            ->load();
        foreach ($collection as $cartalert){
            if ($cartalert->send()){
                $cartalert->delete(); 
            } 
        }  
        return $this;
    }
    
    public function processOrderCreated($observer){
        $order = $observer->getEvent()->getOrder(); 
        
        if (Mage::getStoreConfig('catalog/adjcartalert/stop_after_order')){
            $cartalert = Mage::getResourceModel('adjcartalert/cartalert')
                ->cancelAlertsFor($order->getCustomerEmail());
        }
        return $this;

    } 
    
    public function updateAlertsStatus($observer)
    {
    	if (!Mage::registry('alerts_status_updated'))
    	{
    		Mage::register('alerts_status_updated', true);
    		
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			
			if ($quote)
			{
				$quote->setAllowAlerts(1);
				
				if (Mage::getStoreConfig('catalog/adjcartalert/stop_after_order')){
		            $cartalert = Mage::getResourceModel('adjcartalert/cartalert')
		                ->cancelAlertsFor($quote->getCustomerEmail());
		        }
			}
    	}
		
        return $this;
    }
}