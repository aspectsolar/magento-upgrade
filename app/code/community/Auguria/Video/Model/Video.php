<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Model_Video extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('auguria_video/video');
    }

    public function getPreviewListUrl()
    {
		if (is_file(Mage::getBaseDir('media').DS.$this->getPreview())) {
			return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).str_replace(DS, '/', $this->getPreview());
		}
		else {
			return false;
		}
    }
}