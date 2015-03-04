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
class AdjustWare_Cartalert_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('adjcartalert/history', 'id');
    }

    /**
     * @param  string|integer $quoteId
     * @return string
     */
    public function getReorderUrl($quoteId)
    {        
        $db = $this->_getReadAdapter();
        $this->_select = $db->select()
                             ->from(array('o' => $this->getTable('sales/order')), array('o.entity_id'))
                             ->where('o.quote_id = ?', $quoteId);
        $orderId = $db->fetchOne($this->_select);
        $order = Mage::getModel('sales/order')->load($orderId);

        return $this->_getReorderUrl($order);
    }

    /**
     * @param  string|integer $quoteId
     * @return integer
     */
    public function getOrderId($quoteId)
    {        
        $db = $this->_getReadAdapter();
        $this->_select = $db->select()
                             ->from(array('o' => $this->getTable('sales/order')), array('o.entity_id'))
                             ->where('o.quote_id = ?', $quoteId);
        $orderId = $db->fetchOne($this->_select);
        $order = Mage::getModel('sales/order')->load($orderId);

        return $order->getId();
    }

    /**
     * @param  Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _getReorderUrl($order)
    {
        return 'sales/order/reorder/order_id/' . $order->getId();
    }
}