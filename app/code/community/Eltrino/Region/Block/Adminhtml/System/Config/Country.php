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
 * @subpackage Adminhtml
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Renderer for country configuration field
 *
 * @category   Eltrino
 * @package    Eltrino_Region
 * @subpackage Adminhtml
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Eltrino_Region_Block_Adminhtml_System_Config_Country extends Mage_Adminhtml_Block_Widget_Accordion
{
    protected function _toHtml()
    {
        $country = Mage::getModel('directory/country')->load($this->getCountryId());
        $countryName = '';
        if ($country && $country->getId()) {
            $countryName = ' (' . $country->getName() . ')';
        }
        $container = $this->getLayout()->createBlock('eltrino_region/adminhtml_system_config_fieldset_container')
            ->setCountryId($this->getCountryId())
            ->setDisabledRegions($this->getDisabledRegions());
        $this->addItem('region_configuration', array(
            'title'   => Mage::helper('eltrino_region')->__('Region Configuration%s', $countryName),
            'content' => $container->toHtml(),
            'open'    => true,
        ));
        return parent::_toHtml();
    }
}
