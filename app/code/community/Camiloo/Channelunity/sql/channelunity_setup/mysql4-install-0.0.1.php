<?php

$installer = $this;
$installer->startSetup();
    try {
        $allTypes = Mage::app()->useCache();
        foreach($allTypes as $type => $blah) {
            Mage::app()->getCacheInstance()->cleanType($type);
        }
    } catch (Exception $e) {
        // do something
        error_log($e->getMessage());
    }
    

$installer->endSetup();
?>