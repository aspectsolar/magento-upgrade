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
 * Fieldset for configuration fields
 *
 * @category   Eltrino
 * @package    Eltrino_Region
 * @subpackage Adminhtml
 * @copyright  Copyright (c) 2012 Eltrino LLC. (http://www.eltrino.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Eltrino_Region_Block_Adminhtml_System_Config_Fieldset
    extends Mage_Adminhtml_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'eltrino/region/system/config/fieldset.phtml';

    public function _construct()
    {
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setChild('add_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'label' => Mage::helper('eltrino_region')->__('New Region Configuration'),
            'onclick' => "newCountryConfigurationAction(); return false;"
        )));
        return $this;
    }

    /**
     * Prepare containers of saved regions configuration
     *
     * @return array
     */
    public function getCountriesDisabledRegions()
    {
        $containers = array();
        $data = Mage::getSingleton('eltrino_region/entity')->fetchCountriesDisabledRegions();
        foreach ($data as $countryId => $regions) {
            $container = $this->getLayout()->createBlock('eltrino_region/adminhtml_system_config_country')
                ->setCountryId($countryId)
                ->setDisabledRegions($regions);
            $containers[] = $container;
        }
        return $containers; /** array of @see Eltrino_Region_Block_Adminhtml_System_Config_Country objects */
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}
