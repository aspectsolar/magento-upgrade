<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Adminhtml_Autologin extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId 	= 'id';
		$this->_blockGroup = 'wordpress';
		$this->_controller = 'adminhtml_autologin';
		$this->_buttons = array();

		$this->_addButton('save', array(
			'label' => Mage::helper('adminhtml')->__('Save'),
			'onclick' => 'editForm.submit();',
			'class' => 'save',
		), 1);
	}

	/**
	 * Set the custom form block
	 *
	 * @return $this
	 */
	protected function _prepareLayout()
	{
		// Allows a custom child form block
		$this->_mode = false;

		$this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_form'));
		
		return parent::_prepareLayout();
	}
	
	public function getHeaderText()
	{
		return $this->__('Set Your WordPress Admin Login Details');
	}
}
