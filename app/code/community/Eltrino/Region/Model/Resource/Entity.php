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
 * Resource model for configuration entity
 *
 * @category   Eltrino
 * @package    Eltrino_Region
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Eltrino_Region_Model_Resource_Entity extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('eltrino_region/entity', 'entity_id');
    }

    /**
     * Store disabled regions configuration
     *
     * @param array $regions
     * @return Eltrino_Region_Model_Resource_Entity
     */
    public function storeCountriesDisabledRegions(array $regions = array())
    {
        $data = array();
        foreach ($regions as $countryId => $region) {
            if (strlen($countryId)) {
                foreach ($region as $regionId) {
                    $data[] = array('country_id' => $countryId, 'region_id' => $regionId);
                }
            }
        }

        // before store $regions all previous data should be removed
        $this->_getWriteAdapter()->truncateTable($this->getMainTable());

        if (!empty($data)) {
            $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $data);
        }

        return $this;
    }

    public function storeCommonSettings(array $commonSettings = array())
    {
        $data = array();
        foreach ($commonSettings as $countryId => $commonSetting) {
            $data[] = array('country_id' => $countryId, 'common_settings' => $commonSetting);
        }
        $this->_getWriteAdapter()->truncateTable($this->getTable('eltrino_region/common_settings'));

        if (!empty($data)) {
            $this->_getWriteAdapter()->insertMultiple($this->getTable('eltrino_region/common_settings'), $data);
        }
        return $this;
    }

    /**
     * Fetch rows of disabled regions configuration
     *
     * @return array
     */
    public function fetchCountriesDisabledRegions()
    {
        $data = array();

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('*'));
        $rows = $this->_getReadAdapter()->fetchAll($select);

        foreach ($rows as $row) {
            $data[$row['country_id']][] = $row['region_id'];
        }

        return $data;
    }

    public function fetchCommonSettings()
    {
        $data = array();

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('eltrino_region/common_settings'), array('*'));
        $rows = $this->_getReadAdapter()->fetchAll($select);

        foreach ($rows as $row) {
            $data[$row['country_id']] = $row['common_settings'];
        }

        return $data;
    }

    /**
     * Retrieve array of countries ids which have regions in system
     *
     * @return array
     */
    public function getCountriesWithRegions()
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('directory/country_region'), 'DISTINCT(country_id)');
        $rows = $this->_getReadAdapter()->fetchCol($select);
        return $rows;
    }
}
