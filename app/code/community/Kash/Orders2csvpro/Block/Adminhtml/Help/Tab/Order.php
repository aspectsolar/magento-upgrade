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

class Kash_Orders2csvpro_Block_Adminhtml_Help_Tab_Order extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('helpOrderGrid');
      $this->setFilterVisibility(false);
      $this->setPagerVisibility(false);
  }

  protected function _prepareCollection()
  {
      $collection = new Varien_Data_Collection();
      $order = Mage::getModel('sales/order')->loadByIncrementId("200000001");
      foreach ($order->getData() as $key => $value) {
      	$order_value = new Varien_Object();
		$order_value->setKey("order_data_".$key);
		$order_value->setValue($value);
      	$order_value->setObject("Order");
      	$collection->addItem($order_value);
      }
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
     $this->addColumn('object', array(
	      'header'    => Mage::helper('orders2csvpro')->__('Object'),
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

	  $this->addExportType('*/*/exportOrderCsv', Mage::helper('orders2csvpro')->__('CSV'));
	  $this->addExportType('*/*/exportOrderXml', Mage::helper('orders2csvpro')->__('XML'));

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