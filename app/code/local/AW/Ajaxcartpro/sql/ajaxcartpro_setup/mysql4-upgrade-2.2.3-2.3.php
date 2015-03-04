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

$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('catalog_product', 'aw_acp_disabled', array(
    'backend' => 'giftmessage/entity_attribute_backend_boolean_config',
    'input' => 'select',
    'label' => 'Disable Ajax Cart Pro for that product',
    'source' => 'eav/entity_attribute_source_boolean',
    'required' => false,
    'entity_model' => 'catalog/product',
    'group' => 'ACP Options',
    'global' => true,
    'visible' => true,
    'user_defined' => false,
    'default' => '0',
    'visible_on_front' => false
));

$installer->endSetup();
