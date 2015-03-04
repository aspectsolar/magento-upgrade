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


abstract class AW_Ajaxcartpro_Block_Adminhtml_Promo_Edit_Tab_Action_Abstract extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Action');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    abstract protected function _getHtmlIdPrefix();

    /**
     * @param array $values
     *
     * @return array
     */
    abstract protected function _prepareFormValues($values);

    protected function _prepareForm()
    {
        $promoModel = Mage::registry('current_acp_promo');
        $form = new Varien_Data_Form();
        $htmlIdPrefix = $this->_getHtmlIdPrefix();
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset(
            'fieldset',
            array(
                 'legend' => $this->__('Update Confirmation Block Using the Following Information')
            )
        );

        $fieldset->addType('acp_block_editor', 'AW_Ajaxcartpro_Block_Form_Element_AcpEditor');
        $fieldset->addField(
            'popup_content', 'acp_block_editor',
            array(
                 'label'              => $this->__('Block editor'),
                 'title'              => $this->__('Block editor'),
                 'style'              => 'height:36em;width:45em;',
                 'name'               => 'popup_content',
                 'config'             => $this->_getWysiwygConfig(),
                 'after_element_html' => $this->_getDefaultCheckbox($htmlIdPrefix . 'popup_content', 'popup_content'),
            )
        );

        $fieldset->addField(
            'show_dialog', 'select',
            array(
                 'label'              => $this->__('Show confirmation dialog'),
                 'name'               => 'show_dialog',
                 'options'            => array(
                     '1' => $this->__('Yes'),
                     '0' => $this->__('No'),
                 ),
                 'after_element_html' => $this->_getDefaultCheckbox($htmlIdPrefix . 'show_dialog', 'show_dialog'),
            )
        );

        $afterElementHtml = $this->_getDefaultCheckbox($htmlIdPrefix . 'close_dialog_after', 'close_dialog_after');
        $fieldset->addField(
            'close_dialog_after', 'text',
            array(
                 'label'              => $this->__('Confirmation dialog will be closed after, sec (set 0 to disable)'),
                 'name'               => 'close_dialog_after',
                 'after_element_html' => $afterElementHtml
            )
        );

        $form->setValues($this->_prepareFormValues($promoModel->getData()));
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _getDefaultCheckbox($fieldId, $fieldName)
    {
        $promoModel = Mage::registry('current_acp_promo');
        $configurationUrl = Mage::helper('adminhtml')->getUrl(
            'adminhtml/system_config/edit', array('section' => 'ajaxcartpro')
        );
        $afterElementHtml = '<div><input type="checkbox" id="use_config_' . $fieldId . '" '
            . 'name="use_config_' . $fieldName . '" value="1"'
            . 'onclick="toggleValueElements(this, this.parentNode.parentNode);">'
            . '<label for="use_config_' . $fieldId . '" class="normal">'
            .  $this->__('Use the option from <a href="%s">Global Extension Settings</a>', $configurationUrl)
            . '</label></div>'
        ;
        if ($promoModel->getData('use_config_'. $fieldName) || null === $promoModel->getId()) {
            $afterElementHtml .= '<script type="text/javascript">'
                . 'Event.observe(window, "load", function(){'
                . '$("use_config_' . $fieldId . '").click();'
                . '});'
                . '</script>'
            ;
        }
        return $afterElementHtml;
    }

    protected function _getWysiwygConfig()
    {
        $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        $data = $this->_recursiveUrlUpdate($config->getData());
        $config->setData($data);
        return $config;
    }

    protected function _recursiveUrlUpdate($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->_recursiveUrlUpdate($value);
            }
            if (is_string($value)) {
                $data[$key] = str_replace('ajaxcartpro_admin', 'admin', $value);
            }
        }
        return $data;
    }

}