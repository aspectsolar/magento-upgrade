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


class AW_Ajaxcartpro_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function hasFileOption()
    {
        $product = Mage::registry('current_product');
        if ($product) {
            foreach ($product->getOptions() as $option) {
                if ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isExtensionEnabled($extensionName)
    {
        return $this->isModuleOutputEnabled($extensionName);
    }

    public function getWysiwygVariables()
    {
        $variables = array(
            'label' => $this->__('AW Ajaxcartpro extension'),
            'value' => array(
                array(
                    'label' => $this->__('Product name'),
                    'value' => '{{var product.name}}'
                ),
                array(
                    'label' => $this->__('Product image'),
                    'value' => '{{block type="ajaxcartpro/confirmation_items_productimage" '
                        . 'product="$product" resize="135"}}'
                ),
                array(
                    'label' => $this->__("'Continue' button"),
                    'value' => '{{block type="ajaxcartpro/confirmation_items_continue"}}'
                ),
                array(
                    'label' => $this->__("'Go to checkout' button"),
                    'value' => '{{block type="ajaxcartpro/confirmation_items_gotocheckout"}}'
                ),
            )
        );
        return $variables;
    }

    public function addAjaxcartproVariablesToWysiwygConfig($config)
    {
        $magentoVariablePlugin = null;
        $plugins = $config->getData('plugins');
        foreach ($plugins as $key => $item) {
            if ($item['name'] === 'magentovariable') {
                $magentoVariablePlugin = array(
                    'key' => $key,
                    'data' => $item
                );
                break;
            }
        }
        if (is_null($magentoVariablePlugin)) {
            return $config;
        }

        $options = $magentoVariablePlugin['data']['options'];

        $originalUrl = $options['url'];
        $newUrl = Mage::getUrl('ajaxcartpro_admin/adminhtml_system_ajax/wysiwygPluginVariables');
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $newUrl = Mage::getUrl(
                'ajaxcartpro_admin/adminhtml_system_ajax/wysiwygPluginVariables',
                array('_secure' => true)
            );
        }
        $options['url'] = $newUrl;
        $options['onclick']['subject'] = str_replace($originalUrl, $newUrl, $options['onclick']['subject']);

        $magentoVariablePlugin['data']['options'] = $options;

        $plugins[$magentoVariablePlugin['key']] = $magentoVariablePlugin['data'];
        $config->setData('plugins', $plugins);
        return $config;
    }
}
