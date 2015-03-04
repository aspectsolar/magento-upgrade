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
 * Renderer for region field
 *
 * @category   Eltrino
 * @package    Eltrino_Region
 * @subpackage Adminhtml
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Eltrino_Region_Model_Adminhtml_Customer_Renderer_Region extends Mage_Adminhtml_Model_Customer_Renderer_Region
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $countryId = false;
        if ($country = $element->getForm()->getElement('country_id')) {
            $countryId = $country->getValue();
        }

        if ($countryId) {
            if (!isset(self::$_regionCollections[$countryId])) {
                $disabledRegions = array();
                $disabledRegionsCollection = Mage::getResourceModel('eltrino_region/entity_collection')->load();
                foreach ($disabledRegionsCollection as $item) {
                    $disabledRegions[] = $item->getRegionId();
                }

                $directoryHelper = Mage::helper('eltrino_region/directory');
                $collection = Mage::getResourceModel('directory/region_collection')
                    ->addCountryFilter($countryId);
                if (!empty($disabledRegions)) {
                    $collection->addFieldToFilter($directoryHelper->getRegionTableAlias() . ".region_id", array('nin' => $disabledRegions));
                }
                self::$_regionCollections[$countryId] = $collection->toOptionArray();

            }
        }

        return parent::render($element);
    }
}
