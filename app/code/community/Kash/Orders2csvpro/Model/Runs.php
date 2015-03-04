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
class Kash_Orders2csvpro_Model_Runs extends Mage_Core_Model_Abstract
{

	protected $_column = array();
	protected $_columnInstance;

	public function _construct()
	{
		parent::_construct();
		$this->_init('orders2csvpro/runs');
		
	}
	
	// Get all orders that has not been in a run and that has the right statuses
	// return array of order objects
	public function getAllOrdersNotRun($schedule){
		return Mage::getResourceModel('orders2csvpro/runs')->getAllOrdersNotRun($schedule);		
	}

	// Get all invoices that has not been in a run and that has the right statuses
	// return array of invoice objects
	public function getAllInvoicesNotRun($schedule){
		return Mage::getResourceModel('orders2csvpro/runs')->getAllInvoicesNotRun($schedule);
	}
	
	// Get all shipments that has not been in a run and that has the right statuses
	// return array of shipment objects
	public function getAllShipmentsNotRun($schedule){
		return Mage::getResourceModel('orders2csvpro/runs')->getAllShipmentsNotRun($schedule);
	}
	
	// Get all creditmemo that has not been in a run and that has the right statuses
	// return array of creditmemo objects
	public function getAllCreditmemosNotRun($schedule){
		return Mage::getResourceModel('orders2csvpro/runs')->getAllCreditmemosNotRun($schedule);
	}	
}