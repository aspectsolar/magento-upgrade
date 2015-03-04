<?php

$installer = $this;
$installer->startSetup();

$updateString = <<<EOT
ALTER TABLE {$installer->getTable('orders2csvpro/file')} ADD COLUMN `status_after` varchar(255) NULL COMMENT 'Change to status after manual run'; 
ALTER TABLE {$installer->getTable('orders2csvpro/schedule')} ADD COLUMN `status_after` varchar(255) NULL COMMENT 'Change to status after schedule run'; 
EOT;

$installer->run($updateString);
$installer->endSetup();