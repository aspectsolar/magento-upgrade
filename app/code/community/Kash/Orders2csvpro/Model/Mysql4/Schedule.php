<?php
/**
* Kash Orders2csvpro Module
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to Henrik Kier <info@kash.com> so we can send you a copy immediately.
*
* @category   Kash
* @package    Kash_Orders2csvpro
* @copyright  Copyright (c) 2012 Kash (http://kash.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* @author     Henrik Kier <info@kash.com>
* */
class Kash_Orders2csvpro_Model_Mysql4_Schedule extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		// Note that the orders2csvpro_id refers to the key field in your database table.
		$this->_init('orders2csvpro/schedule', 'schedule_id');
		
	}

	protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
		if (! $object->getId()) {
			$object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
		}
		$object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
		return $this;
	}
}