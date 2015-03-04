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


class AW_Ajaxcartpro_Model_Renderer_Confirmation_Removeproduct extends Varien_Object
    implements AW_Ajaxcartpro_Model_Renderer_Interface
{
    const BLOCK_NAME = 'aw.ajaxcartpro.confirm.removeproduct';

    public function renderFromLayout($layout)
    {
        $block = $layout->getBlock(self::BLOCK_NAME);
        if (!$block) {
            return null;
        }
        $block = $this->_addDataToBlock($block);
        return $block->toHtml();
    }

    private function _addDataToBlock($block)
    {
        $actionData = $this->getData('action_data');
        if (array_key_exists('removed_product', $actionData)) {
            $block->setData('product_id', $actionData['removed_product']);
            $promo = Mage::helper('ajaxcartpro/promo')->validate(
                $actionData['removed_product'], AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::REMOVE_VALUE
            );
            if (null !== $promo) {
                $block->setContent($promo->getPopupContent());
            }
        }
        return $block;
    }
}