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
class AdjustWare_Cartalert_Model_Source_Group extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_data = null;

    public function getAllOptions()
    {
        if(is_null($this->_data)) {
            $groups = Mage::app()->getGroups();
            foreach($groups as $group) {
                $this->_data[$group->getId()] = $group->getWebsite()->getName() .' -> '.$group->getName();
            }
        }
        return $this->_data;
    }

    public function toOptionArray()
    {
        $array = array(
            array('value' => 0, 'label'=>Mage::helper('adjcartalert')->__('')),
        );
        
        $levels = $this->getAllOptions();

        foreach($levels as $key=>$value)
        {
            $array[] = array('value' => $key, 'label'=>Mage::helper('adjcartalert')->__(ucfirst($value)));
        }

        return $array;
    }
    
    public function getOptionArray()
    {
        $array = array(
            0 => Mage::helper('adjcartalert')->__('All websites')
        );

        $levels = $this->getAllOptions();

        foreach($levels as $key=>$value)
        {
            $array[$key] = Mage::helper('adjcartalert')->__(ucfirst($value));
        }

        return $array;
    }
    
}