<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.3.1
 * @license:     lMnQrjiQcxfPlbdwpFhsKslnzVPfVqO45IEZa4tVHs
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Model_Source_Unsubscribe extends Varien_Object
{
    public function toOptionArray()
    {
        $vals = array(
            0 => Mage::helper('adjcartalert')->__('Delete all pending alerts for current client'),
            1 => Mage::helper('adjcartalert')->__('Delete all pending alerts and store his email in \'Stop\' list for current store'),
            2 => Mage::helper('adjcartalert')->__('Delete all pending alerts and store his email in \'Stop\' list for all stores'),
            3 => Mage::helper('adjcartalert')->__('Allow clients to select an action'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                    'value' => $k,
                    'label' => $v
            );
        
        return $options;
    }
}