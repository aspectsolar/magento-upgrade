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
class AdjustWare_Cartalert_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ABANDONED_ORDER_STATUSES = 'catalog/adjcartalert/stchooser';
    const XML_PATH_FROM_DATE = 'catalog/adjcartalert/from_date';
    const XML_PATH_ORDER_FROM_DATE = 'catalog/adjcartalert/order_from_date';

    public function getGroupArray()
    {
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $db->select()->from(Mage::getSingleton('core/resource')->getTableName('customer/customer_group'), array('customer_group_id', 'customer_group_code'));
        $groupIds = array();
        foreach($db->fetchAll($select) as $group)
        {
            $groupIds[$group['customer_group_id']] = $group['customer_group_code'];
        }
        return $groupIds;
    }

    public function isAbandonedOrdersEnabled()
    {
        if(Mage::getStoreConfig(self::XML_PATH_ABANDONED_ORDER_STATUSES) != '')
        {
            return true;
        }

        return false;
    }
}