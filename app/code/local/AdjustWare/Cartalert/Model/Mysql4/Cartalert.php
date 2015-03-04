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
class AdjustWare_Cartalert_Model_Mysql4_Cartalert extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_select;

    /**
     * @var string
     */
    protected $_fromDate;

    public function _construct()
    {    
        $this->_init('adjcartalert/cartalert', 'cartalert_id');
    }

    /**
     * @return array
     */
    protected function _getRequiredFields()
    {
        $fields = array(
            'store_id'         => 'q.store_id', 
            'quote_id'         => 'q.entity_id', 
            'customer_id'      => 'q.customer_id', 
            'customer_email'   => new Zend_Db_Expr('IFNULL(q.customer_email, ba.email)'),
            'customer_fname'   => new Zend_Db_Expr('IFNULL(q.customer_firstname, ba.firstname)'),
            'customer_lname'   => new Zend_Db_Expr('IFNULL(q.customer_lastname, ba.lastname)'),
            'products'         => new Zend_Db_Expr('GROUP_CONCAT(CONCAT(i.product_id,"##,",i.name,"##"))'), 
            'abandoned_at'     => 'q.updated_at',
            'customer_group_id'     => 'q.customer_group_id',
            'quote_is_active'        => 'q.is_active',
        );

        return $fields;
    }

    /**
     * @param string $toDate
     * @return array
     */
    protected function _getAbandonedQuotesIds($toDate)
    {
        return array();
    }
    
    /**
     * @param string $now
     * @return array
     */
    public function generate($now){
        $toDate = $this->_updateDates($now);
        $ids = $this->_getAbandonedQuotesIds($toDate);
        if (!$ids)
            return array($this->_fromDate, $toDate);
        
        $quotes = $this->_getAbandonedQuotesContent($ids);        
        if (!$quotes)
            return array($this->_fromDate, $toDate);        

        $this->_insertAlertToDatabase($quotes);

        return array($this->_fromDate, $toDate);
    }

    /**
     * @param string $now
     * @return string
     */
    protected function _updateDates($now){
        $timeout = $this->_getTimeout();
        $toDate = date('Y-m-d H:i:s', strtotime($now) - 60*$timeout);
        $this->_loadFromDate();        
        $this->_saveFromDate($toDate);  
        
        return $toDate;      
    }

    /**
     * @param string $storeId
     * @return array
     */
    protected function _getDelaysConfig($storeId)
    {
        $delay1 = Mage::getStoreConfig($this->_xmlPathDelays, $storeId);
        $delay2 = Mage::getStoreConfig($this->_xmlPathDelays . '2', $storeId);
        $delay3 = Mage::getStoreConfig($this->_xmlPathDelays . '3', $storeId);

        return array($delay1 * $this->_delay1Seconds, $delay2 * 3600, $delay3 * 3600);
    }

    protected function _loadFromDate(){
        $db = $this->_getReadAdapter();
        $sql = 'SELECT value FROM ' . $this->getTable('core/config_data') 
             . ' WHERE scope="default" AND path="' . $this->_xmlPathFromDate . '"'
             . ' LIMIT 1';
        $this->_fromDate = $db->fetchOne($sql);
    }

    /**
     * @param string $toDate
     */
    protected function _saveFromDate($toDate){
        $db = $this->_getWriteAdapter();
        $sql = 'UPDATE ' . $this->getTable('core/config_data') . ' SET `value` = "'. $toDate .'"'
             . ' WHERE scope="default" AND path="' . $this->_xmlPathFromDate .'"'     
             . ' LIMIT 1';
        $db->query($sql);    
    }

    /**
     * @param array $carts
     */
    protected function _insertAlertToDatabase($carts)
    {
        // START creating insert SQL to schedule follow-ups    
        $db = $this->_getReadAdapter();     
        $insertSql = 'INSERT INTO ' . $this->getMainTable() . '(' . join(',', array_keys($carts[0])) . ', follow_up, sheduled_at) VALUES ';
        //to prevent executing empty sql
        $insertUpdated = false;        
        $stoplist = Mage::getModel('adjcartalert/stoplist');

        foreach ($carts as $row){
            if(!$stoplist->contains(Mage::app()->getStore($row['store_id'])->getGroup()->getId(), $row['customer_email']) && !$stoplist->contains(0, $row['customer_email'])) {
                $insertUpdated = true;
                $vals = '';
                foreach ($row as $field){
                    $vals .= $db->quote($field) . ',';
                }

                $abandoned_at = strtotime($row['abandoned_at']);
                list($delay1, $delay2, $delay3) = $this->_getDelaysConfig($row['store_id']);

                if ($delay1){
                    $sheduled_at = date('Y-m-d H:i:s', $abandoned_at + $delay1);
                    $insertSql .= "($vals 'first', '$sheduled_at'),";
                }

                if ($delay2){
                    $sheduled_at = date('Y-m-d H:i:s', $abandoned_at + $delay2);
                    $insertSql .= "($vals 'second', '$sheduled_at'),";
                }

                if ($delay3){
                    $sheduled_at = date('Y-m-d H:i:s', $abandoned_at + $delay3);
                    $insertSql .= "($vals 'third', '$sheduled_at'),";
                }
            }
            $dbw = $this->_getWriteAdapter();
            $sql = 'UPDATE ' . $this->getTable('sales/quote') . ' SET `allow_alerts` = "0"'
                . ' WHERE entity_id="' . $row['quote_id'] . '"'
                . ' LIMIT 1';
            $dbw->query($sql);

            Mage::dispatchEvent('adjustware_cartalert_alert_generate_after', array('quote'=>$row));        
        }

        // END creating SQL, finally insert records in bulk
        if($insertUpdated) {
            $db->raw_query(substr($insertSql, 0, -1));
        }
    }

    protected function _addFilter($attributeCode, $value)
    {
        $attribute = Mage::getSingleton('catalog/product')->getResource()->getAttribute($attributeCode);
        $t  = 't1_'.$attributeCode;
        $t2 = 't2_'.$attributeCode;

        $this->_select->join(
            array($t => $attribute->getBackend()->getTable()),
            'i.product_id='.$t.'.entity_id AND '.$t.'.store_id=0',
            array()
        )
        ->joinLeft(
            array($t2 => $attribute->getBackend()->getTable()),
            $t.'.entity_id = '.$t2.'.entity_id AND '.$t.'.attribute_id = '.$t2.'.attribute_id AND '.$t2.'.store_id=q.store_id',
            array()
        )
        ->where($t.'.attribute_id=?', $attribute->getId())
        ->where('IFNULL('.$t2.'.value, '.$t.'.value) IN(?)', $value);

        return true;
    }

    /**
     * @param string $email
     */
    public function cancelAlertsFor($email){
        $db = $this->_getWriteAdapter();
        $db->delete($this->getMainTable(), 'customer_email = ' . $db->quote($email));
    }
}