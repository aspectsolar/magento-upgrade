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
 * Fieldset element
 *
 * @category   Eltrino
 * @package    Eltrino_Region
 * @subpackage Adminhtml
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Eltrino_Region_Block_Adminhtml_System_Config_Fieldset_Elements extends Mage_Adminhtml_Block_Abstract
{
    public function getCountries($selectedCountryId = null, array $excludeFilter = array())
    {
        $selectHtml = '<select id="" class="select" name="country[]" onchange="countryChanged(this)">';
        $countriesList = Mage::helper('eltrino_region')->getCountriesList($excludeFilter);
        array_unshift($countriesList, array('value' => '', 'label' => Mage::helper('eltrino_region')->__('--Please Select--')));
        foreach ($countriesList as $item) {
            $selected = '';
            if ($selectedCountryId == $item['value']) {
                $selected = ' selected="selected"';
            }
            $selectHtml .= '<option value="' . $item['value'] . '"' . $selected . '>' . $item['label'] . '</option>';
        }
        $selectHtml .= '</select>';
        return $selectHtml;
    }

    public function getCommonSettings($countryId = null, $selectedCommonSettings = null)
    {
        $commonSettings = array();
        $disabled = '';
        if ($countryId) {
            $commonSettings = Mage::helper('eltrino_region')->getCommonSettingsList($countryId);
        } else {
            $countryId = '__DISABLED__';
        }
        if (empty($commonSettings)) {
            $disabled = ' disabled="disabled"';
            $commonSettings = array(array('value' => '__NOT_PROVIDED__', 'label' => $this->__('-- Not Provided --')));
        } else {
            array_unshift($commonSettings, array('value' => '', 'label' => Mage::helper('eltrino_region')->__('--Please Select--')));
        }

        $selectHtml = '<select id="" class="select" name="eltrino_region[common_settings][' . $countryId . ']"' . $disabled . ' onchange="commonSettingsChanged(this)">';
        foreach ($commonSettings as $item) {
            $selected = '';
            if ($selectedCommonSettings == $item['value']) {
                $selected = ' selected="selected"';
            }
            $selectHtml .= '<option value="' . $item['value'] . '"' . $selected . '>' . $item['label'] . '</option>';
        }
        $selectHtml .= '</select>';
        return $selectHtml;
    }

    /**
     * Return multi select html element of regions for a specific countries
     *
     * @param string $countryId
     * @param array $selectedRegions
     * @return string
     */
    public function getDisabledRegions($countryId = null, $selectedRegions = array())
    {
        $size = 20;
        $regions = array();
        if ($countryId) {
            $regions = Mage::helper('eltrino_region')->getRegionsList($countryId);
        } else {
            $countryId = '__DISABLED__';
        }

        if (count($regions) < 10) {
            $size = 10;
        }

        $multiSelectHtml = '<select id="" class="select multiselect" name="eltrino_region[disabled_regions][' . $countryId . '][]" size="' . $size . '" onchange="regionsChanged(this)" multiple="multiple">';
        foreach ($regions as $item) {
            $selected = '';
            if (in_array($item['value'], $selectedRegions)) {
                $selected = ' selected="selected"';
            }
            $multiSelectHtml .= '<option value="' . $item['value'] . '"' . $selected . '>' . $item['label'] . '</option>';
        }
        $multiSelectHtml .= '</select>';
        return $multiSelectHtml;
    }
}
