<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxcartpro
 * @version    3.2.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ajaxcartpro_Model_Resource_Promo_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ajaxcartpro/promo');
    }

    public function addStoreFilter($store)
    {
        $this
            ->getSelect()
            ->where("FIND_IN_SET(0, store_ids) OR FIND_IN_SET({$store}, store_ids)")
        ;
        return $this;
    }

    public function addTypeFilter($ruleType)
    {
        return $this->addFieldToFilter('type', $ruleType);
    }

    public function addActiveFilter()
    {
        return $this->addFieldToFilter('is_active', 1);
    }

    public function addDateFilter($now = null)
    {
        if (is_null($now)) {
            $now = Mage::getModel('core/date')->date('Y-m-d');
        }
        $this
            ->getSelect()
            ->where('from_date is null or from_date <= ?', $now)
            ->where('to_date is null or to_date >= ?', $now)
        ;
        return $this;
    }

    public function addCustomerGroupFilter($groupId)
    {
        $this
            ->getSelect()
            ->where("FIND_IN_SET({$groupId}, customer_groups)")
        ;
        return $this;
    }

    public function addSortOrderByPriority()
    {
        return $this->setOrder('priority');
    }
}