<?php
/**
* Kash Orders2csvpro Module
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
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
class Kash_Orders2csvpro_Block_Adminhtml_File_Edit_Tab_Columns extends Mage_Adminhtml_Block_Widget
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('orders2csvpro/columns.phtml');
	}


	protected function _prepareLayout()
	{
		$this->setChild('add_button',
		$this->getLayout()->createBlock('adminhtml/widget_button')
		->setData(array(
                    'label' => Mage::helper('orders2csvpro')->__('Add new column'),
                    'class' => 'add',
                    'id'    => 'add_new_column'
		))
		);

		$this->setChild('column_box',
		$this->getLayout()->createBlock('orders2csvpro/adminhtml_file_edit_tab_columns_column')
		);

		return parent::_prepareLayout();
	}

	public function getAddButtonHtml()
	{
		return $this->getChildHtml('add_button');
	}

	public function getColumnBoxHtml()
	{
		return $this->getChildHtml('column_box');
	}
}
