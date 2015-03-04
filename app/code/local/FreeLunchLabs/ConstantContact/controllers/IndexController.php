<?php
class FreeLunchLabs_ConstantContact_IndexController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction() {
		#collect all subscribers users
		Mage::getSingleton('constantcontact/constantcontact')->batchSubscribe();
		$this->_redirect('adminhtml/newsletter_subscriber/');
	}
}

