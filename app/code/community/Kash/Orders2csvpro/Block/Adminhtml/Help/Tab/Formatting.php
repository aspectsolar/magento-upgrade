<?php
/**
 * Kash Orders2csvpro Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to Henrik Kier <info@kash.com> so we can send you a copy immediately.
 *
 * @category   Kash
 * @package    Kash_Orders2csvpro
 * @copyright  Copyright (c) 2012 Kash (http://kash.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Henrik Kier <info@kash.com>
 * */

class Kash_Orders2csvpro_Block_Adminhtml_Help_Tab_Formatting extends Mage_Adminhtml_Block_Widget_Grid
{
	const XPATH_CONFIG_SETTINGS_ORDER_ID		= 'orders2csvpro/settings/order_id';
	const XPATH_CONFIG_SETTINGS_INVOICE_ID				= 'orders2csvpro/settings/invoice_id';

	public function __construct()
	{
		parent::__construct();
		$this->setId('helpFormattingGrid');
		$this->setFilterVisibility(false);
		$this->setPagerVisibility(false);
	}

	protected function _prepareCollection()
	{
		$collection = new Varien_Data_Collection();
		$orderId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_ORDER_ID);
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

		if(!$order->getData() && count($order->getData())==0){
			$invoiceId = Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_INVOICE_ID);
			$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
			$order = $invoice->getOrder();
			if(!$order->getData() && count($order->getData())==0){
				$row = new Varien_Object(array('key'=>"format_?", 'value'=>'Selected order not present',
	            						'object'=>'Format'));
				$collection->addItem($row);
				$this->setCollection($collection);
				return parent::_prepareCollection();
			}
		}
		 
		$replacements = array (
			'format_price_txt' => array('1245.1248','$order->formatPriceTxt($text)'),
			'format_currency' => array('1245.1248'," Mage::helper('core')->formatCurrency(\$text)"),
			'format_price' => array('1245.1248','$order->formatPrice($text)'),
			'format_base_price' => array('1245.1248','$order->formatBasePrice($text)'),
			'format_convert_price' => array('1245.1248','$order->getStore()->convertPrice($text)'),
	  		'format_date_short' => array('2011-11-20 12:15:10',"Mage::helper('core')->formatDate(\$text, 'short')"),
			'format_date_medium' => array('2011-11-20 12:15:10',"Mage::helper('core')->formatDate(\$text, 'medium')"),
			'format_date_long' => array('2011-11-20 12:15:10',"Mage::helper('core')->formatDate(\$text, 'long')"),
			'format_date_full' => array('2011-11-20 12:15:10',"Mage::helper('core')->formatDate(\$text, 'full')"),
			'format_time_short' => array('2011-11-20 12:15:10',"Mage::helper('core')->formatTime(\$text, 'short')"),
			'format_time_medium' => array('2011-11-20 12:15:10',"Mage::helper('core')->formatTime(\$text, 'medium')"),
			'format_time_long' => array('2011-11-20 12:15:10',"Mage::helper('core')->formatTime(\$text, 'long')"),
			'format_time_full' => array('2011-11-20 12:15:10',"Mage::helper('core')->formatTime(\$text, 'full')"),
			'format_integer' => array('1245.1248','(int)$text'),
			'format_number_1' => array('1245.1248','number_format($text, 1)'),
			'format_number_2' => array('1245.1248','number_format($text, 2)'),
			'format_number_3' => array('1245.1248','number_format($text, 3)'),
			'format_number_4' => array('1245.1248','number_format($text, 4)')
		);

		foreach ($replacements as $key => $value) {
			$text = $value[0];
			ob_start();
			$evaValue = @eval("return ($value[1]);");
			ob_end_clean();
			 
			if(!is_string($value)) $value = print_r($value, true);
			$row = new Varien_Object(array('key'=>$key, 'value'=>htmlentities($evaValue),
      						'object'=>$text));
			$collection->addItem($row);
		}

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('object', array(
	      'header'    => Mage::helper('orders2csvpro')->__('Input value'),
	      'align'     =>'left',
	      'index'     => 'object',
	      'sortable'  => false,
    	  'type'      => 'text',
          'width'     => '20%'
		));
		$this->addColumn('variable', array(
          'header'    => Mage::helper('orders2csvpro')->__('Key'),
          'align'     =>'left',
          'index'     => 'key',
	      'sortable'  => false,
    	  'type'      => 'text',
          'width'     => '40%'
		));
		$this->addColumn('value', array(
          'header'    => Mage::helper('orders2csvpro')->__('Exampel value'),
          'align'     =>'left',
          'index'     => 'value',
	      'sortable'  => false,
    	  'type'      => 'text',
          'width'     => '40%'
		));

		$this->addExportType('*/*/exportFormattingCsv', Mage::helper('orders2csvpro')->__('CSV'));
		$this->addExportType('*/*/exportFormattingXml', Mage::helper('orders2csvpro')->__('XML'));

		return parent::_prepareColumns();
	}
	
	/**
	* Get row edit url
	*
	* @return string
	*/
	public function getRowUrl($row)
	{
		return false;
		//return $this->getUrl('*/*/edit', array('type'=>$row->getId()));
	}
}