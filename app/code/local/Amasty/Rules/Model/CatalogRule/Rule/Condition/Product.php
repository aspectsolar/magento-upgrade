<?php
/**
 * @copyright   Copyright (c) 2009-14 Amasty
 */
class Amasty_Rules_Model_CatalogRule_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    /**
     * Validate product attrbute value for condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $attrCode = $this->getAttribute();

        if ('category_ids' == $attrCode && isset($object->getCategoryIds())) {
	    return $this->validateAttribute($object->getCategoryIds());
        }
        return parent::validate($object);
    }
  
}
