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

ALTER TABLE `".$installer->getTable('adjcartalert/cartalert')."` ADD `customer_group_id` INT(10) UNSIGNED DEFAULT NULL;

");

$installer->endSetup();