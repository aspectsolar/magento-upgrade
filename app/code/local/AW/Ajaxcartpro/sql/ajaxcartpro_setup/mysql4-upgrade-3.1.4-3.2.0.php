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

try {
    $this->startSetup();
    $this->run("
        CREATE TABLE IF NOT EXISTS {$this->getTable('ajaxcartpro/promo')}  (
            `rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `type` smallint(6) NOT NULL,
            `description` text,
            `is_active` smallint(6) DEFAULT 1,
            `from_date` date DEFAULT NULL,
            `to_date` date DEFAULT NULL,
            `store_ids` varchar(255) NOT NULL DEFAULT '0',
            `customer_groups` varchar(255) NOT NULL,
            `priority` int(10) unsigned NOT NULL DEFAULT '0',
            `conditions_serialized` mediumtext,
            `rule_actions_serialized` mediumtext,
            PRIMARY KEY (`rule_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
    $this->endSetup();
} catch (Exception $e) {
    echo $e->getMessage();
    Mage::logException($e);
}