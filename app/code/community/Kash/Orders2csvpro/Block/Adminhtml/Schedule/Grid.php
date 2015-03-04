<?php
/**
* Kash Orders2csvpro Module
*
* NOTICE OF LICENSE
*
* This source schedule is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the schedule LICENSE.txt.
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
class Kash_Orders2csvpro_Block_Adminhtml_Schedule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('scheduleGrid');
		$this->setDefaultSort('schedule_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('orders2csvpro/schedule')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('schedule_id', array(
          'header'    => Mage::helper('orders2csvpro')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'schedule_id',
		));

		$this->addColumn('title', array(
          'header'    => Mage::helper('orders2csvpro')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
		));

		$this->addColumn('file_id', array(
		          'header'    => Mage::helper('orders2csvpro')->__('File structure'),
		          'align'     =>'left',
		          'index'     => 'file_id',
		          'type'      => 'options',
          		  'options'	  => Mage::getSingleton('orders2csvpro/file')->getFilesForGrid(),
		));
		
		$this->addColumn('last_run', array(
			'header'    => Mage::helper('orders2csvpro')->__('Last run at'),
			'align'     => 'left',
			'width'     => '180px',
			'type'      => 'datetime',
			'default'   => '--',
			'index'     => 'last_run',
		));

		$this->addColumn('is_active', array(
          'header'    => Mage::helper('orders2csvpro')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'is_active',
          'type'      => 'options',
          'options'   => array(
		1 => 'Enabled',
		2 => 'Disabled',
		),
		));
		 
		$this->addColumn('action',
		array(
                'header'    =>  Mage::helper('orders2csvpro')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
		array(
                        'caption'   => Mage::helper('orders2csvpro')->__('Test send'),
                        'url'       => array('base'=> '*/*/test'),
                        'field'     => 'id'
		)
		),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
		));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('schedule_id');
		$this->getMassactionBlock()->setFormFieldName('schedule');

		$this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('orders2csvpro')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('orders2csvpro')->__('Are you sure?')
		));

		$statuses = Mage::getSingleton('orders2csvpro/status')->getOptionArray();

		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('orders2csvpro')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('orders2csvpro')->__('Status'),
                         'values' => $statuses
		)
		)
		));
		return $this;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

}