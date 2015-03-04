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


class AW_Ajaxcartpro_Model_Response extends Varien_Object
{
    protected $_error = array();
    protected $_msg = array();

    /**
     * @param string|array $msg
     * @return void
     */
    public function addError($msg)
    {
        if (is_array($msg)) {
            foreach ($msg as $item) {
                $this->addError($item);
            }
        } else if ($msg instanceof Mage_Core_Model_Message_Abstract) {
            $this->_error[] = $msg->getText();
        } else {
            $this->_error[] = $msg;
        }
    }

    public function addMsg($msg)
    {
        if (is_array($msg)) {
            foreach ($msg as $item) {
                $this->addMsg($item);
            }
        } else if ($msg instanceof Mage_Core_Model_Message_Abstract) {
            $this->_msg[] = array('text' => $msg->getText(), 'type' => $msg->getType());
        } else {
            $this->_msg[] = array('text' => $msg, 'type' => Mage_Core_Model_Message::NOTICE);
        }
    }

    public function isError()
    {
        return !empty($this->_error);
    }

    public function toJson(array $arrAttributes = array())
    {
        $this->setSuccess(true);
        if ($this->isError()) {
            $this->setSuccess(false);
            $this->setMsg($this->_error);
        } else {
            $this->setMsg($this->_msg);
        }
        return parent::toJson($arrAttributes);
    }
}
