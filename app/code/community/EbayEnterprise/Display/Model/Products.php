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

class EbayEnterprise_Display_Model_Products extends Mage_Core_Model_Abstract
{
	const CSV_FIELD_DELIMITER = ',';
	const CSV_FIELD_ENCLOSURE = '"';
	/**
	 * Export product feeds. Get every store from every store group for every website.
	 */
	public function export()
	{
		// Email won't send if it's previously been sent. And I'm doing
		// The installation notification here because it seemed as good a place
		// as any, and seemed likely that we'd have something configured by now.
		Mage::getModel('eems_display/email')->sendInstalledNotification();
		foreach (Mage::app()->getWebsites() as $website) {
			foreach ($website->getGroups() as $storeGroup) {
				$this->_processStores($storeGroup->getStores());
			}
		}
	}
	/**
	 * Writes an array as a string into the resource fh
	 * @param $fh resource handle
	 * @param $dataRow array of values to be written
	 * @return int length of string written, or false on failure
	 */
	protected function _writeCsvRow($fh, array $dataRow)
	{
		return fputcsv($fh, $dataRow, self::CSV_FIELD_DELIMITER, self::CSV_FIELD_ENCLOSURE);
	}
	/**
	 * Processes output files for one Store Group. Each store that has a
	 * non-empty SiteId and is enabled gets a feed output. File name
	 * is StoreId.csv and it's placed in the configured feed file path.
	 * @param $stores array of Mage_Core_Model_Store(s) to process
	 * @return self
	 */
	protected function _processStores(array $stores)
	{
		$helper  = Mage::helper('eems_display/config');
		$dirName = $helper->getFeedFilePath();
		foreach ($stores as $store) {
			$storeId = $store->getId();
			$siteId  = $helper->getSiteId($storeId);
			if (empty($siteId) || !$helper->getIsEnabled($storeId)) {
				// If this store doesn't have a site id, or isn't enabled for Display, skip it
				continue;
			}
			$fh = fopen($dirName . DS . $storeId . '.csv', 'w');
			if ($fh === false) {
				// If we can't open the output file, complain to log and skip this store
				Mage::log("Cannot open file for writing in {$dirName}.");
				continue;
			}
			$this->_writeCsvRow($fh, array('Id', 'Name', 'Description', 'Price', 'Image URL', 'Page URL'));
			$this->_writeDataRows($fh, $storeId);
			fclose($fh);
		}
		return $this;
	}
	/**
	 * Gets the product image URL, ensuring that it gets resized
	 * @param $product One Mage_Catalog_Model_Product from a Collection
	 * @param $storeId int which store Id this is for
	 * @return image url, or blank if we can't get it figured out
	 */
	protected function _getResizedImage(Mage_Catalog_Model_Product $product, $storeId)
	{
		$helper = Mage::helper('eems_display/config');
		try {
			// Image implementation doesn't save the resize filed unless it's coerced into
			// a string. Its (php) magic '__toString()' method is what actually resizes and saves
			$imageUrl = (string) Mage::helper('catalog/image')
				->init($product, 'image')
				->keepAspectRatio(
					$helper->getFeedImageKeepAspectRatio($storeId)
				)
				->resize(
					$helper->getFeedImageWidth($storeId),
					$helper->getFeedImageHeight($storeId)
				);
		} catch (Exception $e) {
			$imageUrl = '';
			Mage::log("Error sizing Image URL for {$product->getSku()}: {$e->getMessage()}");
		}
		return $imageUrl;
	}
	/**
	 * Compile the product data for Fetchback into
	 * an array that can be put into a CSV.
	 * @param $fh resource handle
	 * @param $storeId Store id
	 * @return self
	 *
	 * The collection is formed thus:
	 * 1. Since we are working with multiple stores, we use Mage:getResourceModel('catalog/product_collection') instead of
	 * 		Mage::getModel('catalog/product')->getCollection() because the latter will load some store context we
	 * 		prefer to control explicitly.
	 * 2. setStore() is a method that will set up store context we do need. It does not set filtering.
	 * 3. addAttributeToSelect() to get only the fields we need. Each product's _data will contain only these fields.
	 * 4. addFieldToFilter() basically our 'where' clauses
	 * 5. addStoreFilter() called without arguments defaults to the store most recently setStore()'d. Admin store is explicitly
	 * 		ignored by addStoreFilter().
	 * 6. setPageSize() in order to limit collection's use of memory. This is the number of products we'll load at once.
	 */
	protected function _writeDataRows($fh, $storeId)
	{
		$helper   = Mage::helper('eems_display');
		$products = Mage::getResourceModel('catalog/product_collection')
			->setStore($storeId)
			->addAttributeToSelect(array('sku', 'name', 'short_description', 'price', 'url_key', 'image'))
			->addFieldToFilter('visibility', array('neq'=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
			->addFieldToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
			->addStoreFilter()
			->setPageSize(Mage::helper('eems_display/config')->getProductFeedPageSize());

		$lastPage = $products->getLastPageNumber();
		for($page = 1; $page <= $lastPage; $page++) {
			$products->setCurPage($page);
			foreach ($products as $product) {
				$this->_writeCsvRow($fh,
				array(
					$product->getSku(),
					$helper->cleanString($product->getName()),
					$helper->cleanString($product->getShortDescription()),
					$product->getPrice(),
					$this->_getResizedImage($product, $storeId),
					$product->getProductUrl(),
				));
			}
			$products->clear();
		}
		return $this;
	}
}
