<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Block_Adminhtml_Video extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_video';
		$this->_blockGroup = 'auguria_video';
		$this->_headerText = $this->__('Videos list');
		parent::__construct();
	}
}