<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Model_Mysql4_Video extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('auguria_video/video', 'auguria_video_id');
    }
}