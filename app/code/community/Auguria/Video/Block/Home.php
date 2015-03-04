<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Auguria_Video_Block_Home extends Mage_Core_Block_Template
{
	public function getVideo()
	{
		$collection = Mage::getResourceModel('auguria_video/video_collection')
						->addFieldToFilter('status',1)
						->addFieldToFilter('display_on_home_page',1);
		$collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
		if ($collection && $collection->count()>0) {
			return $collection->getFirstItem();
		}
		return false;
	}

	public function getItemImage($_item)
	{
		$width = Mage::getStoreConfig('auguria_video/sizes/preview_home_width');
		$height = Mage::getStoreConfig('auguria_video/sizes/preview_home_height');
		return Mage::helper('auguria_video')->getPreviewResizedImage($_item, $width, $height);
	}
}