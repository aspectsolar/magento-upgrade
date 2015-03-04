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


class AW_Ajaxcartpro_Block_Adminhtml_Promo_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Conditions');
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

    protected function _prepareForm()
    {
        $promoModel = Mage::registry('current_acp_promo');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/adminhtml_promo/newConditionHtml/form/rule_conditions_fieldset'))
        ;

        $fieldset = $form->addFieldset(
            'conditions_fieldset', array('legend' => $this->__('Conditions (leave blank for all products)'))
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions', 'text',
            array(
                 'name'     => 'conditions',
                 'label'    => $this->__('Conditions'),
                 'title'    => $this->__('Conditions'),
                 'required' => true,
            )
        )->setRule($promoModel)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->setValues($promoModel->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}