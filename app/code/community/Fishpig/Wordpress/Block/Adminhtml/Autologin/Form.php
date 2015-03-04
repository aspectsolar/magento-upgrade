<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Adminhtml_Autologin_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Create the form for the autologin details
	 *
	 * @return $this
	 */
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/autologinpost', array('id' => Mage::getSingleton('wordpress/admin_user')->getId())),
			'method' => 'post'
		));

		$this->setForm($form->setUseContainer(true));

		$fieldset = $form->addFieldset('autologin_form', array(
			'legend'=> 'WordPress Admin'
		));

		$fieldset->addField('username', 'text', array(
			'label'     	=> 'Username',
			'class'     	=> 'required-entry',
			'required'  => true,
			'name'      => 'username',
		));
		
		$fieldset->addField('password', 'password', array(
			'label'     	=> 'Password',
			'class'     	=> 'required-entry',
			'required'  => true,
			'name'      => 'password',
		));

		$form->setValues(
			Mage::getSingleton('wordpress/admin_user')->getData()
		);

		return parent::_prepareForm();	
	}
}
