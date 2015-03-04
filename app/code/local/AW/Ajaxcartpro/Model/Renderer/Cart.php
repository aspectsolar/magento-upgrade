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


class AW_Ajaxcartpro_Model_Renderer_Cart extends Varien_Object implements AW_Ajaxcartpro_Model_Renderer_Interface
{
    const TRIGGER_BLOCK_NAME = 'checkout.cart';
    const BLOCK_NAME = 'content';

    public function renderFromLayout($layout)
    {
        $block = $layout->getBlock(self::TRIGGER_BLOCK_NAME);
        if (!$block) {
            return null;
        }
        $block = $layout->getBlock(self::BLOCK_NAME);
        if (!$block) {
            return null;
        }
        return $block->toHtml();
    }
}