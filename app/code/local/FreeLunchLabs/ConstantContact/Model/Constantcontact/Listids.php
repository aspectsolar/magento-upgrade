<?php

class FreeLunchLabs_ConstantContact_Model_Constantcontact_Listids {

    protected $_options;
    protected $_lists = array();

    protected function _getConstantContact() {
        return Mage::getModel('constantcontact/constantcontact');
    }

    public function toOptionArray($isMultiselect=false) {
        if (!$this->_options) {
            //get lists for admin
            $this->_options = $this->_lists;

            if (Mage::getStoreConfig('constantcontact/general/username') != "" && Mage::getStoreConfig('constantcontact/general/password') != "" && Mage::getStoreConfig('constantcontact/general/url') != "") {
                $arr = $this->_getConstantContact()->getLists();
                if (!empty($arr)) {
                    foreach ($arr as $opt) {
                        $this->_options[] = array('value' => $opt['id'], 'label' => $opt['title']);
                    }
                }
            }
        }

        $options = $this->_options;
        if (empty($this->_options) && Mage::getStoreConfig('constantcontact/general/username') != "" && Mage::getStoreConfig('constantcontact/general/password') != "" && Mage::getStoreConfig('constantcontact/general/url') != "") {
            if (!$isMultiselect) {
                array_unshift($options, array('value' => '', 'label' => Mage::helper('adminhtml')->__('Invalid CC Username and/or Password')));
            }
        }
        elseif (empty($this->_options)) {
            if (!$isMultiselect) {
                array_unshift($options, array('value' => '', 'label' => Mage::helper('adminhtml')->__('Enter your Username and Password to see lists')));
            }
        }
        else {
            if (!$isMultiselect) {
                array_unshift($options, array('value' => '', 'label' => Mage::helper('adminhtml')->__('--Please Select--')));
            }
        }
        return $options;
    }

}