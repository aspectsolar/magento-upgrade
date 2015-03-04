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


class AW_Ajaxcartpro_Block_Adminhtml_Promo_Edit_Tab_Action_Remove
    extends AW_Ajaxcartpro_Block_Adminhtml_Promo_Edit_Tab_Action_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _getHtmlIdPrefix()
    {
        return 'remove_action_';
    }

    protected function _prepareFormValues($values)
    {
        if (array_key_exists('rule_id', $values)
            && $values['type'] == AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::REMOVE_VALUE
        ) {
            return $values;
        }
        $values['popup_content'] = Mage::helper('ajaxcartpro/config')->getRemoveProductConfirmationContent();
        $values['show_dialog'] = Mage::helper('ajaxcartpro/config')->getRemoveProductConfirmationEnabled();
        $values['close_dialog_after'] = Mage::helper('ajaxcartpro/config')->getRemoveProductConfirmationCountdown();
        return $values;
    }
}