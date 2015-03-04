<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->isTableExists($installer->getTable('orders2csvpro/file'))){

	/**
	 * Create table 'orders2csvpro/file'
	 */
	$table = $installer->getConnection()
	->newTable($installer->getTable('orders2csvpro/file'))
		->addColumn('file_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	        'identity'  => true,
	        'nullable'  => false,
	        'primary'   => true,
		), 'ID')
		->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
	        'nullable'  => false,
		), 'Title')
		->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
	        'nullable'  => false,
	        'default'   => '1',
		), 'Is Active')
		->addColumn('num_formatting', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
	        'nullable'  => false,
	        'default'   => '1',
		), 'Number formatting')
		->addColumn('creation_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
		->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Modification Time')
		->setComment('File structur for orders2csvpropro');
	$installer->getConnection()->createTable($table);
	
	/**
	 * Create table 'orders2csvpro/column'
	 */
	$table = $installer->getConnection()
		->newTable($installer->getTable('orders2csvpro/column'))
		->addColumn('column_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	        'identity'  => true,
	        'nullable'  => false,
	        'primary'   => true,
		), 'ID')
		->addColumn('file_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
			'nullable'  => false,
		), 'Comment')
		->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
	        'nullable'  => false,
		), 'Title')
		->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
	        'nullable'  => false,
	        'default'   => '1',
		), 'Is Active')
		->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
	        'nullable'  => false,
	        'default'   => '1',
			), 'Comment')
		->addColumn('num_formatting', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
	        'nullable'  => false,
	        'default'   => '1',
		), 'Number formatting')
		->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
			'nullable'  => false,
	 		), 'Comment')
		->addColumn('creation_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
		->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Modification Time')
		->addForeignKey($installer->getFkName('orders2csvpro/column', 'file_id', 'orders2csvpro/file', 'file_id'),
	        'file_id', $installer->getTable('orders2csvpro/file'), 'file_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
		->setComment('Single columns for the csv file in orders2csv');
	$installer->getConnection()->createTable($table);
}

/**
* Create table 'orders2csvpro/schedule'
*/
$table = $installer->getConnection()
->newTable($installer->getTable('orders2csvpro/schedule'))
	->addColumn('schedule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
	), 'ID')
	->addColumn('file_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,
	), 'Comment')
	->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
	), 'Title')
	->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
	), 'Is Active')
	->addColumn('periode', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
	), 'Periode')
	->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
	), 'Status to select from')
	->addColumn('show_header', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
	), 'Show header')
	->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
	), 'Email to send to')
	->addColumn('attached', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
	), 'Attached')
	->addColumn('last_run', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Last Time run')
	->addColumn('creation_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
	->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Modification Time')
	->addForeignKey($installer->getFkName('orders2csvpro/schedule', 'file_id', 'orders2csvpro/file', 'file_id'),
        'file_id', $installer->getTable('orders2csvpro/file'), 'file_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Schedule for sending out csv file in orders2csv PRO');
$installer->getConnection()->createTable($table);

/**
 * Create table 'orders2csvpro/runs'
 */
$table = $installer->getConnection()
->newTable($installer->getTable('orders2csvpro/runs'))
	->addColumn('runs_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
	), 'ID')
	->addColumn('schedule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,
	), 'Comment')
	->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,
	), 'Comment')
	->addColumn('run_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Run Time')
	->addForeignKey($installer->getFkName('orders2csvpro/runs', 'schedule_id', 'orders2csvpro/schedule', 'schedule_id'),
        'schedule_id', $installer->getTable('orders2csvpro/schedule'), 'schedule_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->addForeignKey($installer->getFkName('orders2csvpro/runs', 'order_id', 'sales/order', 'entity_id'),
        'order_id', $installer->getTable('sales/order'), 'entity_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Sending out csv file in orders2csvpro PRO log');
$installer->getConnection()->createTable($table);

$installer->endSetup();