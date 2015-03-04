<?php
class FreeLunchLabs_ConstantContact_Model_Constantcontact_Emailtype
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'html', 'label'=>Mage::helper('adminhtml')->__('HTML')),
            array('value'=>'text', 'label'=>Mage::helper('adminhtml')->__('Text')),
        );
    }

}