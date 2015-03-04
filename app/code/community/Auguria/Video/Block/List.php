<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Auguria_Video_Block_List extends Mage_Core_Block_Template
{
	public function getVideos()
	{
		$collection = Mage::getResourceModel('auguria_video/video_collection')
						->addFieldToFilter('status',1);
		return $collection;
	}

	public function getItemImage($_item)
	{
		$width = Mage::getStoreConfig('auguria_video/sizes/preview_list_width');
		$height = Mage::getStoreConfig('auguria_video/sizes/preview_list_height');
		return Mage::helper('auguria_video')->getPreviewResizedImage($_item, $width, $height);
	}
}