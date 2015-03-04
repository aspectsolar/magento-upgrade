<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function statusToOptionArray()
	{
		return array (
				0 => $this->__('Disabled'),
				1 => $this->__('Enabled')
		);
	}

	public function getPreviewResizedImage($_video, $width, $height)
	{
		if (!$_video->getPreview()) {
			return false;
		}

		$baseImage = Mage::getBaseDir ( 'media' ) . DS . $_video->getPreview();
		if (!is_file($baseImage)) {
			return false;
		}

		$imageResized = Mage::getBaseDir ( 'media' ) . DS . "catalog" . DS . "product" . DS . "cache" . DS . (int)$width . DS . (int)$height. DS . $_video->getPreview();// Because clean Image cache function works in this folder only

		if (!file_exists($imageResized) && file_exists($baseImage) || file_exists($baseImage) && filemtime($baseImage) > filemtime($imageResized)) {
			if ((int)$height == 0) {
				$height = null;
			}
			if ((int)$width == 0) {
				$width = null;
			}
			$quality = 100;

			$imageObj = new Varien_Image ($baseImage);
			$imageObj->constrainOnly(true);
			$imageObj->keepAspectRatio(true);
			$imageObj->keepFrame(false);
			$imageObj->quality($quality);
			$imageObj->resize($width, $height);
			$imageObj->save($imageResized);
		}

		if (file_exists($imageResized)) {
			return Mage::getBaseUrl ( 'media' ) ."catalog/product/cache/".(int)$width.'/'.(int)$height.'/'.str_replace(DS, '/', $_video->getPreview());
		}
		else {
			return false;
		}
	}
}