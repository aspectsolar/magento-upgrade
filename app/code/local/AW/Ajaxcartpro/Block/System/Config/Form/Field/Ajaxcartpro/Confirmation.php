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


abstract class AW_Ajaxcartpro_Block_System_Config_Form_Field_Ajaxcartpro_Confirmation
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getHtmlId()
    {
        throw new Exception('Method must be implemented');
    }

    protected function _getPathToSetting()
    {
        throw new Exception('Method must be implemented');
    }

    protected function _previewJsInitString()
    {
        throw new Exception('Method must be implemented');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $config = array(
            'name'      => $this->_getPathToSetting(),
            'html_id'   => $this->_getHtmlId(),
            'label'     => 'Content',
            'title'     => 'Content',
            'style'     => 'height:36em;width:600px',
            'required'  => true,
            'config'    => $this->_getWysiwygConfig()
        );
        $element->addData($config);
        return $element->getElementHtml();
    }

    protected function _getWysiwygConfig()
    {
        $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        $config->addData(array('hidden' => true, 'enabled' => false));
        $config = Mage::helper('ajaxcartpro')->addAjaxcartproVariablesToWysiwygConfig($config);
        $config = $this->_addPreviewButton($config);
        return $config;
    }

    protected function _addPreviewButton($config)
    {
        $plugins = $config->getData('plugins');
        if (!is_array($plugins)) {
            $plugins = array();
        }
        $plugins[] = array(
            'options' => array(
                'title' => $this->__('Preview'),
                'onclick' => $this->_previewJsInitString()
            )
        );
        $config->setData('plugins', $plugins);
        return $config;
    }
}

