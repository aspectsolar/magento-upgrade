<?php
$installer = $this;

$select = $installer->getConnection()->select()
->from(array('f' => $installer->getTable('orders2csvpro/file')))
->where("f.title=?", 'BasicInvoiceInfo');
$resultBasicInvoiceInfo = $installer->getConnection()->fetchOne($select);

if(!isset($resultBasicInvoiceInfo['file_id']) && $resultBasicInvoiceInfo['file_id'] == 0){

$orders2cvsFile = array(
	array(
		'file_id' 			=> null,
		'title' 			=> 'BasicInvoiceInfo',
		'is_active' 		=> 1,
		'num_formatting'	=> 1,
		'delimiter' 		=> ',',
		'enclosure' 		=> '"',
		'type'				=> 2,
	),
	array(
		'file_id' 			=> null,
		'title' 			=> 'BasicShipmentInfo',
		'is_active' 		=> 1,
		'num_formatting'	=> 1,
		'delimiter' 		=> ',',
		'enclosure' 		=> '"',
		'type'				=> 3,
    ),
	array(
		'file_id' 			=> null,
		'title' 			=> 'BasicCreditmemoInfo',
		'is_active' 		=> 1,
		'num_formatting'	=> 1,
		'delimiter' 		=> ',',
		'enclosure' 		=> '"',
		'type'				=> 4,
	),
);

/**
 * Insert default files
 */
foreach ($orders2cvsFile as $data) {
	$installer->getConnection()->insertForce($installer->getTable('orders2csvpro/file'), $data);
}

$select = $installer->getConnection()->select()
	->from(array('f' => $installer->getTable('orders2csvpro/file')))
	->where("f.title=?", 'BasicInvoiceInfo');
$resultBasicInvoiceInfo = $installer->getConnection()->fetchOne($select);

$select = $installer->getConnection()->select()
->from(array('f' => $installer->getTable('orders2csvpro/file')))
->where("f.title=?", 'BasicShipmentInfo');
$resultBasicShipmentInfo = $installer->getConnection()->fetchOne($select);

$select = $installer->getConnection()->select()
->from(array('f' => $installer->getTable('orders2csvpro/file')))
->where("f.title=?", 'BasicCreditmemoInfo');
$resultBasicCreditmemoInfo = $installer->getConnection()->fetchOne($select);

$orders2cvsColumns = array(
		array(
			'column_id' => null,
			'file_id' => $resultBasicInvoiceInfo['file_id'],
			'title' => 'Order id',
			'sort_order' => 10,
			'value' => '{{order_data_increment_id}}',
		),
		
		array(
			'column_id' => null,
			'file_id' => $resultBasicInvoiceInfo['file_id'],
			'title' => 'Order created at',
			'sort_order' => 80,
			'value' => '{{format_date_short order_data_created_at}}',
		),
		
		array(
			'column_id' => null,
			'file_id' => $resultBasicInvoiceInfo['file_id'],
			'title' => 'Invoice total qty',
			'sort_order' => 50,
			'value' => '{{invoice_data_total_qty}}',
		),
		
		array(
			'column_id' => null,
			'file_id' => $resultBasicInvoiceInfo['file_id'],
			'title' => 'Order currency code',
			'sort_order' => 60,
			'value' => '{{invoice_data_order_currency_code}}',
		),
		
		array(
			'column_id' => null,
			'file_id' => $resultBasicInvoiceInfo['file_id'],
			'title' => 'Invoice id',
			'sort_order' => 20,
			'value' => '{{invoice_data_increment_id}}',
		),
		
		array(
			'column_id' => null,
			'file_id' => $resultBasicInvoiceInfo['file_id'],
			'title' => 'Invoice subtotal',
			'sort_order' => 30,
			'value' => '{{format_number_2 invoice_data_subtotal_incl_tax}}',
		),
		
		array(
			'column_id' => null,
			'file_id' => $resultBasicInvoiceInfo['file_id'],
			'title' => 'Invoice shipping',
			'sort_order' => 40,
			'value' => '{{format_number_2 invoice_data_shipping_amount}}',
		),
		
		array(
			'column_id' => null,
			'file_id' => $resultBasicInvoiceInfo['file_id'],
			'title' => 'Invoice avg. item price',
			'sort_order' => 70,
			'value' => '##{{format_convert_price invoice_data_subtotal_incl_tax}}/{{format_integer invoice_data_total_qty}}##',
		),	
				
		array(
				'column_id' => null,
				'file_id' => $resultBasicShipmentInfo['file_id'],
				'title' => 'Shipment id',
				'sort_order' => 20,
				'value' => '{{shipping_data_increment_id}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicShipmentInfo['file_id'],
				'title' => 'Carrier code',
				'sort_order' => 30,
				'value' => '{{shipping_track_last_data_carrier_code}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicShipmentInfo['file_id'],
				'title' => 'Track number',
				'sort_order' => 40,
				'value' => '{{shipping_track_last_data_track_number}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicShipmentInfo['file_id'],
				'title' => 'Order id',
				'sort_order' => 10,
				'value' => '{{order_data_increment_id}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicShipmentInfo['file_id'],
				'title' => 'Order created at',
				'sort_order' => 70,
				'value' => '{{format_date_short order_data_created_at}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicCreditmemoInfo['file_id'],
				'title' => 'Order id',
				'sort_order' => 10,
				'value' => '{{order_data_increment_id}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicCreditmemoInfo['file_id'],
				'title' => 'Order created at',
				'sort_order' => 70,
				'value' => '{{format_date_short order_data_created_at}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicCreditmemoInfo['file_id'],
				'title' => 'Adjustment total',
				'sort_order' => 60,
				'value' => '##format_price_txt {{creditmemo_data_adjustment_positive}}-{{creditmemo_data_adjustment_negative}}##',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicCreditmemoInfo['file_id'],
				'title' => 'Creditmemo id',
				'sort_order' => 20,
				'value' => '{{creditmemo_data_increment_id}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicCreditmemoInfo['file_id'],
				'title' => 'Creditmemo subtotal',
				'sort_order' => 30,
				'value' => '{{format_price_txt creditmemo_data_subtotal_incl_tax}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicCreditmemoInfo['file_id'],
				'title' => 'Adjustment positive',
				'sort_order' => 40,
				'value' => '{{format_price_txt creditmemo_data_adjustment_positive}}',
		),
		
		array(
				'column_id' => null,
				'file_id' => $resultBasicCreditmemoInfo['file_id'],
				'title' => 'Adjustment negativ',
				'sort_order' => 50,
				'value' => '{{format_price_txt creditmemo_data_adjustment_negative}}',
		),		
);

/**
 * Insert default columns
 */
foreach ($orders2cvsColumns as $data) {
	$installer->getConnection()->insertForce($installer->getTable('orders2csvpro/column'), $data);
}
}