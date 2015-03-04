<?php

$installer = $this;
$installer->startSetup();


/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
$installer->getTable('orders2csvpro/column'),
    	'FK_ORDERS2CVS_COLUMN_FILE'
);

/**
 * Change columns
 */
$tables = array(
$installer->getTable('orders2csvpro/file') => array(
		'columns' => array(
			'file_id' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 
				'identity'  => true,
				'nullable'  => false,
				'primary'   => true,
				'comment' => 'ID'
				),
			'title' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_TEXT, 
				'nullable'  => false,
				'lenght' => 255,
				'comment' => 'Title'
				),
			'is_active' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT, 
				'nullable'  => false,
				'default'   => '1',
				'comment' => 'Is Active'
				),
			'num_formatting' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT, 
				'nullable'  => false,
				'default'   => '1',
				'comment' => 'Number formatting'
				),
			'creation_time' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP, 
				'comment' => 'Creation Time'
				),
			'update_time' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP, 
				'comment' => 'Modification Time'
				)
			),
		'comment' => 'File structur for orders2csvpro'
		),
		$installer->getTable('orders2csvpro/column') => array(
		'columns' => array(
			'column_id' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 
				'identity'  => true,
				'nullable'  => false,
				'primary'   => true,
				'comment' => 'ID'
				),
			'file_id' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 
				'nullable'  => false,
				'comment' => 'Table ID'
				),
			'title' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_TEXT, 
				'lenght' => 255, 
				'nullable'  => false,
				'comment' => 'Title'
				),
			'sort_order' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT, 
				'nullable'  => false,
				'default'   => '1',
				'comment' => 'Comment'
				),
			'num_formatting' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT, 
				'nullable'  => false,
				'default'   => '1',
				'comment' => 'Comment'
				),
			'value' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_TEXT, 
				'lenght' => '64k', 
				'nullable'  => false,
				'comment' => 'Comment'
				),
			'creation_time' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP, 
				'comment' => 'Creation Time'
				),
			'update_time' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP, 
				'comment' => 'Modification Time'
				)
				),
		'comment' => 'Single columns for the csv file in orders2csv'
		),
		$installer->getTable('orders2csvpro/schedule') => array(
			'columns' => array(
				'schedule_id' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 
					'identity'  => true,
					'nullable'  => false,
					'primary'   => true,
					'comment' => 'ID'
				),
				'file_id' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 
					'nullable'  => false,
					'comment' => 'File ID'
				),
				'title' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_TEXT, 
					'nullable'  => false,
					'lenght' => 255,
					'comment' => 'Title'
				),
				'is_active' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT, 
					'nullable'  => false,
					'default'   => '1',
					'comment' => 'Is Active'
				),
				'periode' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT, 
					'nullable'  => false,
					'default'   => '1',
					'comment' => 'Periode'
				),
				'status' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_TEXT, 
					'nullable'  => true,
					'lenght' => 255,
					'comment' => 'statuses'
				),
				'show_header' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT, 
					'nullable'  => false,
					'default'   => '1',
					'comment' => 'show_header'
				),
				'email' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_TEXT, 
					'nullable'  => false,
					'lenght' => 255,
					'comment' => 'email'
				),
				'attached' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT, 
					'nullable'  => false,
					'default'   => '1',
					'comment' => 'attached'
				),
				'creation_time' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP, 
					'comment' => 'Creation Time'
				),
				'update_time' => array(
					'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP, 
					'comment' => 'Modification Time'
				)
			),
			'comment' => 'Schedule for sending out csv file in orders2csv PRO'
		),
		$installer->getTable('orders2csvpro/runs') => array(
		'columns' => array(
			'runs_id' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 
				'identity'  => true,
				'nullable'  => false,
				'primary'   => true,
				'comment' => 'ID'
				),
			'schedule_id' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 
				'nullable'  => false,
				'comment' => 'schedule ID'
				),
			'order_id' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT, 
				'nullable'  => false,
				'comment' => 'order_id'
				),
			'run_time' => array(
				'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP, 
				'comment' => 'Run Time'
				)
				),
		'comment' => 'Sending out csv file in orders2csvpro PRO log'
		),
      );

        $installer->getConnection()->modifyTables($tables);


        /**
         * Add indexes
         */
        $installer->getConnection()->addIndex(
	        $installer->getTable('orders2csvpro/column'),
	        $installer->getIdxName('orders2csvpro/column', array('file_id')),
	        array('file_id')
        );
        $installer->getConnection()->addIndex(
	        $installer->getTable('orders2csvpro/schedule'),
	        $installer->getIdxName('orders2csvpro/schedule', array('file_id')),
	        array('file_id')
        );
        $installer->getConnection()->addIndex(
	        $installer->getTable('orders2csvpro/runs'),
	        $installer->getIdxName('orders2csvpro/runs', array('schedule_id')),
	        array('schedule_id')
        );
        $installer->getConnection()->addIndex(
	        $installer->getTable('orders2csvpro/runs'),
	        $installer->getIdxName('orders2csvpro/runs', array('order_id')),
	        array('order_id')
        );
        
        /**
         * Add foreign keys
         */
        $installer->getConnection()->addForeignKey(
	        $installer->getFkName('orders2csvpro/column', 'file_id', 'orders2csvpro/file', 'file_id'),
	        $installer->getTable('orders2csvpro/column'),
			    'file_id',
	        $installer->getTable('orders2csvpro/file'),
			    'file_id'
        );
        $installer->getConnection()->addForeignKey(
	        $installer->getFkName('orders2csvpro/schedule', 'file_id', 'orders2csvpro/file', 'file_id'),
	        $installer->getTable('orders2csvpro/schedule'),
	        			    'file_id',
	        $installer->getTable('orders2csvpro/file'),
	        			    'file_id'
        );
        $installer->getConnection()->addForeignKey(
	        $installer->getFkName('orders2csvpro/runs', 'schedule_id', 'orders2csvpro/schedule', 'schedule_id'),
	        $installer->getTable('orders2csvpro/runs'),
	        			    'schedule_id',
	        $installer->getTable('orders2csvpro/schedule'),
	        			    'schedule_id'
        );
        $installer->getConnection()->addForeignKey(
	        $installer->getFkName('orders2csvpro/runs', 'order_id', 'sales/order', 'entity_id'),
	        $installer->getTable('orders2csvpro/runs'),
	        			    'order_id',
	        $installer->getTable('sales/order'),
	        			    'entity_id'
        );
        
        $installer->endSetup();