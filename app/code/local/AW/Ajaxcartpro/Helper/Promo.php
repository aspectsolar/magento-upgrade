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


class AW_Ajaxcartpro_Helper_Promo extends Mage_Core_Helper_Abstract
{
    /**
     * @param int $productId
     * @param int $ruleType
     *
     * @return AW_Ajaxcartpro_Model_Promo|null
     */
    public function validate($productId, $ruleType = AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::ADD_VALUE)
    {
        $_result = null;
        $collection = Mage::getModel('ajaxcartpro/promo')->getResourceCollection();
        $collection
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addTypeFilter($ruleType)
            ->addActiveFilter()
            ->addDateFilter()
            ->addCustomerGroupFilter(Mage::getSingleton('customer/session')->getCustomerGroupId())
            ->addSortOrderByPriority()
        ;
        $productModel = Mage::getModel('catalog/product')->load($productId);

        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $productCollection->addWebsiteFilter(Mage::app()->getWebsite());

        foreach ($collection as $_item) {
            $promoRule = Mage::getModel('ajaxcartpro/promo')->load($_item->getId());
            $promoRule->getConditions()->collectValidatedAttributes($productCollection);
            if ($promoRule->validate($productModel)) {
                $_result = $promoRule;
                break;
            }
        }
        return $_result;
    }
}