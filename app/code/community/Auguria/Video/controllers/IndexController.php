<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
    	$this->loadLayout();

    	$this->getLayout()->getBlock('customer');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb ('home', array(
        				'label'=>Mage::helper('cms')->__('Home'),
        				'title'=>Mage::helper('cms')->__('Home'), 'link'=>Mage::getBaseUrl()));
        $breadcrumbs->addCrumb ('Video', array(
        				'label'=>Mage::helper('auguria_video')->__('Video page'),
        				'title'=>Mage::helper('auguria_video')->__('Video page')));

        $this->renderLayout();
	}
}
