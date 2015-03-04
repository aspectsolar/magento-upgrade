<?php

class Kash_Orders2csvpro_Block_Adminhtml_Schedule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$helpLinkUrl = "http://kash.com/gethelp/orders2csvpro/schedule/edit.html";

		$this->_objectId = 'id';
		$this->_blockGroup = 'orders2csvpro';
		$this->_controller = 'adminhtml_schedule';

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
			return Mage::helper('orders2csvpro')->__("Edit Schedule '%s'", $this->htmlEscape(Mage::registry('orders2csvpro_data')->getTitle()));
		} else {
			return Mage::helper('orders2csvpro')->__('Add Schedule ');
		}
	}

}