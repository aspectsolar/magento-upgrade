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
class Eltrino_Region_Block_Adminhtml_System_Config_Fieldset_Container extends Mage_Adminhtml_Block_Widget
{
    protected $_template = 'eltrino/region/system/config/fieldset/container.phtml';

    protected $_countryId = null;

    protected $_disabledRegions = array();

    public function setCountryId($countryId)
    {
        $this->_countryId = $countryId;
        return $this;
    }

    public function getCountryId()
    {
        return $this->_countryId;
    }

    public function setDisabledRegions($disabledRegions)
    {
        $this->_disabledRegions = $disabledRegions;
        return $this;
    }

    public function getDisabledRegions()
    {
        return $this->_disabledRegions;
    }

    public function getCountriesElmHtml()
    {
        return $this->getLayout()->createBlock('eltrino_region/adminhtml_system_config_fieldset_elements')
            ->getCountries($this->getCountryId());
    }

    /**
     * Return HTML of common settings element
     *
     * @return string
     */
    public function getCommonSettingsElmHtml()
    {
        $regions = $this->getDisabledRegions();
        $selected = null;
        if ($regions) {
            $commonSettings = Mage::helper('eltrino_region')->getCommonSettingsList($this->getCountryId());
            foreach ($commonSettings as $item) {
                $preselectedRegions = explode(',', $item['value']);
                if (count($regions) != count($preselectedRegions)) {
                    break;
                }
                foreach ($preselectedRegions as $region) {
                    if (!in_array($region, $regions)) {
                        break;
                    }
                }
                $selected = $item['value'];
            }
        }

        return $this->getLayout()->createBlock('eltrino_region/adminhtml_system_config_fieldset_elements')
            ->getCommonSettings($this->getCountryId(), $selected);
    }

    public function getDisabledRegionsElmHtml()
    {
        return $this->getLayout()->createBlock('eltrino_region/adminhtml_system_config_fieldset_elements')
            ->getDisabledRegions($this->getCountryId(), $this->getDisabledRegions(), $this->getCommonSettings());
    }

    public function getRemoveButtonHtml()
    {
        return $this->getButtonHtml(Mage::helper('eltrino_region')->__('Remove'),
            'this.up(\'div.region-configuration-container\').remove();return false;', 'delete');
    }
}
