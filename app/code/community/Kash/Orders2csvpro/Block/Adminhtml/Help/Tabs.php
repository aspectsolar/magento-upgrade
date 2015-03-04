<?php
/**
 * Kash Orders2csvpro Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to Henrik Kier <info@kash.com> so we can send you a copy immediately.
 *
 * @category   Kash
 * @package    Kash_Orders2csvpro
 * @copyright  Copyright (c) 2012 Kash (http://kash.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Henrik Kier <info@kash.com>
 * */

class Kash_Orders2csvpro_Block_Adminhtml_Help_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('help_tabs');
		//$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('orders2csvpro')->__('Orders2CSV PRO Help'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('generel_section', array(
          'label'     => Mage::helper('orders2csvpro')->__('General'),
          'title'     => Mage::helper('orders2csvpro')->__('All general variabels'),
          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_general')->toHtml(),
		));
		//      //Tab for order section excluded because no specific variables exsist except general
		//      $this->addTab('order_section', array(
		//          'label'     => Mage::helper('orders2csvpro')->__('Order pdf'),
		//          'title'     => Mage::helper('orders2csvpro')->__('Order pdf - variabels'),
		//          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_order')->toHtml(),
		//      ));
		$this->addTab('invoice_section', array(
          'label'     => Mage::helper('orders2csvpro')->__('Invoice'),
          'title'     => Mage::helper('orders2csvpro')->__('Invoice variabels'),
          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_invoice')->toHtml(),
		));
		$this->addTab('shipping_section', array(
          'label'     => Mage::helper('orders2csvpro')->__('Shipping'),
          'title'     => Mage::helper('orders2csvpro')->__('Shipping variabels'),
          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_shipping')->toHtml(),
		));
		$this->addTab('credit_section', array(
          'label'     => Mage::helper('orders2csvpro')->__('Creditmemo'),
          'title'     => Mage::helper('orders2csvpro')->__('Creditmemo variabels'),
          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_credit')->toHtml(),
		));
		$this->addTab('product_section', array(
          'label'     => Mage::helper('orders2csvpro')->__('Product'),
          'title'     => Mage::helper('orders2csvpro')->__('Product variables'),
          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_product')->toHtml(),
		));
		$this->addTab('productbundle_section', array(
          'label'     => Mage::helper('orders2csvpro')->__('Product Bundle'),
          'title'     => Mage::helper('orders2csvpro')->__('Product Bundle variables'),
          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_productbundle')->toHtml(),
		));
// 		$this->addTab('cross_section', array(
//           'label'     => Mage::helper('orders2csvpro')->__('Cross Sell'),
//           'title'     => Mage::helper('orders2csvpro')->__('Cross Sell variables'),
//           'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_crosssell')->toHtml(),
// 		));
		$this->addTab('customer_section', array(
          'label'     => Mage::helper('orders2csvpro')->__('Customer'),
          'title'     => Mage::helper('orders2csvpro')->__('Order Customer variables'),
          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_customer')->toHtml(),
		));
		$this->addTab('formatting_section', array(
          'label'     => Mage::helper('orders2csvpro')->__('Formatting types'),
          'title'     => Mage::helper('orders2csvpro')->__('Example of formatting variables'),
          'content'   => $this->getLayout()->createBlock('orders2csvpro/adminhtml_help_tab_formatting')->toHtml(),
		));

		return parent::_beforeToHtml();
	}
}