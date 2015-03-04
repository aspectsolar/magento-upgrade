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
class AdjustWare_Cartalert_Model_Mysql4_Cartalert_Order extends AdjustWare_Cartalert_Model_Mysql4_Cartalert
{
    const XML_PATH_TIMEOUT = 'catalog/adjcartalert/timeout_order';

    protected $_xmlPathFromDate = 'catalog/adjcartalert/order_from_date';
    protected $_delay1Seconds = 3600;
    protected $_xmlPathDelays = 'catalog/adjcartalert/delay_order';

    /**
     * @param string $toDate
     * @return array
     */
    protected function _getAbandonedQuotesIds($toDate)
    {
        $validOrderStatuses = Mage::getStoreConfig('catalog/adjcartalert/stchooser');
        $validOrderStatuses = explode(",", $validOrderStatuses);

        $db = $this->_getReadAdapter();
        $group_id_ignore = Mage::getStoreConfig('catalog/adjcartalert/send_customer_group');
        $sql = $db->select()
            ->from(array('o' => $this->getTable('sales/order')), array())
            ->joinInner(array('osh' => $this->getTable('sales/order_status_history')), 'o.entity_id=osh.parent_id', array())
            ->joinInner(array('q' => $this->getTable('sales/quote')), 'o.quote_id = q.entity_id', array('q.entity_id'))
            ->where('osh.comment IS null OR osh.comment = ""')
            ->where('osh.created_at > ?', $this->_fromDate)
            ->where('osh.created_at < ?', $toDate)
            ->where('o.status IN (?)', $validOrderStatuses)
            ->where('osh.status IN (?)', $validOrderStatuses)
            ->where('q.allow_alerts = 1');
        if($group_id_ignore != '') {
            $sql->where('q.customer_group_id NOT IN ('.$group_id_ignore.')');
        }

        $result = $db->fetchAll($sql);  
        $ids = array();   
        foreach ($result as $row)
        {
            $ids[] = $row['entity_id'];
        }
            
        return $ids;
    }  

    /**
     * @param  array $ids
     * @return array
     */
    protected function _getAbandonedQuotesContent($ids){
        $validOrderStatuses = Mage::getStoreConfig('catalog/adjcartalert/stchooser');
        $validOrderStatuses = explode(",", $validOrderStatuses);

        $db = $this->_getReadAdapter();        
        $fields = $this->_getRequiredFields();
        
        $this->_select = $db->select()
            ->from(array('o' => $this->getTable('sales/order')), $fields)
            ->joinInner(array('osh' => $this->getTable('sales/order_status_history')), 'o.entity_id=osh.parent_id', array())
            ->joinInner(array('q' => $this->getTable('sales/quote')), 'o.quote_id = q.entity_id', array())
            ->joinInner(array('i' => $this->getTable('sales/quote_item')), 'q.entity_id=i.quote_id', array())
            ->joinLeft(array('ba' => $this->getTable('sales/quote_address')), 'q.entity_id=ba.quote_id AND ba.address_type="billing"', array())
            ->where('q.entity_id IN(?)', $ids)
            ->where('IFNULL(q.customer_email, ba.email) IS NOT NULL')
            ->where('i.parent_item_id IS NULL')            
            ->where('o.status IN (?)', $validOrderStatuses)
            ->where('osh.status IN (?)', $validOrderStatuses)
            ->where('osh.created_at > ?', $this->_fromDate)
            ->group('q.entity_id')
            ->limit(50); // we expect that there will be 10-20 carts maximum, because cron runs each hour
        $this->_addFilter('visibility', Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());
        $this->_addFilter('status', Mage::getSingleton('catalog/product_status')->getVisibleStatusIds());
 
        return $db->fetchAll($this->_select); 
    }

    protected function _getTimeout()
    {
        return intVal(Mage::getStoreConfig(self::XML_PATH_TIMEOUT)) * 60;
    }
}