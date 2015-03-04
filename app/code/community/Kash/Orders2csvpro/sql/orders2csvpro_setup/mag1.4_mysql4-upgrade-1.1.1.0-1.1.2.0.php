<?php

$installer = $this;
$installer->startSetup();

$updateString = <<<EOT

ALTER TABLE {$installer->getTable('orders2csvpro/file')} ADD COLUMN `delimiter` varchar(10) NOT NULL default ',' COMMENT 'Delimiter for file'; 
ALTER TABLE {$installer->getTable('orders2csvpro/file')} ADD COLUMN `enclosure` varchar(10) NOT NULL default '\"' COMMENT 'Enclosure for variables in file';
ALTER TABLE {$installer->getTable('orders2csvpro/file')} ADD COLUMN `type` smallint NOT NULL default '1' COMMENT 'File type (order, invoice etc)';
ALTER TABLE {$installer->getTable('orders2csvpro/runs')} ADD COLUMN `invoice_id` int NULL default NULL COMMENT 'Invoices run';
ALTER TABLE {$installer->getTable('orders2csvpro/runs')} ADD COLUMN `shipment_id` int NULL default NULL COMMENT 'Shipment run';
ALTER TABLE {$installer->getTable('orders2csvpro/runs')} ADD COLUMN `creditmemo_id` int NULL default NULL COMMENT 'Creditmemo run';

ALTER TABLE {$installer->getTable('orders2csvpro/runs')} MODIFY COLUMN invoice_id int UNSIGNED NULL;
ALTER TABLE {$installer->getTable('orders2csvpro/runs')} MODIFY COLUMN shipment_id int UNSIGNED NULL;
ALTER TABLE {$installer->getTable('orders2csvpro/runs')} MODIFY COLUMN creditmemo_id int UNSIGNED NULL;

ALTER TABLE {$installer->getTable('orders2csvpro/runs')} ADD CONSTRAINT `FK_ORDERS2CSV_RUNS_INVOICE_ID_SALES_FLAT_INVOICE_ENTITY_ID` FOREIGN KEY (`invoice_id`) REFERENCES `{$this->getTable('sales/invoice')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE {$installer->getTable('orders2csvpro/runs')} ADD CONSTRAINT `FK_ORDERS2CSV_RUNS_SHIPMENT_ID_SALES_FLAT_SHIPMENT_ENTITY_ID` FOREIGN KEY (`shipment_id`) REFERENCES `{$this->getTable('sales/shipment')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE {$installer->getTable('orders2csvpro/runs')} ADD CONSTRAINT `FK_ORDERS2CSV_RUNS_CREDITMEMO_ID_SALES_FLAT_CREDITMEMO_ENTITY_ID` FOREIGN KEY (`creditmemo_id`) REFERENCES `{$this->getTable('sales/creditmemo')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

EOT;

$installer->run($updateString);

$updateString1 = <<<EOT

INSERT INTO {$installer->getTable('orders2csvpro/file')} (`file_id`, `title`, `is_active`, `num_formatting`, `creation_time`, `update_time`, `delimiter`, `enclosure`, `type`) VALUES
(NULL, 'BasicInvoiceInfo', 1, 1, NULL, NULL, ',', '"', 2),
(NULL, 'BasicShipmentInfo', 1, 1, NULL, NULL, ',', '"', 3),
(NULL, 'BasicCreditmemoInfo', 1, 1, NULL, NULL, ',', '"', 4);

EOT;
$installer->run($updateString1);

$fileBasicInvoiceInfo = $installer->getConnection()->fetchOne("SELECT file_id FROM {$installer->getTable('orders2csvpro/file')} WHERE title = 'BasicInvoiceInfo'");
$fileBasicShipmentInfo = $installer->getConnection()->fetchOne("SELECT file_id FROM {$installer->getTable('orders2csvpro/file')} WHERE title = 'BasicShipmentInfo'");
$fileBasicCreditmemoInfo = $installer->getConnection()->fetchOne("SELECT file_id FROM {$installer->getTable('orders2csvpro/file')} WHERE title = 'BasicCreditmemoInfo'");

$updateString2 = <<<EOT

INSERT INTO `{$installer->getTable('orders2csvpro/column')}` (`column_id`, `file_id`, `title`, `sort_order`, `value`, `creation_time`, `update_time`) VALUES
(NULL, {$fileBasicInvoiceInfo}, 'Order id', 10, '{{order_data_increment_id}}', NULL, NULL),
(NULL, {$fileBasicInvoiceInfo}, 'Order created at', 80, '{{format_date_short order_data_created_at}}', NULL, NULL),
(NULL, {$fileBasicInvoiceInfo}, 'Invoice total qty', 50, '{{invoice_data_total_qty}}', NULL, NULL),
(NULL, {$fileBasicInvoiceInfo}, 'Order currency code', 60, '{{invoice_data_order_currency_code}}', NULL, NULL),
(NULL, {$fileBasicInvoiceInfo}, 'Invoice id', 20, '{{invoice_data_increment_id}}', NULL, NULL),
(NULL, {$fileBasicInvoiceInfo}, 'Invoice subtotal', 30, '{{format_number_2 invoice_data_subtotal_incl_tax}}', NULL, NULL),
(NULL, {$fileBasicInvoiceInfo}, 'Invoice shipping', 40, '{{format_number_2 invoice_data_shipping_amount}}', NULL, NULL),
(NULL, {$fileBasicInvoiceInfo}, 'Invoice avg. item price', 70, '##{{format_convert_price invoice_data_subtotal_incl_tax}}/{{format_integer invoice_data_total_qty}}##', NULL, NULL),
(NULL, {$fileBasicShipmentInfo}, 'Shipment id', 20, '{{shipping_data_increment_id}}', NULL, NULL),
(NULL, {$fileBasicShipmentInfo}, 'Carrier code', 30, '{{shipping_track_last_data_carrier_code}}', NULL, NULL),
(NULL, {$fileBasicShipmentInfo}, 'Track number', 40, '{{shipping_track_last_data_track_number}}', NULL, NULL),
(NULL, {$fileBasicShipmentInfo}, 'Order id', 10, '{{order_data_increment_id}}', NULL, NULL),
(NULL, {$fileBasicShipmentInfo}, 'Order created at', 70, '{{format_date_short order_data_created_at}}', NULL, NULL),
(NULL, {$fileBasicCreditmemoInfo}, 'Order id', 10, '{{order_data_increment_id}}', NULL, NULL),
(NULL, {$fileBasicCreditmemoInfo}, 'Order created at', 70, '{{format_date_short order_data_created_at}}', NULL, NULL),
(NULL, {$fileBasicCreditmemoInfo}, 'Adjustment total', 60, '##format_price_txt {{creditmemo_data_adjustment_positive}}-{{creditmemo_data_adjustment_negative}}##', NULL, NULL),
(NULL, {$fileBasicCreditmemoInfo}, 'Creditmemo id', 20, '{{creditmemo_data_increment_id}}', NULL, NULL),
(NULL, {$fileBasicCreditmemoInfo}, 'Creditmemo subtotal', 30, '{{format_price_txt creditmemo_data_subtotal_incl_tax}}', NULL, NULL),
(NULL, {$fileBasicCreditmemoInfo}, 'Adjustment positive', 40, '{{format_price_txt creditmemo_data_adjustment_positive}}', NULL, NULL),
(NULL, {$fileBasicCreditmemoInfo}, 'Adjustment negativ', 50, '{{format_price_txt creditmemo_data_adjustment_negative}}', NULL, NULL);
EOT;

$installer->run($updateString2);

$installer->endSetup();