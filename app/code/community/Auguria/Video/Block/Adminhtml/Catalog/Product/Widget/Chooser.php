<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Block_Adminhtml_Catalog_Product_Widget_Chooser extends Mage_Adminhtml_Block_Catalog_Product_Widget_Chooser
{
	protected $_category;

	public function getRowClickCallback()
	{
		return "function (grid, event) {
			var trElement = Event.findElement(event, 'tr');
			var productId = trElement.down('td').innerHTML;
			var productName = trElement.down('td').next().next().innerHTML;

			$('auguria_video_product_id').value = productId.replace(/^\s+/g,'').replace(/\s+$/g,'');
			$('video_name').innerHTML = productName;
			winGrid.close();
		}";
	}

	public function getCategory()
	{
		if (!isset($this->_category)) {
			$categoryId = (int)$this->getCategoryId();
			if ($categoryId>0) {
				$this->_category = Mage::getModel('catalog/category')->load($categoryId);
			}
		}
		return $this->_category;
	}

	protected function _prepareCollection()
	{
		/* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
		$collection = Mage::getResourceModel('catalog/product_collection')
						->setStoreId(0)
						->addAttributeToSelect('name');

		if ($this->getCategory()) {
			$collection->addCategoryFilter($this->_category);
		}

		if ($productTypeId = $this->getProductTypeId()) {
			$collection->addAttributeToFilter('type_id', $productTypeId);
		}

		$this->setCollection($collection);

		return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
	}
}