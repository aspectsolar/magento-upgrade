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
class AdjustWare_Cartalert_Block_Adminhtml_Quotestat_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; // ?
        $this->_blockGroup = 'adjcartalert';
        $this->_controller = 'adminhtml_quotestat';
        $this->_mode = 'view';
        
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('delete');        
    
    
        $data = Mage::registry('quotestat_data');
        $this->_order = Mage::getModel('sales/order')->getCollection()->addFieldTofilter('quote_id', array('eq'=>$data->getQuoteId()))->getFirstItem();
        $quote = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('entity_id', array('eq'=>$data->getQuoteId()))->getFirstItem();
        $this->_customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
        $this->_customerGroup = Mage::getModel('customer/group')->load($this->_customer->getGroupId());    
    
        if($this->_order->getId())
        {
            $this->_addButton('order_view', array(
                'label'     => Mage::helper('sales')->__('View Order'),
                'onclick'   => 'setLocation(\'' .  Mage::helper("adminhtml")->getUrl('adminhtml/sales_order/view', array('order_id'=>$this->_order->getId())) . '\')',
            ));
        }
    
    }

    public function getHeaderText()
    {
            return Mage::helper('adjcartalert')->__('Abandoned Carts Statistic');
    }
    
    public function getOrder()
    {
        return $this->_order;
    }

    public function getCustomer()
    {
        return $this->_customer;
    }         
    
    public function getCustomerGroup()
    {
        return $this->_customerGroup;
    }     
    
}