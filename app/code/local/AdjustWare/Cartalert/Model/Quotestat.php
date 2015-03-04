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
class AdjustWare_Cartalert_Model_Quotestat extends Mage_Core_Model_Abstract
{

    protected $_followupArray = array('first'=>1,'second'=>2,'third'=>3,''=>0);

    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/quotestat');
    }
    
    public function onOrderCreate($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->load($order->getQuoteId(),'quote_id');
        if($this->getId() && $this->getRecoveryDate())
        {
            $this->setOrderDate($order->getCreatedAt());
            $this->setOrderPrice($order->getBaseGrandTotal());
            $items = array();
            foreach($order->getAllItems() as $item)
            {
                if($item->getProductType() == 'simple')
                {            
                    $items[$item->getProductId()] = $item->getQtyOrdered();
                }
            }
            $this->setOrderItems(serialize($items));        
            $this->setOrderCouponUsed($order->getCouponCode());
            $this->save();
        }
    }
    
    public function onAlertGenerate($observer)
    {
        $quoteData = $observer->getEvent()->getQuote();
        $quote = Mage::getModel('sales/quote')->setStoreId($quoteData['store_id'])->load($quoteData['quote_id']);
        $instance = new self;
        if($quote->getEntityId())
        {
            $instance->load($quote->getEntityId(),'quote_id');
            $instance->setQuoteId($quote->getEntityId());
            $instance->setCartPrice($quote->getBaseGrandTotal());
            $instance->setCartAbandonDate($quoteData['abandoned_at']);
            $items = array();
            foreach($quote->getAllItems() as $item)
            {
                if($item->getProductType() == 'simple')
                {        
                    $items[$item->getProductId()] = $item->getQty();
                }
            }
            $instance->setCartItems(serialize($items));
            $instance->save();
        }
    }
    
    public function onAlertSend($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $history = $observer->getEvent()->getHistory();
        $this->load($quote->getQuoteId(),'quote_id');
        if($this->getId())
        {
            $this->setAlertNumber($this->_followupArray[$quote->getFollowUp()]);
            $this->setAlertDate(date('Y-m-d H:i:s'));
            $this->setAlertCouponGenerated($history->getCouponCode());
            $this->save();
        }
    }
    
    public function onCartRecovery($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $this->load($quote->getEntityId(),'quote_id');
        if($this->getId())
        {        
            $this->setRecoveryDate(date('Y-m-d H:i:s'));
            $this->save();
        }
    }    
    
    public static function collectDay($date)
    {
        $instance = new self;        
        $quoteCollection = Mage::getModel('Mage_Reports_Model_Mysql4_Quote_Collection')->prepareForAbandonedReport(0);
        $quoteCollection->getSelect()->where('`main_table`.`updated_at` BETWEEN \''.$date.'\' AND \''.$date.'\' + INTERVAL 1 DAY');
        foreach($quoteCollection as $quote)        
        {
            $instance->load($quote->getEntityId(),'quote_id');
            
            $instance->setQuoteId($quote->getEntityId());
            $instance->setCartPrice($quote->getBaseGrandTotal());
            $instance->setCartAbandonDate($quote->getUpdatedAt());
            $items = array();
            foreach($quote->getAllItems() as $item)
            {
                if($item->getProductType() == 'simple')
                {                 
                    $items[$item->getProductId()] = $item->getQty();
                }
            }
            $instance->setCartItems(serialize($items));
            
            $historyCollection = Mage::getModel('adjcartalert/history')->getCollection();
            $historyCollection->addFieldToFilter('quote_id', $quote->getEntityId())->setOrder('sent_at','DESC');
            $history = $historyCollection->getFirstItem();
        
            $instance->setAlertNumber($instance->_followupArray[$history->getFollowUp()]);
            $instance->setAlertDate($history->getSheduledAt());
            $instance->setRecoveryDate($history->getRecoveredAt());
            
            $orderCollection = Mage::getModel('sales/order')->getCollection();
            $order = $orderCollection->addFieldToFilter('quote_id',$quote->getEntityId())->getFirstItem();
            
            if($order->getId())
            {
                $instance->setOrderDate($order->getCreatedAt());
                $instance->setOrderPrice($order->getBaseGrandTotal());
                $items = array();
                foreach($order->getAllItems() as $item)
                {
                    if($item->getProductType() == 'simple')
                    {                     
                        $items[$item->getProductId()] = $item->getQtyOrdered();
                    }
                }
                $instance->setOrderItems(serialize($items));        
                $instance->setOrderCouponUsed($order->getCouponCode());
            }
            
            $instance->save();
        }
    }
    
}