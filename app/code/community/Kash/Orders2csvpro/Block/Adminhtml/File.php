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
class Kash_Orders2csvpro_Block_Adminhtml_File extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_file';
		$this->_blockGroup = 'orders2csvpro';
		$this->_headerText = Mage::helper('orders2csvpro')->__('Orders2CSV PRO file structure');
		$this->_addButtonLabel = Mage::helper('orders2csvpro')->__('Create new file structure');
		parent::__construct();
	}
}