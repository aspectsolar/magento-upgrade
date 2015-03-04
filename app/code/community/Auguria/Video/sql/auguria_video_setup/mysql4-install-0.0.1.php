<?php
/**
 * Create video table
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
$installer->startSetup();
$installer->run("
		-- DROP TABLE IF EXISTS {$this->getTable('auguria_video/video')};
		CREATE TABLE {$this->getTable('auguria_video/video')} (
			`auguria_video_id` int(11) unsigned NOT NULL auto_increment,
			`title` varchar(255) NOT NULL default '',
			`description` text,
			`image_identifier` varchar(255) NOT NULL default '',
			`preview` varchar(255) NOT NULL default '',
			`status` smallint(6) NOT NULL default '0',
			`product_id` int(11) unsigned,
			`display_on_home_page` tinyint(1) unsigned NOT NULL default '0',
			PRIMARY KEY (`auguria_video_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
