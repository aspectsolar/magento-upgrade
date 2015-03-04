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
class Kash_Orders2csvpro_Block_Adminhtml_Schedule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('schedule_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('orders2csvpro')->__('Orders2CSV PRO Schedule'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('general_section', array(
          'label'     => Mage::helper('orders2csvpro')->__('General'),
          'title'     => Mage::helper('orders2csvpro')->__('Schedule information'),
          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_schedule_edit_tab_generel')->toHtml(),
		));

		return parent::_beforeToHtml();
	}
}