<?php
class VladimirPopov_WebForms_Model_Webforms_Smtpvalidation
    extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/webforms_smtpvalidation');
    }

    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('webforms')->__('Default')),
            array('value' => 2, 'label' => Mage::helper('webforms')->__('Yes')),
            array('value' => 1, 'label' => Mage::helper('webforms')->__('No')),
        );
    }
}