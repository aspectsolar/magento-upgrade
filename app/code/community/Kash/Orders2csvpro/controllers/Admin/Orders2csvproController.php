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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Kash
 * @package    Kash_Orders2csvpro
 * @copyright  Copyright (c) 2012 Kash (http://kash.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Genevieve Eddison, Jonathan Feist, Farai Kanyepi <sales@kash.com>
 * */
class Kash_Orders2csvpro_Admin_Orders2csvproController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Make selected csv file from selected orders
     */
    public function makecsvAction()
    {
    	$orders = $this->getRequest()->getPost('order_ids', array());
    	
		$file = Mage::getModel('orders2csvpro/csvgenerator_order')->getCsv($orders);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/order_'.$file));
    }

    /**
     * Make selected csv file from selected invoices
     */
    public function makeinvoicecsvAction()
    {
    	$elements = $this->getRequest()->getPost('invoice_ids', array());
    	
		$file = Mage::getModel('orders2csvpro/csvgenerator_invoice')->getCsv($elements);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/invoice_'.$file));
    }

    /**
     * Make selected csv file from selected shipment
     */
    public function makeshipmentcsvAction()
    {
    	$elements = $this->getRequest()->getPost('shipment_ids', array());
    	
		$file = Mage::getModel('orders2csvpro/csvgenerator_shipment')->getCsv($elements);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/shipment_'.$file));
    }

    /**
     * Make selected csv file from selected creditmemo
     */
    public function makecreditmemocsvAction()
    {
    	$elements = $this->getRequest()->getPost('creditmemo_ids', array());
    	
		$file = Mage::getModel('orders2csvpro/csvgenerator_creditmemo')->getCsv($elements);
		$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/creditmemo_'.$file));
    }
}
?>