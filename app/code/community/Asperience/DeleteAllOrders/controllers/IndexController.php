<?php
/**
 * @category   ASPerience
 * @package	Asperience_DeleteAllOrders
 * @author	 ASPerience - www.asperience.fr
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';

class Asperience_DeleteAllOrders_IndexController extends Mage_Adminhtml_Sales_OrderController
{
	protected function _construct()
	{
		$this->setUsedModuleName('Asperience_DeleteAllOrders');
	}
	/**
	 * Delete selected orders
	 */
	public function indexAction()
	{
		$orderIds = $this->getRequest()->getPost('order_ids', array());
		if (Mage::getStoreConfig(Asperience_DeleteAllOrders_Model_Order::XML_PATH_SALES_IS_ACTIVE)) {
			$countDeleteOrder = 0;
			$countDeleteOrderGrid = 0;
			$countDeleteOrderGridException = 0;
			$countDeleteInvoice = 0;
			$countDeleteInvoiceGrid = 0;
			$countDeleteInvoiceGridException = 0;
			$countDeleteShipment = 0;
			$countDeleteShipmentGrid = 0;
			$countDeleteShipmentGridException = 0;
			$countDeleteCreditmemo = 0;
			$countDeleteCreditmemoGrid = 0;
			$countDeleteCreditmemoGridException = 0;
			$countDeleteTax = 0;
			$orders_delete = array();
			$invoices_delete = array();
			$creditmemos_delete = array();
			$shipments_delete = array();
			$orders_grid_delete = array();
			$invoices_grid_delete = array();
			$creditmemos_grid_delete = array();
			$shipments_grid_delete = array();
			$taxes_delete = array();
			$orders_undelete = array();
			$conn = Mage::getSingleton('core/resource')->getConnection('asperience/deleteallorders');
			try {
				foreach ($orderIds as $orderId) {
					$order = Mage::getModel('deleteallorders/order')->load($orderId);
					if($order->getIncrementId()) {
						$order_loaded = True;
					} else {
						$this->_getSession()->addWarning($this->__('No order loaded for id %s', $orderId));
						$order_loaded = False;
					}
					
					$order_to_delete = False;
					if($order_loaded) {
						if ($order->canDelete()) {
							$order_to_delete = True;
							
							if ($order->hasInvoices()) {
								$invoices = Mage::getResourceModel('sales/order_invoice_collection')->setOrderFilter($orderId)->load();
								foreach($invoices as $invoice){
									$id = $invoice->getId();
									$invoice = Mage::getModel('sales/order_invoice')->load($id);
									$invoices_delete[] = $invoice->getIncrementId();
									$invoice->delete();
									$countDeleteInvoice++;
								}
							}

							if ($order->hasShipments()) {
								$shipments = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($orderId)->load();
								foreach($shipments as $shipment){
									$id = $shipment->getId();
									$shipment = Mage::getModel('sales/order_shipment')->load($id);
									$shipments_delete[] = $shipment->getIncrementId();
									$shipment->delete();
									$countDeleteShipment++;
								}
							}
								
							if ($order->hasCreditmemos()) {
								$creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')->setOrderFilter($orderId)->load();
								foreach($creditmemos as $creditmemo){
									$id = $creditmemo->getId();
									$creditmemo = Mage::getModel('sales/order_creditmemo')->load($id);
									$creditmemos_delete[] = $creditmemo->getIncrementId();
									$creditmemo->delete();
									$countDeleteCreditmemo++;
								}
							}
							$order = Mage::getModel('sales/order')->load($orderId);
							$orders_delete[] = $order->getIncrementId();
							$order->delete();
							$countDeleteOrder++;
						} else {
							$orders_undelete[] = $order->getIncrementId();
						}
					} 
					
					if($order_to_delete || !$order_loaded) {

						$invoices = Mage::getModel('sales/resource_order_invoice_grid_collection')
							->addFieldToFilter('order_id', $orderId);
						foreach($invoices as $invoice){
							$invoices_grid_delete[] = $invoice->getIncrementId();
							$conn->delete(Mage::getSingleton('core/resource')->getTableName('sales/invoice_grid'),
									array('increment_id = ?' => (int) $invoice->getIncrementId()));
							$countDeleteInvoiceGrid++;
						}

						$shipments = Mage::getModel('sales/resource_order_shipment_grid_collection')
							->addFieldToFilter('order_id', $orderId);
						foreach($shipments as $shipment){
							$shipments_grid_delete[] = $shipment->getIncrementId();
							$conn->delete(Mage::getSingleton('core/resource')->getTableName('sales/shipment_grid'),
									array('increment_id = ?' => (int) $shipment->getIncrementId()));
							$countDeleteShipmentGrid++;
						}
						
						$credit_memos = Mage::getResourceModel('sales/order_creditmemo_grid_collection')
							->addFieldToFilter('order_id', $orderId);
						foreach($credit_memos as $credit_memo){
							$creditmemos_grid_delete[] = $credit_memo->getIncrementId();
							$conn->delete(Mage::getSingleton('core/resource')->getTableName('sales/creditmemo_grid'), 
									array('increment_id = ?' => (int) $credit_memo->getIncrementId()));
							$countDeleteCreditmemoGrid++;
						}

						$orders = Mage::getResourceModel('sales/order_grid_collection')
							->addFieldToFilter('entity_id', $orderId);
						foreach($orders as $order){
							$orders_grid_delete[] = $order->getIncrementId();
							$conn->delete(Mage::getSingleton('core/resource')->getTableName('sales/order_grid'), 
									array('entity_id = ?' => (int) $order->getEntityId()));
							$countDeleteOrderGrid++;
						}
					}
					if($order_to_delete) {
						/*$conn->delete(Mage::getSingleton('core/resource')->getTableName('tax/sales_order_tax'),
								array('order_id = ?' => (int) $tax->getOrderId()));*/
						$taxes = Mage::getModel('tax/sales_order_tax')->getCollection()
							->addFieldToFilter('order_id', $orderId);
						foreach($taxes as $tax){
							$id = $tax->getId();
							//Mage::log($tax->getData());
							$taxes_delete[] = $tax->getTaxId();
							$tax = Mage::getModel('tax/sales_order_tax')->load($id);
							$tax->delete();
							$countDeleteTax++;
						}
					}
				}
			} catch (Exception $e){
				$this->_getSession()->addError($this->__('An error arose during the deletion. %s', $e));
			}

			if ($countDeleteOrder > 0) {
				$this->_getSession()->addSuccess($this->__('%s order(s) was/were successfully deleted.', $countDeleteOrder));
				$this->_getSession()->addSuccess(implode(" ",$orders_delete));
			}
			if ($countDeleteOrderGrid > 0) {
				$this->_getSession()->addSuccess($this->__('%s order(s) was/were successfully deleted in grid.', $countDeleteOrderGrid));
				$this->_getSession()->addSuccess(implode(" ",$orders_grid_delete));
			}
			if ($countDeleteInvoice > 0) {
				$this->_getSession()->addSuccess($this->__('%s invoice(s) was/were successfully deleted.', $countDeleteInvoice));
				$this->_getSession()->addSuccess(implode(" ",$invoices_delete));
			}
			if ($countDeleteInvoiceGrid > 0) {
				$this->_getSession()->addSuccess($this->__('%s invoice(s) was/were successfully deleted in grid.', $countDeleteInvoiceGrid));
				$this->_getSession()->addSuccess(implode(" ",$invoices_grid_delete));
			}
			if ($countDeleteShipment > 0) {
				$this->_getSession()->addSuccess($this->__('%s shipment(s) was/were successfully deleted.', $countDeleteShipment));
				$this->_getSession()->addSuccess(implode(" ",$shipments_delete));
			}
			if ($countDeleteShipmentGrid > 0) {
				$this->_getSession()->addSuccess($this->__('%s shipment(s) was/were successfully deleted in grid.', $countDeleteShipmentGrid));
				$this->_getSession()->addSuccess(implode(" ",$shipments_grid_delete));
			}
			if ($countDeleteCreditmemo > 0) {
				$this->_getSession()->addSuccess($this->__('%s credit memo(s) was/were successfully deleted.', $countDeleteCreditmemo));
				$this->_getSession()->addSuccess(implode(" ",$creditmemos_delete));
			}
			if ($countDeleteCreditmemoGrid > 0) {
				$this->_getSession()->addSuccess($this->__('%s credit memo(s) was/were successfully deleted in grid.', $countDeleteCreditmemoGrid));
				$this->_getSession()->addSuccess(implode(" ",$creditmemos_grid_delete));
			}
			if(count($countDeleteTax) > 0) {
				$this->_getSession()->addSuccess($this->__('%s order tax(es) was/were successfully deleted.', $countDeleteTax));
			}
			if(count($orders_undelete) > 0) {
				$this->_getSession()->addWarning($this->__('Selected order(s) can not be deleted due to configuration:').implode(" ",$orders_undelete));
			}
		
		} else {
			$this->_getSession()->addError($this->__('This feature was deactivated.'));
		}
		$this->_redirect('adminhtml/sales_order/', array());
	}
}
