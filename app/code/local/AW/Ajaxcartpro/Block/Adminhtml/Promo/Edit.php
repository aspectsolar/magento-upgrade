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


class AW_Ajaxcartpro_Block_Adminhtml_Promo_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_promo';
        $this->_blockGroup = 'ajaxcartpro';
        $this->_addButton(
            'saveandcontinue',
            array(
                 'label'   => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                 'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                 'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit(url) {
                editForm.submit(url.replace(/{{tab_id}}/ig,ajaxcartpro_promo_tabsJsTabs.activeTab.id));
            }

            var checkActionTabs = function() {
                 if ($('general_type').value == " . AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::ADD_VALUE . ") {
                    $('ajaxcartpro_promo_tabs_add_action_content').select('input, select, textarea').each(function (elem) {
                        elem.disabled=false;elem.removeClassName('disabled');
                    });
                    $('ajaxcartpro_promo_tabs_remove_action_content').select('input, select, textarea').each(function (elem) {
                        elem.disabled=true;elem.addClassName('disabled');
                    });
                    $('ajaxcartpro_promo_tabs_add_action').up().show();
                    $('ajaxcartpro_promo_tabs_remove_action').up().hide();
                 } else {
                    $('ajaxcartpro_promo_tabs_add_action_content').select('input, select').each(function (elem) {
                        elem.disabled=true;elem.addClassName('disabled');
                    });
                    $('ajaxcartpro_promo_tabs_remove_action_content').select('input, select, textarea').each(function (elem) {
                        elem.disabled=false;elem.removeClassName('disabled');
                    });
                    $('ajaxcartpro_promo_tabs_add_action').up().hide();
                    $('ajaxcartpro_promo_tabs_remove_action').up().show();
                 }
            }

            document.observe('dom:loaded', function() {
                Event.observe('general_type', 'change', function(event) {
                    checkActionTabs();
                });
                checkActionTabs();
            });
        ";
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/' . $this->_controller . '/save', array('_current' => true));
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            array(
                 '_current' => true,
                 'back'     => 'edit',
                 'tab'      => '{{tab_id}}'
            )
        );
    }

    public function getHeaderText()
    {
        $id = $this->getRequest()->getParam('id', null);
        if ($id === null) {
            return $this->__('New Rule');
        }
        return $this->__("Edit Rule '%s'", Mage::registry('current_acp_promo')->getName());
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}