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
 * Model for configuration entity
 *
 * @category   Eltrino
 * @package    Eltrino_Region
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Eltrino_Region_Model_Entity extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('eltrino_region/entity');
    }

    /**
     * Store countries with disabled regions configuration
     *
     * @param array $regions
     * @return Eltrino_Region_Model_Entity
     */
    public function storeCountriesDisabledRegions(array $regions = array())
    {
        $this->getResource()->storeCountriesDisabledRegions($regions);
        return $this;
    }

    /**
     * Retrieve countries with disabled regions configuration
     *
     * @return array
     */
    public function fetchCountriesDisabledRegions()
    {
        return $this->getResource()->fetchCountriesDisabledRegions();
    }
}
