<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Adminhtml_VideoController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('auguria_video')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Videos Manager'), Mage::helper('adminhtml')->__('Videos Manager'));
		return $this;
	}

	public function indexAction()
	{
		$this->_initAction()
			->renderLayout();
	}

	public function newAction()
	{
		$this->_forward('edit');
	}

	public function deleteAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('auguria_video/video')->load($id);
		if ($model->getId()) {
			$model->delete();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('auguria_video')->__("The video has been successfully deleted"));
			$this->_redirect('*/*/');
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('auguria_video')->__("This video doesn't exist"));
			$this->_redirect('*/*/');
		}
	}

	public function editAction()
	{
		$id = $this->getRequest()->getParam('id');

		$model = Mage::getModel('auguria_video/video')->load($id);
		if ($model->getId() || $id == 0)
		{
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data))
			{
				$model->setData($data);
			}

			Mage::register('video_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('auguria_video');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('auguria_video/adminhtml_video_edit'))
				->_addLeft($this->getLayout()->createBlock('auguria_video/adminhtml_video_edit_tabs'));

			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('auguria_video')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			if(isset($_FILES['preview']['name']) && $_FILES['preview']['name'] != '')
			{
				try
				{
					/* Starting upload */
					$uploader = new Varien_File_Uploader('preview');

					// Any extention would work
					$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setFilesDispersion(false);

					// Upload image and copy into product dir
					$path = Mage::getBaseDir('media') . DS . 'videos' . DS;
					$fileName = $_FILES['preview']['name'];
					$uploader->save($path, $fileName );

				}
				catch (Exception $e)
				{
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					Mage::getSingleton('adminhtml/session')->setFormData($data);
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					return;
				}

				//this way the name is saved in DB
				$data['preview'] = 'videos'.'/'.$_FILES['preview']['name'];
			}
			if (isset($data['preview']) && is_array($data['preview'])) {
				unset($data['preview']);
			}
			try {
				$id = $this->getRequest()->getParam('id');
				$model = Mage::getModel('auguria_video/video')->load($id);
				$data['auguria_video_id'] = $id;
				$model->setData($data);
				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('auguria_video')->__("The video has been successfully recorded"));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('auguria_video')->__("Unable to find change to save"));
		$this->_redirect('*/*/');
	}

	public function massDeleteAction()
	{
		$videoIds = $this->getRequest()->getParam('videos');
		if(!is_array($videoIds)) {
			$this->_getSession()->addError(Mage::helper('auguria_video')->__("Please select keys"));
		}
		else {
			foreach ($videoIds as $keyId) {
				$model  = Mage::getModel('auguria_video/video')->load($keyId);
				if ($model->getId()) {
					$model->delete();
				}
			}
			$this->_getSession()->addSuccess(
					$this->__('Total of %d record(s) were successfully deleted', count($videoIds))
			);
		}
		$this->_redirect('*/*/index');
	}

	public function massStatusAction()
	{
		$videoIds = $this->getRequest()->getParam('videos');
		if(!is_array($videoIds)) {
			$this->_getSession()->addError(Mage::helper('auguria_video')->__("Please select keys"));
		}
		else {
			foreach ($videoIds as $keyId) {
				$model  = Mage::getModel('auguria_video/video')->load($keyId);
				if ($model->getId()) {
					$model->setStatus($this->getRequest()->getParam('status'));
					$model->save();
				}
			}
			$this->_getSession()->addSuccess(
					$this->__('Total of %d record(s) were successfully updated', count($videoIds))
			);
		}
		$this->_redirect('*/*/index');
	}

	/*
	 public function exportCsvAction()
	 {
	$fileName   = 'productdescriptionKeys.csv';
	$content    = $this->getLayout()->createBlock('auguria_productdescription/adminhtml_change_grid')
	->getCsv();
	$this->_sendUploadResponse($fileName, $content);
	}

	public function exportXmlAction()
	{
	$fileName   = 'sponsorshipChange.xml';
	$content    = $this->getLayout()->createBlock('auguria_productdescription/adminhtml_change_grid')
	->getXml();

	$this->_sendUploadResponse($fileName, $content);
	}

	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
	$response = $this->getResponse();
	$response->setHeader('HTTP/1.1 200 OK','');
	$response->setHeader('Pragma', 'public', true);
	$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
	$response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
	$response->setHeader('Last-Modified', date('r'));
	$response->setHeader('Accept-Ranges', 'bytes');
	$response->setHeader('Content-Length', strlen($content));
	$response->setHeader('Content-type', $contentType);
	$response->setBody($content);
	$response->sendResponse();
	die;
	}
	*/
}
