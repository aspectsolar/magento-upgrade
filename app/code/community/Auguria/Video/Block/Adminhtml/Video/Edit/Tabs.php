<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Block_Adminhtml_Video_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('video_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('auguria_video')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('auguria_video')->__('Item Information'),
          'title'     => Mage::helper('auguria_video')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('auguria_video/adminhtml_video_edit_tab_form')->toHtml(),
      ));

      return parent::_beforeToHtml();
  }
}