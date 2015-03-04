<?php
$installer = $this;

$collection_of_files = Mage::getModel('orders2csvpro/file')->getCollection();

if(!$collection_of_files->getSize()>0){

$orders2cvsFile = array(
	array(
		'file_id' 			=> 1,
		'title' 			=> 'DanishVatEUSale',
		'is_active' 		=> 1,
		'num_formatting'	=> 1,
	),
	array(
		'file_id' 			=> 2,
		'title' 			=> 'BasicOrderInfo',
		'is_active' 		=> 1,
		'num_formatting'	=> 1,
			
    ),
	array(
		'file_id' 			=> 3,
		'title' 			=> 'BasicItemInfo',
		'is_active' 		=> 1,
		'num_formatting'	=> 1,
	),
);

$orders2cvsColumns = array(
	array(
		'column_id' => 1,
		'file_id' => 1,
		'title' => 'Order amount services',
		'sort_order' => 70,
		'value' => '{{format_number_2 order_data_grand_total}}',
	),
	array(
		'column_id' => 2,
		'file_id' => 1,
		'title' => 'Order amount triangular trade',
		'sort_order' => 60,
		'value' => '{{format_number_2 order_data_grand_total}}',
	),
	array(
		'column_id' => 3,
		'file_id' => 1,
		'title' => 'Order amount goods',
		'sort_order' => 50,
		'value' => '{{format_number_2 order_data_grand_total}}',
	),
	array(
		'column_id' => 4,
		'file_id' => 1,
		'title' => 'Buyers Vat',
		'sort_order' => 40,
		'value' => '{{format_number_2 order_data_customer_taxvat}}',
	),
	array(
		'column_id' => 5,
		'file_id' => 1,
		'title' => 'Contry code',
		'sort_order' => 30,
		'value' => '{{order_billing_data_country_id}}',
	),
	array(
		'column_id' => 6,
		'file_id' => 1,
		'title' => 'Order date',
		'sort_order' => 20,
		'value' => '{{order_data_created_at}}',
	),
	array(
		'column_id' => 7,
		'file_id' => 1,
		'title' => 'Order id',
		'sort_order' => 10,
		'value' => '{{order_data_increment_id}}',
	),
	array(
		'column_id' => 8,
		'file_id' => 2,
		'title' => 'Order weight',
		'sort_order' => 70,
		'value' => '{{order_data_weight}}',
	),
	array(
		'column_id' => 9,
		'file_id' => 2,
		'title' => 'Billing email',
		'sort_order' => 130,
		'value' => '{{order_billing_data_email}}',
	),
	array(
		'column_id' => 10,
		'file_id' => 2,
		'title' => 'Billing telephone',
		'sort_order' => 120,
		'value' => '{{order_billing_data_telephone}}',
	),
	array(
		'column_id' => 11,
		'file_id' => 2,
		'title' => 'Billing zip',
		'sort_order' => 110,
		'value' => '{{order_billing_data_postcode}}',
	),
	array(
		'column_id' => 12,
		'file_id' => 2,
		'title' => 'Billing street',
		'sort_order' => 100,
		'value' => '{{order_billing_data_street}}',
	),
	array(
		'column_id' => 13,
		'file_id' => 2,
		'title' => 'Customer lastname',
		'sort_order' => 90,
		'value' => '{{order_data_customer_lastname}}',
	),
	array(
		'column_id' => 14,
		'file_id' => 2,
		'title' => 'Customer firstname',
		'sort_order' => 80,
		'value' => '{{order_data_customer_firstname}}',
	),
	array(
		'column_id' => 15,
		'file_id' => 2,
		'title' => 'Total amount',
		'sort_order' => 60,
		'value' => '{{order_data_grand_total}}',
	),
	array(
		'column_id' => 16,
		'file_id' => 2,
		'title' => 'Discount amount',
		'sort_order' => 50,
		'value' => '{{order_data_discount_amount}}',
	),
	array(
		'column_id' => 17,
		'file_id' => 2,
		'title' => 'Shipping amout',
		'sort_order' => 40,
		'value' => '{{order_data_shipping_amount}}',
	),
	array(
		'column_id' => 18,
		'file_id' => 2,
		'title' => 'Order amount',
		'sort_order' => 30,
		'value' => '{{order_data_subtotal}}',
	),
	array(
		'column_id' => 19,
		'file_id' => 2,
		'title' => 'Qty',
		'sort_order' => 20,
		'value' => '{{order_data_total_qty_ordered}}',
	),
	array(
		'column_id' => 20,
		'file_id' => 2,
		'title' => 'Order id',
		'sort_order' => 10,
		'value' => '{{order_data_increment_id}}',
	),
	array(
		'column_id' => 21,
		'file_id' => 3,
		'title' => 'Item option value',
		'sort_order' => 100,
		'value' => '{{item_option_data_value}}',
	),
	array(
		'column_id' => 22,
		'file_id' => 3,
		'title' => 'Item option label',
		'sort_order' => 90,
		'value' => '{{item_option_data_label}}',
	),
	array(
		'column_id' => 23,
		'file_id' => 3,
		'title' => 'Item discount amount',
		'sort_order' => 80,
		'value' => '{{item_data_discount_amount}}',
	),
	array(
		'column_id' => 24,
		'file_id' => 3,
		'title' => 'Item amount',
		'sort_order' => 60,
		'value' => '{{item_data_row_total}}',
	),
	array(
		'column_id' => 25,
		'file_id' => 3,
		'title' => 'Item tax amount',
		'sort_order' => 70,
		'value' => '{{item_data_tax_amount}}',
	),
	array(
		'column_id' => 26,
		'file_id' => 3,
		'title' => 'Order Id',
		'sort_order' => 10,
		'value' => '{{order_data_increment_id}}',
	),
	array(
		'column_id' => 27,
		'file_id' => 3,
		'title' => 'Item id',
		'sort_order' => 20,
		'value' => '{{item_data_product_id}}',
	),
	array(
		'column_id' => 28,
		'file_id' => 3,
		'title' => 'Item sku',
		'sort_order' => 30,
		'value' => '{{item_data_sku}}',
	),
	array(
		'column_id' => 29,
		'file_id' => 3,
		'title' => 'Item name',
		'sort_order' => 40,
		'value' => '{{item_data_name}}',
	),
	array(
		'column_id' => 30,
		'file_id' => 3,
		'title' => 'Item qty',
		'sort_order' => 50,
		'value' => '{{item_data_qty_ordered}}',
	),
	array(
		'column_id' => 31,
		'file_id' => 3,
		'title' => 'Item price',
		'sort_order' => 60,
		'value' => '{{item_data_price}}',
	),
	array(
		'column_id' => 32,
		'file_id' => 2,
		'title' => 'Tax amount',
		'sort_order' => 35,
		'value' => '{{order_data_tax_amount}}',
	),
	array(
		'column_id' => 33,
		'file_id' => 3,
		'title' => 'Product created at',
		'sort_order' => 44,
		'value' => '{{product_data_created_at}}',
	),
	array(
		'column_id' => 34,
		'file_id' => 3,
		'title' => 'Product url path',
		'sort_order' => 47,
		'value' => '{{product_data_url_path}}',
	)
);

/**
 * Insert default files
 */
foreach ($orders2cvsFile as $data) {
	$installer->getConnection()->insertForce($installer->getTable('orders2csvpro/file'), $data);
}

/**
 * Insert default columns
 */
foreach ($orders2cvsColumns as $data) {
	$installer->getConnection()->insertForce($installer->getTable('orders2csvpro/column'), $data);
}
}