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
 * A class that checks that the entered md5 Site Id Checksum mathes the one we generate.
 */
class EbayEnterprise_Display_Model_Adminhtml_System_Config_Backend_Siteidchecksum
	extends Mage_Core_Model_Config_Data
{
	const SESSION_KEY  = 'adminhtml/session';
	const CONTACT_INFO = 'Contact dl-ebayent-displaysupport@ebay.com, or call (888) 343-6411 ext. 4 to obtain your Site Id and Site Id Checksum';
	const FIELD_SEP    = ':';
	/**
	 * Send only the Checksum part of the Site Id Checksum Field.
	 * @return self
	 */
	protected function _afterLoad()
	{
		parent::_afterLoad();
		list($justTheHash,) = Mage::helper('eems_display')->splitSiteIdChecksumField($this->getOldValue());
		$this->setValue($justTheHash);
		return $this;
	}
	/**
	 * Checks to see if we have a new and valid Site Id Checksum Entered
	 * @return self
	 */
	protected function _beforeSave()
	{
		$helper = Mage::helper('eems_display'); // We need this helper several times herein

		$newChecksum = $this->getValue();
		list($oldHash,$oldSiteId) = $helper->splitSiteIdChecksumField($this->getOldValue());
		if (empty($newChecksum) && empty($oldHash)) {
			// If both old and new checksums are still empty, prompt with some help info.
			$this->_dataSaveAllowed = false;
			Mage::getSingleton(self::SESSION_KEY)
				->addWarning('Please note that tracking is not enabled. Site Id Checksum is empty. ' . self::CONTACT_INFO);
			return $this;
		}
		$storeId    = $helper->getStoreIdForCurrentAdminScope();
		$formFields = $this->getFieldsetData();
		$newSiteId  = $formFields['site_id'];

		// Not allowed to change the Checksum unless we previously had a hash and we are changing the Site Id
		if (!empty($oldHash) && $oldSiteId === $newSiteId) {
			$this->_dataSaveAllowed = false;
			return $this;
		}
		// Check that the value provided in newCheckSum matches what we calculate for ourHash.
		$url = parse_url($helper->getProductFeedUrl($storeId), PHP_URL_HOST);
		$ourHash = md5($newSiteId . $url);
		if ($ourHash === $newChecksum) {
			// Upon success, we save the hash and the newSiteId. In the frontend at runtime,
			// we just have make sure that the siteId matches the runtime siteId
			$this->setValue($newChecksum . self::FIELD_SEP . $newSiteId);
			parent::_beforeSave();
		} else {
			$this->setValue(self::FIELD_SEP);
			Mage::getSingleton(self::SESSION_KEY)
				->addError('Failed to validate the Site Id. ' . self::CONTACT_INFO);
		}
		return $this;
	}
}
