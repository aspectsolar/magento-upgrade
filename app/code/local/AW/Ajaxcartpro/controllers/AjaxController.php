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


class AW_Ajaxcartpro_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function addProductConfirmationAction()
    {
        $textToGenerate = $this->getRequest()->getParam('textToGenerate', false);
        $this->loadLayout();

        $confirmationBlock = $this->_getConfirmationBlock('aw.ajaxcartpro.confirm.addproduct');
        $confirmationBlock->setData($this->_getDemoData());
        $confirmationBlock->setContent($textToGenerate);

        $this->renderLayout();
        return;
    }

    public function removeProductConfirmationAction()
    {
        $textToGenerate = $this->getRequest()->getParam('textToGenerate', false);
        $this->loadLayout();

        $confirmationBlock = $this->_getConfirmationBlock('aw.ajaxcartpro.confirm.removeproduct');
        $confirmationBlock->setData($this->_getDemoData());
        $confirmationBlock->setContent($textToGenerate);

        $this->renderLayout();
        return;
    }

    private function _getConfirmationBlock($blockName)
    {
        $layout = $this->getLayout();
        $block = $layout->getBlock($blockName);
        return $block;
    }

    private function _getDemoData()
    {
        $productCollection = Mage::getModel('catalog/product')->getResourceCollection();
        $count = $productCollection->getSize();
        $ids = $productCollection->getAllIds(1, $count -1);
        return array(
            'product_id' => $ids[0]
        );
    }
}