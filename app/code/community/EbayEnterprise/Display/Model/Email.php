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
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Model_Email extends Mage_Core_Model_Email
{
	const EMAIL_TEMPLATE       = 'eems_display_installed';
	const EMAIL_SENT_PATH      = 'marketing_solutions/eems_dislpay/install_email/sent';
	const SEND_TO_EMAIL_PATH   = 'marketing_solutions/eems_display/install_email/email';
	const SEND_TO_NAME_PATH    = 'marketing_solutions/eems_display/install_email/name';
	/**
	 * Send notification of installation to self::SEND_TO_EMAIL.
	 * @return void
	 */
	public function sendInstalledNotification()
	{
		$sent = Mage::getStoreConfig(self::EMAIL_SENT_PATH);
		if ($sent) {
			return;
		}
		Mage::getModel('core/email_template')
			->loadDefault(
				self::EMAIL_TEMPLATE
			)
			->setSenderEmail(
				Mage::getStoreConfig('trans_email/ident_general/email')
			)
			->setSenderName(
				Mage::getStoreConfig('trans_email/ident_general/name')
			)
			->send(
				Mage::getStoreConfig(self::SEND_TO_EMAIL_PATH),
				Mage::getStoreConfig(self::SEND_TO_NAME_PATH),
				// The array of variables made available to the template:
				array(
					'productFeedUrls' => implode(', ', $this->_getProductFeedUrls())
				)
			);
		$this->_markEmailSent();
		return;
	}
	/**
	 * Gather a list of all potential product feed URLs. Since we're doing this upon installation,
	 * we don't know what they'll (eventually) configure.
	 * @return array
	 */
	protected function _getProductFeedUrls()
	{
		$productFeedUrls = array();
		foreach (Mage::app()->getWebsites() as $website) {
			foreach ($website->getGroups() as $storeGroup) {
				foreach($storeGroup->getStores() as $store) {
					$productFeedUrls[] = Mage::helper('eems_display')->getProductFeedUrl($store->getId());
				}
			}
		}
		return $productFeedUrls;
	}
	/**
	 * Mark the configuration that the email has been sent
	 * @return void
	 */
	protected function _markEmailSent()
	{
		$config = Mage::getModel('core/config_data');
		$config->addData(array(
			'path' => self::EMAIL_SENT_PATH,
			'value' => 1,
			'scope' => 'default',
			'scope_id' => 0,
		));
		$config->save();
		return;
	}
}
