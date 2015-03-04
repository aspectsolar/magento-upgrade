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
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('adjcartalert_stoplist')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `store_id` smallint UNSIGNED NOT NULL ,
  `customer_email` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `email` (`store_id`, `customer_email`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();