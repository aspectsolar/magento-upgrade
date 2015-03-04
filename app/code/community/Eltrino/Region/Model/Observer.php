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
 * Event observers container
 *
 * @category   Eltrino
 * @package    Eltrino_Region
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Eltrino_Region_Model_Observer
{
    /**
     * Save regions configuration for countries
     *
     * @param Varien_Event_Observer $observer
     * @return Eltrino_Region_Model_Observer
     */
    public function storeCountryDisabledRegions(Varien_Event_Observer $observer)
    {
        $countries = Mage::app()->getRequest()->getPost('country', array());
        $regions   = Mage::app()->getRequest()->getPost('eltrino_region', array('disabled_regions' => array()));
        $regions   = $regions['disabled_regions'];

        $countryRegionsData = array();
        foreach ($countries as $country) {
            if (isset($regions[$country])) {
                $countryRegionsData[$country] = $regions[$country];
            } else {
                $countryRegionsData[$country] = array(0);
            }
        }

        // countries configuration should be limited to number of available countries in the system
        $countryRegionsData = array_slice($countryRegionsData, 0, count(Mage::helper('eltrino_region')->getCountriesList()));

        Mage::getModel('eltrino_region/entity')->storeCountriesDisabledRegions($countryRegionsData);

        return $this;
    }
}
