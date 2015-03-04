<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Block_Adminhtml_Video_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('videoGrid');
		$this->setDefaultSort('auguria_video_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getResourceModel('auguria_video/video_collection');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('auguria_video_id', array(
          'header'    => Mage::helper('auguria_video')->__('ID'),
          'align'     =>'right',
          'width'     => '20px',
          'index'     => 'auguria_video_id',
		));

		$this->addColumn('title', array(
          'header'    => Mage::helper('auguria_video')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
		));

		$this->addColumn('filename', array(
          'header'    => Mage::helper('auguria_video')->__('Filename'),
          'align'     =>'left',
          'index'     => 'filename',
		));

		$this->addColumn('status', array(
          'header'    => Mage::helper('auguria_video')->__('Status'),
          'align'     => 'left',
          'index'     => 'status',
      	  'width'     => '100px',
          'type'      => 'options',
          'options'   => Mage::helper('auguria_video')->statusToOptionArray(),
		));

		$this->addColumn('action',
            array(
                'header'    =>  Mage::helper('auguria_video')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('auguria_video')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'auguria_video_id',
                'is_system' => true,
		));

		//$this->addExportType('*/*/exportCsv', Mage::helper('auguria_video')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('auguria_video')->__('XML'));

		return parent::_prepareColumns();
	}

    protected function _prepareMassaction()
    {
    	$this->setMassactionIdField('auguria_video_id');
    	$this->getMassactionBlock()->setFormFieldName('videos');

    	$this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('auguria_video')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('auguria_video')->__('Are you sure to delete selected videos ?')
    	));

    	$statuses = Mage::getSingleton('adminhtml/system_config_source_enabledisable')->toOptionArray();
    	array_unshift($statuses, array('label'=>'', 'value'=>''));

    	$this->getMassactionBlock()->addItem('status', array(
	             'label'=> Mage::helper('auguria_video')->__('Update status'),
	             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
	             'confirm'  => Mage::helper('auguria_video')->__('Are you sure to change status of selected videos ?'),
	             'additional' => array(
	                    'visibility' => array(
	                         'name' => 'status',
	                         'type' => 'select',
	                         'class' => 'required-entry',
	                         'label' => Mage::helper('auguria_video')->__('Status'),
	                         'values' => $statuses
    				)
    			)
    		)
    	);
    	return $this;
    }

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

}