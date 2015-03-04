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
ALTER TABLE `$hist` ADD `customer_id` INT UNSIGNED NOT NULL AFTER `id` ,
ADD `recover_code` CHAR( 32 ) NOT NULL AFTER `customer_id` ;

ALTER TABLE `$hist` ADD `quote_id` INT UNSIGNED NOT NULL AFTER `id` ;
ALTER TABLE `$hist` ADD `recovered_at` DATETIME DEFAULT NULL AFTER `sent_at` ;

ALTER TABLE `$hist` ADD `recovered_from` VARCHAR( 32 ) NOT NULL AFTER `sent_at` ;


ALTER TABLE `$alert` ADD `customer_id` INT UNSIGNED NOT NULL AFTER `store_id` ;

ALTER TABLE `$alert` ADD `quote_id` INT UNSIGNED NOT NULL AFTER `customer_id` ;

ALTER TABLE `$alert` CHANGE `abandoned_at` `abandoned_at` DATETIME NULL DEFAULT NULL;

");


$installer->endSetup();