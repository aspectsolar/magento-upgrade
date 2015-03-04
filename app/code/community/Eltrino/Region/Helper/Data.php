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
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Generic helper for module
 *
 * @category   Eltrino
 * @package    Eltrino_Region
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Eltrino_Region_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Return list of available countries
     *
     * @return array
     */
    public function getCountriesList()
    {
        $countryCollection = Mage::getResourceModel('directory/country_collection');
        $countriesWithRegions = Mage::getResourceModel('eltrino_region/entity')->getCountriesWithRegions();
        $allowCountries = explode(',', (string)Mage::getStoreConfig('general/country/allow'));
        $allowCountries = array_intersect($allowCountries, $countriesWithRegions);
        if (!empty($allowCountries)) {
            $countryCollection->addFieldToFilter("country_id", array('in' => $allowCountries));
        }
        $optionArray = $countryCollection->toOptionArray(false);
        return $optionArray;
    }

    /**
     * Return list of common settings for given country.
     * For example for US - States Only (not all regions included).
     *
     * @param string $countryId
     * @return array
     */
    public function getCommonSettingsList($countryId)
    {
        $optionArray = array();
        $commonSettingsNode = Mage::app()->getConfig()
            ->getNode('global/common_settings/' . $countryId);
        if ($commonSettingsNode) {
            $commonSettingsArr = $commonSettingsNode->asArray();
            foreach ($commonSettingsArr as $itemValue) {
                if (!isset($itemValue['label']) || !isset($itemValue['regions_code'])) {
                    continue;
                }
                $regionIds = array();
                $regionsCollection = Mage::getResourceModel('directory/region_collection')
                        ->addCountryFilter($countryId)
                        ->addRegionCodeFilter(array_keys($itemValue['regions_code']));
                foreach ($regionsCollection as $region) {
                    $regionIds[] = $region->getId();
                }
                $optionArray[] = array(
                    'value' => implode(',', $regionIds),
                    'label' => $itemValue['label']
                );
            }
        }
        return $optionArray;

    }

    /**
     * Return list of regions, if available, of given given country
     *
     * @param string $countryId
     * @return array
     */
    public function getRegionsList($countryId)
    {
        $optionArray = array();
        $regionsCollection = Mage::getResourceModel('directory/region_collection')->addCountryFilter($countryId);
        foreach ($regionsCollection as $item) {
            $optionArray[] = array(
                'value' => $item->getData('region_id'),
                'label' => $item->getData('default_name')
            );
        }
        return $optionArray;
    }
}
