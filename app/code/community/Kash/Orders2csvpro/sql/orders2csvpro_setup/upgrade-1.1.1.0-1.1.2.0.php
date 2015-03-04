<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('orders2csvpro/file'), 'status_after', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => 255,
		'nullable' => true,
		'comment' => 'Change to status after manual run'
));

$installer->getConnection()->addColumn($installer->getTable('orders2csvpro/schedule'), 'status_after', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => 255,
		'nullable' => true,
		'comment' => 'Change to status after schedule run'
));

$installer->endSetup();