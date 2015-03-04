<?php

class Kash_Orders2csvpro_Block_Adminhtml_File_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$helpLinkUrl = "http://kash.com/gethelp/orders2csvpro/file/edit.html";

		$this->_objectId = 'id';
		$this->_blockGroup = 'orders2csvpro';
		$this->_controller = 'adminhtml_file';

		$this->_addButton('save_as_button', array(
            'label'     => Mage::helper('orders2csvpro')->__('Save As'),
            'onclick'   => 'saveAs();',
            'class'     => 'save'
		));

		$this->_updateButton('save', 'label', Mage::helper('orders2csvpro')->__('Save Element'));
		$this->_updateButton('delete', 'label', Mage::helper('orders2csvpro')->__('Delete Element'));

		$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('orders2csvpro')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
		), -100);

		$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('orders2csvpro_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'orders2csvpro_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'orders2csvpro_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }

            function saveAs(){
				$('saveas').value = '1';
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            $('page-help-link').href = '".$helpLinkUrl."';
			
		";
	}

	public function getHeaderText()
	{
		if( Mage::registry('orders2csvpro_data') && Mage::registry('orders2csvpro_data')->getId() ) {
			return Mage::helper('orders2csvpro')->__("Edit File structure '%s'", $this->htmlEscape(Mage::registry('orders2csvpro_data')->getTitle()));
		} else {
			return Mage::helper('orders2csvpro')->__('Add File structure ');
		}
	}

	/**
	 * Load Wysiwyg on demand and Prepare layout
	 */
	protected function _prepareLayout(){
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
		parent::_prepareLayout();
	}

}