<?php
class FreeLunchLabs_ConstantContact_Block_Adminhtml_Newsletter_Subscriber extends Mage_Adminhtml_Block_Newsletter_Subscriber
{
	public function __construct()
	{
		$this->setTemplate('newsletter/subscriber/list_constant.phtml');
	}

	public function getConstantSyn()
	{
		return $this->getUrl('constantcontact/index/index');
	}
}
?>
