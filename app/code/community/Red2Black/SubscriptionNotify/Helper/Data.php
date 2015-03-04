<?php

/**
 * Red2Black SubscriptionNotify
 *
 * Magento extension
 *
 * NOTICE OF LICENSE
 * Magento is subject to the Open Software License (OSL 3.0)
 *
 * @author      Andre Nickatina<andre@r2bconcepts.com>
 * @category    Red2Black
 * @package     Red2Black_SubscriptionNotify
 * @copyright   R2Bconcepts.com 2011
 * @link        http://www.r2bconcepts.com Red2Black Concepts
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @version     0.1.0
*/
class Red2Black_SubscriptionNotify_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get a value from the module config
     *
     * Returns the configuration value for specified field id
     *
     * @access       public
     * @author       Andre Nickatina <andre@r2bconcepts.com>
     * @copyright    R2Bconcepts.com 2011
     * @param        string $fieldId the field id of the value needed
     * @return       bool || string depending on field id requested
     * @link         http://www.r2bconcepts.com Red2Black Concepts
     * @version      0.1.0
    */
    public function getConfig($fieldId)
    {
        return Mage::getStoreConfig('newsletter/subscriptionnotify/' . $fieldId);
    }
}
