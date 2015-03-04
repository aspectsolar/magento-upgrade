<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
	
	$this->startSetup();

	try {
		$table = $this->getTable('core/config_data');
		$cond = $this->getConnection()->quoteInto('path=?', 'wordpress/misc/path');
		$data = array(
			'path' => 'wordpress/integration/path'
		);
		
		$this->getConnection()->update($table, $data, $cond);
	}
	catch (Exception $e) {
		Mage::helper('wordpress')->log($e);
		throw $e;
	}

	$this->endSetup();
