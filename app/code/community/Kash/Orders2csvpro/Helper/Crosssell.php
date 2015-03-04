<?php
/**
 * Magento
 *
 * NOTICE
 *
 * This source file a part of Kash_Orders2csvpro extension
 * all rights to this modul belongs to kash.com
 *
 * @category    Kash
 * @package     Kash_Orders2csvpro
 * @copyright   Copyright (c) 2011 kash (http://www.kash.com)
 */

class Kash_Orders2csvpro_Helper_Crosssell extends Mage_Catalog_Block_Product_Abstract
{
	/**
	 * Items quantity will be capped to this value
	 *
	 * @var int
	 */
	protected $_maxItemCount = 4;

	/**
	 * all products that crosssell shall be based on
	 *
	 * @var array
	 */
	protected $_baseProducts = array();

	/**
	 * Get crosssell items
	 *
	 * @input productsIds - all products that crosssell shall be based on
	 * @input maxItemCount - How many product shall be shown, default 4
	 * @return array
	 */
	public function getItems($products = array(), $maxItemCount = 4){
		$this->_maxItemCount = $maxItemCount;
		$this->_baseProducts = $products;
		 
		$items = array();
		foreach($this->_baseProducts as $product){
			$ninProductIds[] = $product->getProductId();
		} 
		
		if ($ninProductIds) {
			$collection = $this->_getCollection()
				->addProductFilter($ninProductIds)
                ->addExcludeProductFilter($ninProductIds)
				->setPageSize($this->_maxItemCount)
				->setGroupBy()
				->setPositionOrder()
				->load();
			foreach ($collection as $item) {
				$items[] = $item;
			}
			return $items;
		}else{
			return null;
		}		
	}
	
	/**
	* Get crosssell products collection
	*
	* @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
	*/
	protected function _getCollection()
	{
		$collection = Mage::getModel('catalog/product_link')->useCrossSellLinks()
		->getProductCollection()
		->setStoreId(Mage::app()->getStore()->getId())
		->addStoreFilter()
		->setPageSize($this->_maxItemCount);
		$this->_addProductAttributesAndPrices($collection);
		
		Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
	
		return $collection;
	}
	
	/**
	* Add all attributes and apply pricing logic to products collection
	* to get correct values in different products lists.
	* E.g. crosssells, upsells, new products, recently viewed
	*
	* @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
	* @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	*/
	protected function _addProductAttributesAndPrices(Mage_Catalog_Model_Resource_Product_Collection $collection)
	{
		return $collection
		->addTaxPercents()
		->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
		->addUrlRewrite();
	}
}
