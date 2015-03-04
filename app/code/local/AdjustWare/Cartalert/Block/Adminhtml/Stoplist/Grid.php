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
/**
 * @author Adjustware
 */ 
class AdjustWare_Cartalert_Block_Adminhtml_Stoplist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('stoplistGrid');
      $this->setDefaultSort('id');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('adjcartalert/stoplist')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $hlp =  Mage::helper('adjcartalert'); 
    $this->addColumn('id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'id',
    ));

    $this->addColumn('store_id', array(
        'header'    => $hlp->__('Specified Store Name'),
        'index'     => 'store_id',
        'type'      => 'options',
        'options'   => Mage::getSingleton('adjcartalert/source_group')->getOptionArray(),
    ));
    
    $this->addColumn('customer_email', array(
        'header'    => $hlp->__('Customer E-mail'),
        'index'     => 'customer_email',
    ));

    $this->addColumn('date', array(
        'header'    => $hlp->__('Unsibscribed On'),
        'index'     => 'date',
        'type'      => 'datetime', 
        'width'     => '150px',
        'gmtoffset' => true,
        'default'    => ' ---- ',
    ));

    return parent::_prepareColumns();
  }

  /*public function getRowUrl($row)
  {
      return $this->getUrl('* /* /edit', array('id' => $row->getId()));
  } */
  
  protected function _prepareMassaction(){
    $this->setMassactionIdField('id');
    $this->getMassactionBlock()->setFormFieldName('cartalert');
    
    $this->getMassactionBlock()->addItem('delete', array(
         'label'    => Mage::helper('adjcartalert')->__('Delete'),
         'url'      => $this->getUrl('*/*/massDelete'),
         'confirm'  => Mage::helper('adjcartalert')->__('Are you sure?')
    ));
    
    return $this; 
  }

}