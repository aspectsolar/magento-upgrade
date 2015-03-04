<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxcartpro
 * @version    3.2.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


abstract class AW_Ajaxcartpro_Block_Confirmation_Abstract extends Mage_Core_Block_Template
{
    protected $_content = null;

    public function getContent($storeId = null)
    {
        throw new Exception('Method must be implemented');
    }

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    protected function _toHtml()
    {
        $content = $this->_process($this->getContent());
        $this->setContent($content);
        return parent::_toHtml();
    }

    protected function _process($text)
    {
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        $processor->setVariables($this->_getVariables());
        return $processor->filter($text);
    }

    private function _getVariables()
    {
        $variables = array();
        if ($productId = $this->getData('product_id')) {
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!is_null($product->getId())) {
                $variables['product'] = $product;
            }
        }
        return $variables;
    }
}
