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

$alert = $this->getTable('adjcartalert');
$hist  = $this->getTable('adjcartalert_history');

$installer->run("

ALTER TABLE `$alert` ADD `follow_up` ENUM( 'first', 'second', 'third' ) NOT NULL DEFAULT 'first' AFTER `abandoned_at` ;
ALTER TABLE `$alert` ADD INDEX ( `customer_email` ) ;
ALTER TABLE `$alert` ADD `sheduled_at` DATETIME NOT NULL AFTER `abandoned_at` ;
update `$alert` set `sheduled_at`=now();

ALTER TABLE `$hist` ADD `follow_up` ENUM( 'first', 'second', 'third' ) NOT NULL DEFAULT 'first' AFTER `recovered_at` ;

TRUNCATE TABLE {$this->getTable('cron/schedule')};
");


$installer->endSetup();