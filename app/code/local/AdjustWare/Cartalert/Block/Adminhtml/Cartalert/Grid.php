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
class AdjustWare_Cartalert_Block_Adminhtml_Cartalert_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('cartalertGrid');
      $this->setDefaultSort('cartalert_id');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('adjcartalert/cartalert')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $hlp =  Mage::helper('adjcartalert');
    $groupIds = $hlp->getGroupArray();
    $this->addColumn('cartalert_id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'cartalert_id',
    ));
    
    $this->addColumn('abandoned_at', array(
        'header'    => $hlp->__('Abandoned At'),
        'index'     => 'abandoned_at',
        'type'      => 'datetime', 
        'width'     => '150px',
        'gmtoffset' => true,
        'default'	=> ' ---- ',
    ));

    $this->addColumn('sheduled_at', array(
        'header'    => $hlp->__('Scheduled At'),
        'index'     => 'sheduled_at',
        'type'      => 'datetime', 
        'width'     => '150px',
        'gmtoffset' => true,
        'default'	=> ' ---- ',
    ));
    
    $this->addColumn('follow_up', array(
        'header'    => $hlp->__('Follow Up'),
        'index'     => 'follow_up',
        'type'      => 'options',
        'options'   => array(
    		'first' 	=> $hlp->__('First'),
    		'second' 	=> $hlp->__('Second'),
    		'third' 	=> $hlp->__('Third'),
         ),
        'width'     => '100px',
    ));

    $this->addColumn('status', array(
        'header'    => $hlp->__('Status'),
        'index'     => 'status',
        'type'      => 'options',
        'options'   => array(
    		'pending' 	  => $hlp->__('Pending'),
    		'invalid' 	  => $hlp->__('Not Sent'),
//    		'cancelled'   => $hlp->__('Cancelled'),
         ),
        'width'     => '100px',
    ));

    $this->addColumn('customer_group_id', array(
        'header'    => $hlp->__('Customer Group'),
        'index'     => 'customer_group_id',
        'type'      => 'options',
        'options'   => $groupIds,
    ));

    $this->addColumn('customer_email', array(
        'header'    => $hlp->__('Customer E-mail'),
        'index'     => 'customer_email',
    ));

    $this->addColumn('customer_fname', array(
        'header'    => $hlp->__('Customer First Name'),
        'index'     => 'customer_fname',
    ));

    $this->addColumn('customer_lname', array(
        'header'    => $hlp->__('Customer Last Name'),
        'index'     => 'customer_lname',
    ));
    
    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
  protected function _prepareMassaction(){
    $this->setMassactionIdField('cartalert_id');
    $this->getMassactionBlock()->setFormFieldName('cartalert');
    
    $this->getMassactionBlock()->addItem('send', array(
         'label'    => Mage::helper('adjcartalert')->__('Send and Save to History'),
         'url'      => $this->getUrl('*/*/massSend'),
         'confirm'  => Mage::helper('adjcartalert')->__('Are you sure?')
    ));
    $this->getMassactionBlock()->addItem('delete', array(
         'label'    => Mage::helper('adjcartalert')->__('Delete'),
         'url'      => $this->getUrl('*/*/massDelete'),
         'confirm'  => Mage::helper('adjcartalert')->__('Are you sure?')
    ));
    
    return $this; 
  }

}