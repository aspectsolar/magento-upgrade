<?php
/**
 * Remove or Change Displayed States and Regions
 *
 * LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE_OSL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 *
 * @category   Eltrino
 * @package    Eltrino_Region
 * @subpackage Install
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

if (!$installer->getConnection()->isTableExists($installer->getTable('eltrino_region/entity'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('eltrino_region/entity'))
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Region Entity Id')
        ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            ), 'Country Id')
        ->addColumn('region_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Region Id')
        ->setComment('Region limitations');
    $installer->getConnection()->createTable($table);

    $table = $installer->getConnection()
        ->newTable($installer->getTable('eltrino_region/common_settings'))
        ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(), 'Country Id')
        ->addColumn('common_settings', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Common Settings')
        ->setComment('Regions common settings');
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
