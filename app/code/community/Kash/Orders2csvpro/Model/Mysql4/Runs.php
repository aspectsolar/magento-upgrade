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
class Kash_Orders2csvpro_Model_Mysql4_Runs extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		// Note that the orders2csvpro_id refers to the key field in your database table.
		$this->_init('orders2csvpro/runs', 'runs_id');
		
	}

	protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
		$object->setRunTime(Mage::getSingleton('core/date')->gmtDate());
		return $this;
	}
	
	// Get all orders that has not been in a run and that has the right statuses
	// return array of order objects
	public function getAllOrdersNotRun($schedule){
		$select = $this->_getReadAdapter()->select();
		
		$select->from(array('o' => $this->getTable('sales/order')), array('o.entity_id'))
			->joinLeft(array('runs' => $this->getMainTable()), 'runs.order_id = o.entity_id AND runs.schedule_id = '.$schedule->getId(), array())
			->where('runs.order_id IS NULL');
		if(strcasecmp($schedule->getStatus(),'order,all') != 0){
			list($type, $status) = explode(",", $schedule->getStatus());
			if(strcasecmp($type, 'order') === 0)
				$select->where('o.status = ?', $status);
		}

		return $this->_getReadAdapter()->fetchAll($select);
	}

	// Get all invoices that has not been in a run and that has the right statuses
	// return array of order objects
	public function getAllInvoicesNotRun($schedule){
		$select = $this->_getReadAdapter()->select();
	
		list($type, $status) = explode(",", $schedule->getStatus());
		if (strcasecmp($type, 'order') === 0){
		//All based on order status
			$select->from(array('i' => $this->getTable('sales/invoice')), array('i.entity_id'))
			->joinLeft(array('o' => $this->getTable('sales/order')), 'i.order_id = o.entity_id', array())
			->joinLeft(array('runs' => $this->getMainTable()), 'runs.invoice_id = i.entity_id AND runs.schedule_id = '.$schedule->getId(), array())
			->where('runs.invoice_id IS NULL');
			if(strcasecmp($schedule->getStatus(),'order,all') != 0){
				$select->where('o.status = ?', $status);
			}
		}else{
		//All based on invoice status
			$select->from(array('i' => $this->getTable('sales/invoice')), array('i.entity_id'))
			->joinLeft(array('runs' => $this->getMainTable()), 'runs.invoice_id = i.entity_id AND runs.schedule_id = '.$schedule->getId(), array())
			->where('runs.invoice_id IS NULL');
			if(strcasecmp($type, 'invoice') === 0 && strcasecmp($schedule->getStatus(),'invoice,all') != 0){
				$select->where('i.state = ?', $status);
			}				
		}
	
		return $this->_getReadAdapter()->fetchAll($select);
	}
	
	// Get all shipments that has not been in a run and that has the right statuses
	// return array of shipment objects
	public function getAllShipmentsNotRun($schedule){
		$select = $this->_getReadAdapter()->select();
	
		list($type, $status) = explode(",", $schedule->getStatus());
		if (strcasecmp($type, 'order') === 0){
		//All based on order status
			$select->from(array('s' => $this->getTable('sales/shipment')), array('s.entity_id'))
			->joinLeft(array('o' => $this->getTable('sales/order')), 's.order_id = o.entity_id', array())
			->joinLeft(array('runs' => $this->getMainTable()), 'runs.shipment_id = s.entity_id AND runs.schedule_id = '.$schedule->getId(), array())
			->where('runs.shipment_id IS NULL');
			if(strcasecmp($schedule->getStatus(),'order,all') != 0){
				$select->where('o.status = ?', $status);
			}
		}else{
		//All based on shipment status
			$select->from(array('s' => $this->getTable('sales/shipment')), array('s.entity_id'))
			->joinLeft(array('runs' => $this->getMainTable()), 'runs.shipment_id = s.entity_id AND runs.schedule_id = '.$schedule->getId(), array())
			->where('runs.shipment_id IS NULL');
			if(strcasecmp($type, 'shipment') === 0 && strcasecmp($schedule->getStatus(),'shipment,all') != 0){
				$select->where('s.state = ?', $status);
			}				
		}
	
		return $this->_getReadAdapter()->fetchAll($select);
	}
	
	// Get all creditmemos that has not been in a run and that has the right statuses
	// return array of creditmemo objects
	public function getAllCreditmemosNotRun($schedule){
		$select = $this->_getReadAdapter()->select();
	
		list($type, $status) = explode(",", $schedule->getStatus());
		if (strcasecmp($type, 'order') === 0){
		//All based on order status
			$select->from(array('c' => $this->getTable('sales/creditmemo')), array('c.entity_id'))
			->joinLeft(array('o' => $this->getTable('sales/order')), 'c.order_id = o.entity_id', array())
			->joinLeft(array('runs' => $this->getMainTable()), 'runs.creditmemo_id = c.entity_id AND runs.schedule_id = '.$schedule->getId(), array())
			->where('runs.creditmemo_id IS NULL');
			if(strcasecmp($schedule->getStatus(),'order,all') != 0){
				$select->where('o.status = ?', $status);
			}
		}else{
		//All based on creditmemo status
			$select->from(array('c' => $this->getTable('sales/creditmemo')), array('c.entity_id'))
			->joinLeft(array('runs' => $this->getMainTable()), 'runs.creditmemo_id = c.entity_id AND runs.schedule_id = '.$schedule->getId(), array())
			->where('runs.creditmemo_id IS NULL');
			if(strcasecmp($type, 'creditmemo') === 0 && strcasecmp($schedule->getStatus(),'creditmemo,all') != 0){
				$select->where('c.state = ?', $status);
			}				
		}
	
		return $this->_getReadAdapter()->fetchAll($select);
	}
	
}