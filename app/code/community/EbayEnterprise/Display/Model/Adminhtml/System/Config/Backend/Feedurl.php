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

class EbayEnterprise_Display_Model_Adminhtml_System_Config_Backend_Feedurl
	extends Mage_Core_Model_Config_Data
{
	/**
	 * Take the current configuration view and append the Display Feed frontName
	 * to present a complete route
	 */
	protected function _afterLoad()
	{
		parent::_afterLoad();
		$storeId = Mage::helper('eems_display')->getStoreIdForCurrentAdminScope();
		$siteId  = Mage::helper('eems_display/config')->getSiteId($storeId);
		if (empty($siteId)) {
			$this->setValue('');
		} else {
			$this->setValue(Mage::helper('eems_display')->getProductFeedUrl($storeId));
		}
		return $this;
	}
}
