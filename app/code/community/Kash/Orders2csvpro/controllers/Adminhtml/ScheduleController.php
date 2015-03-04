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
class Kash_Orders2csvpro_Adminhtml_ScheduleController extends Mage_Adminhtml_Controller_Action
{
	const XPATH_CONFIG_SETTINGS_LICENSE					= 'orders2csvpro/settings/license_code';
	
	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('orders2csvpro/schedule')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Orders2csvpro Schedule structure'), Mage::helper('adminhtml')->__('Orders2csvpro Schedule structure'));

		return $this;
	}

	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function testAction() {
		$id     = $this->getRequest()->getParam('id');
	
		if ($id > 0) {
			Mage::getModel('orders2csvpro/orders2csvpro')->cronRun(true, $id);
		}
		//Mage::helper('kashshared')->checkLicenseOnline("orders2csvpro",Mage::getStoreConfig(self::XPATH_CONFIG_SETTINGS_LICENSE));
		$this->_redirect('*/*/index');
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('orders2csvpro/schedule')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('orders2csvpro_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('orders2csvpro/schedule');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Schedule element content'), Mage::helper('adminhtml')->__('Schedule element content'));
				
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('orders2csvpro/adminhtml_schedule_edit'))
			->_addLeft($this->getLayout()->createBlock('orders2csvpro/adminhtml_schedule_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('orders2csvpro')->__('Element does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->_forward('edit');
	}

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
				
			$model = Mage::getModel('orders2csvpro/schedule');
			$model->setData($data)->setId($this->getRequest()->getParam('id'));
				
			try {
				if ($this->getRequest()->getParam('saveas') == 1) {
					$model->setId(null);
				}
				$model->save();
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orders2csvpro')->__('Element was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back') || $this->getRequest()->getParam('save_as')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('orders2csvpro')->__('Unable to find element to save'));
		$this->_redirect('*/*/');
	}

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('orders2csvpro/schedule');
					
				$model->setId($this->getRequest()->getParam('id'))
				->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orders2csvpro')->__('Schedule was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function massDeleteAction() {
		$scheduleIds = $this->getRequest()->getParam('schedule');
		if(!is_array($scheduleIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('orders2csvpro')->__('Please select schedule element(s)'));
		} else {
			try {
				foreach ($scheduleIds as $scheduleId) {
					$schedule = Mage::getModel('orders2csvpro/schedule')->load($scheduleId);
					$schedule->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('orders2csvpro')->__('Total of %d schedule element(s) were successfully deleted', count($scheduleIds))
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massStatusAction()
	{
		$scheduleIds = $this->getRequest()->getParam('schedule');
		if(!is_array($scheduleIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('orders2csvpro')->__('Please select schedule element(s)'));
		} else {
			try {
				foreach ($scheduleIds as $scheduleId) {
					$schedule = Mage::getSingleton('orders2csvpro/schedule')
					->load($scheduleId)
					->setIsActive($this->getRequest()->getParam('status'))
					->setIsMassupdate(true)
					->save();
				}
				$this->_getSession()->addSuccess(
				Mage::helper('orders2csvpro')->__('Total of %d schedule element(s) were successfully updated', count($scheduleIds))
				);
			} catch (Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}
}