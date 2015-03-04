<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Block_Adminhtml_Video_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
    	parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'auguria_video';
		$this->_controller = 'adminhtml_video';

		$this->_updateButton('save', 'label', Mage::helper('auguria_video')->__('Save Item'));
		$this->_updateButton('delete', 'label', Mage::helper('auguria_video')->__('Delete Item'));

    	$this->_addButton('saveandcontinue', array(
    	            'label'     => Mage::helper('auguria_video')->__('Save And Continue Edit'),
    	            'onclick'   => 'saveAndContinueEdit()',
    	            'class'     => 'save',
    	), -100);

    	$this->_formScripts[] = "
    	function toggleEditor() {
    	if (tinyMCE.getInstanceById('description') == null) {
    	tinyMCE.execCommand('mceAddControl', false, 'description');
    	} else {
    	tinyMCE.execCommand('mceRemoveControl', false, 'description');
    	}
    	}

    	function saveAndContinueEdit(){
    	editForm.submit($('edit_form').action+'back/edit/');
    	}
    	";
    }

    public function getHeaderText()
    {
        if( Mage::registry('video_data') && Mage::registry('video_data')->getId() ) {
            return Mage::helper('auguria_video')->__("Edition of the video '%s'", Mage::registry('video_data')->getTitle());
        }
        else {
            return Mage::helper('auguria_video')->__('Add a video');
        }
    }

    protected function _prepareLayout() {
    	parent::_prepareLayout();
    	if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
    		$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
    	}
    }
}