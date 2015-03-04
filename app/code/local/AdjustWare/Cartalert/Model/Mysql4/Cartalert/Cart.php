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
class AdjustWare_Cartalert_Model_Mysql4_Cartalert_Cart extends AdjustWare_Cartalert_Model_Mysql4_Cartalert
{
    const XML_PATH_TIMEOUT = 'catalog/adjcartalert/timeout_order';

    protected $_xmlPathFromDate = 'catalog/adjcartalert/from_date';
    protected $_delay1Seconds = 60;
    protected $_xmlPathDelays = 'catalog/adjcartalert/delay';

    /**
     * @param string $toDate
     * @return array
     */
    protected function _getAbandonedQuotesIds($toDate)
    {
        $db = $this->_getReadAdapter();
        $group_id_ignore = Mage::getStoreConfig('catalog/adjcartalert/send_customer_group');
        $sql = $db->select()
            ->from(array('q' => $this->getTable('sales/quote')), array('q.entity_id'))
            ->where('q.updated_at > ?', $this->_fromDate)
            ->where('q.updated_at < ?', $toDate)
            ->where('q.allow_alerts = 1');
        if($group_id_ignore != '') {
            $sql->where('q.customer_group_id NOT IN ('.$group_id_ignore.')');
        }
        $sql->where('q.is_active=1');
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
        $db = $this->_getReadAdapter();
        $fields = $this->_getRequiredFields();
        
        $this->_select = $db->select()
            ->from(array('q' => $this->getTable('sales/quote')), $fields)
            ->joinInner(array('i' => $this->getTable('sales/quote_item')), 'q.entity_id=i.quote_id', array())
            ->joinLeft(array('ba' => $this->getTable('sales/quote_address')), 'q.entity_id=ba.quote_id AND ba.address_type="billing"', array())
            ->where('q.entity_id IN(?)', $ids)
            ->where('IFNULL(q.customer_email, ba.email) IS NOT NULL')
            ->where('i.parent_item_id IS NULL')
            ->group('q.entity_id')
            ->limit(50); // we expect that there will be 10-20 carts maximum, because cron runs each hour
        $this->_addFilter('visibility', Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());
        $this->_addFilter('status', Mage::getSingleton('catalog/product_status')->getVisibleStatusIds());

        return $db->fetchAll($this->_select); 
    }

    protected function _getTimeout()
    {
        return intVal(Mage::getStoreConfig(self::XML_PATH_TIMEOUT));
    }
}