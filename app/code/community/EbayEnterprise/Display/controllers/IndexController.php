<?php
/**
 * Copyright (c) 2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the eBay Enterprise
 * Magento Extensions End User License Agreement
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf
 *
 * @copyright   Copyright (c) 2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf  eBay Enterprise Magento Extensions End User License Agreement
 *
 */

/**
 * There is no index action for this controller. You must use 'retrieve?id=' to retrieve
 * an existing feed. If we decide later we want to create feeds upon request via this
 * controller, just add another action.
 */
class EbayEnterprise_Display_IndexController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Provides a feed for the given store parameter
	 */
	public function retrieveAction()
	{
		$helper   = Mage::helper('eems_display/config');
		$storeId  = Mage::app()->getRequest()->getParam('id');
		$store    = Mage::getModel('core/store')->load($storeId);
		if (!$store->getId() || !$helper->getIsEnabled($storeId) || !$helper->getSiteId($storeId)) {
			// Store is invalid, or not enabled for EEMS Display, or there's no SiteId here.
			$this->_sendNotFound();
			return;
		}
		$fileName = Mage::helper('eems_display/config')->getFeedFilePath() . DS . $storeId . '.csv';
		if (!file_exists($fileName)) {
			// File not here, can't send it
			$this->_sendNotFound();
			return;
		}
		$contents = file_get_contents($fileName);
		$this->_sendContents($contents);
	}
	/**
	 * Sends an http response with the given contents
	 * @param type $contents
	 */
	protected function _sendContents($contents)
	{
		$this->getResponse()
			->setHeader('Content-Type', 'text/csv')
			->appendBody($contents);
	}
	/**
	 * Send not found.
	 */
	protected function _sendNotFound()
	{
		$this->getResponse()
			->setRawHeader('HTTP/1.1, 404');
	}
}
