<?php

class Kash_Orders2csvpro_Adminhtml_HelpController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('orders2csvpro/help')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Order2CSV PRO Help'), Mage::helper('adminhtml')->__('Order2CSV PRO Help'));
			
			
		$this->_addLeft($this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tabs'));

		return $this;
	}

	public function indexAction() {
		$this->_initAction();

		$this->renderLayout();
	}

	public function exportGeneralCsvAction()
	{
		$fileName   = 'orders2csvpro_general_help.csv';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_general')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exportGeneralXmlAction()
	{
		$fileName   = 'orders2csvpro_general_help.xml';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_general')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exportProductCsvAction()
	{
		$fileName   = 'orders2csvpro_product_help.csv';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_product')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exportProductXmlAction()
	{
		$fileName   = 'orders2csvpro_product_help.xml';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_product')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exportProductbundleCsvAction()
	{
		$fileName   = 'orders2csvpro_productbundle_help.csv';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_productbundle')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exportProductbundleXmlAction()
	{
		$fileName   = 'orders2csvpro_productbundle_help.xml';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_productbundle')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exportCustomerCsvAction()
	{
		$fileName   = 'orders2csvpro_customer_help.csv';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_customer')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exportCustomerXmlAction()
	{
		$fileName   = 'orders2csvpro_customer_help.xml';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_customer')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	public function exportInvoiceCsvAction()
	{
		$fileName   = 'orders2csvpro_invoice_help.csv';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_invoice')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	public function exportInvoiceXmlAction()
	{
		$fileName   = 'orders2csvpro_invoice_help.xml';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_invoice')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	public function exportShippingCsvAction()
	{
		$fileName   = 'orders2csvpro_shipping_help.csv';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_shipping')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	public function exportShippingXmlAction()
	{
		$fileName   = 'orders2csvpro_shipping_help.xml';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_shipping')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	public function exportCreditCsvAction()
	{
		$fileName   = 'orders2csvpro_credit_help.csv';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_credit')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	public function exportCreditXmlAction()
	{
		$fileName   = 'orders2csvpro_credit_help.xml';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_credit')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	public function exportFormattingCsvAction()
	{
		$fileName   = 'orders2csvpro_formatting_help.csv';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_formatting')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	public function exportFormattingXmlAction()
	{
		$fileName   = 'orders2csvpro_formatting_help.xml';
		$content    = $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_formatting')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
}