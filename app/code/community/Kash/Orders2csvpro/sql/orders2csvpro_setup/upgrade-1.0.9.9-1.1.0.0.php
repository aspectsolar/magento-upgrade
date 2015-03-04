<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('orders2csvpro/file'), 'delimiter', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => 10,
		'nullable' => false,
		'default' => ',',
		'comment' => 'Delimiter for file'
));

$installer->getConnection()->addColumn($installer->getTable('orders2csvpro/file'), 'enclosure', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => 10,
		'nullable' => false,
		'default' => '"',
		'comment' => 'Enclosure for variables in file'
));

$installer->getConnection()->addColumn($installer->getTable('orders2csvpro/file'), 'type', array(
		'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
		'nullable' => false,
		'default' => '1',
		'comment' => 'File type (order, invoice etc)'
));

$installer->getConnection()->addColumn($installer->getTable('orders2csvpro/runs'), 'invoice_id', array(
		'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'default' => null,
		'comment' => 'Invoices run'
));

$installer->getConnection()->addColumn($installer->getTable('orders2csvpro/runs'), 'shipment_id', array(
		'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'default' => null,
		'comment' => 'Shipment run'
));

$installer->getConnection()->addColumn($installer->getTable('orders2csvpro/runs'), 'creditmemo_id', array(
		'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'default' => null,
		'comment' => 'Creditmemo run'
));

$installer->getConnection()->addForeignKey($installer->getFkName('orders2csvpro/runs', 'invoice_id', 'sales/invoice', 'entity_id'),
		$installer->getTable('orders2csvpro/runs'), 'invoice_id', $installer->getTable('sales/invoice'), 'entity_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->addForeignKey($installer->getFkName('orders2csvpro/runs', 'shipment_id', 'sales/shipment', 'entity_id'),
		$installer->getTable('orders2csvpro/runs'), 'shipment_id', $installer->getTable('sales/shipment'), 'entity_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->addForeignKey($installer->getFkName('orders2csvpro/runs', 'creditmemo_id', 'sales/creditmemo', 'entity_id'),
		$installer->getTable('orders2csvpro/runs'), 'creditmemo_id', $installer->getTable('sales/creditmemo'), 'entity_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->endSetup();