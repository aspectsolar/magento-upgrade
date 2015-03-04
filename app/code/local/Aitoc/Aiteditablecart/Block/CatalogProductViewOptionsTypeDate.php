<?php
/**
 * Shopping Cart Editor
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aiteditablecart
 * @version      2.1.2
 * @license:     sJyHIXeWlUU1XYytqPDfVrCdIMO1jSQ6obarjJbRep
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aiteditablecart_Block_CatalogProductViewOptionsTypeDate extends Mage_Catalog_Block_Product_View_Options_Type_Date
{

    /**
     * Fill date and time options with leading zeros or not
     *
     * @var boolean
     */
    protected $_fillLeadingZeros = true;

    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setCanLoadCalendarJs(true);
        }
        return parent::_prepareLayout();
    }

    /**
     * Use JS calendar settings
     *
     * @return boolean
     */
    public function useCalendar()
    {
        return Mage::getSingleton('catalog/product_option_type_date')->useCalendar();
    }
    
    // override parent    
    public function setOption(Mage_Catalog_Model_Product_Option $option)
    {
// start aitoc code                

        $oResult = parent::setOption($option);
        if ($this->getOption()->getItemId()) 
        {
            $this->iCurrentItemId = $this->getOption()->getItemId(); 
        }
        else 
        {
            $this->iCurrentItemId = uniqid('cart_item'); 
        }
        

        return $oResult;
// finish aitoc code   
    }
    
    
    /**
     * Date input
     *
     * @return string Formatted Html
     */
    public function getDateHtml()
    {
// start aitoc code                
        $sHiddenField = ' <input type="hidden" name="cart['.$this->iCurrentItemId.'][cart_product_id]" value="'.$this->getOption()->getProductId().'"> '; // aitoc code
        
// finish aitoc code   
        
        
        if ($this->useCalendar()) {
#            return $this->getCalendarDateHtml();
            return $this->getCalendarDateHtml() . $sHiddenField;
        } else {
#            return $this->getDropDownsDateHtml();
            return $this->getDropDownsDateHtml() . $sHiddenField;
        }
    }

    /**
     * JS Calendar html
     *
     * @return string Formatted Html
     */
    public function getCalendarDateHtml()
    {
        $sFieldValue = $this->getOption()->getCartValue();
        
        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        
        if ($sFieldValue)
        {
            $oDate = new Zend_Date((int)strtotime($sFieldValue));
            $sDateValue = $oDate->toString($dateFormatIso);
        }
        else 
        {
            $sDateValue = '';
        }
        
        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $calendar = $this->getLayout()
            ->createBlock('core/html_date')
//            ->setId('options_'.$this->getOption()->getId().'_date')
            ->setId('options_'.$this->getOption()->getId().'_' . $this->iCurrentItemId . '_date') // 1.4 fix
#            ->setName('options['.$this->getOption()->getId().'][date]')
            ->setName('cart['.$this->iCurrentItemId.'][cart_options]['.$this->getOption()->getid().'][date]') // aitoc code            ->setClass('product-custom-option datetime-picker input-text' . $require)
            ->setImage($this->getSkinUrl('images/calendar.gif'))
///            ->setExtraParams('onchange="opConfig.reloadPrice()"') // magento 1.4 fix
            ->setFormat(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT))
            ->setValue($sDateValue); // fix for 1.4

        return $calendar->getHtml();
    }

    /**
     * Date (dd/mm/yyyy) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getDropDownsDateHtml()
    {
// start aitoc code                
        
        if ($sValue = $this->getOption()->getCartValue())
        {
            $iMkTime = strtotime($sValue);
            
            $iYears     = date('Y',$iMkTime);
            $iMonths    = date('n',$iMkTime);
            $iDays      = date('j',$iMkTime);
            
        }
        else 
        {
            $iYears     = 0;
            $iMonths    = 0;
            $iDays      = 0;
        }

// finish aitoc code          
        
        $_fieldsSeparator = '&nbsp;';
        $_fieldsOrder = Mage::getSingleton('catalog/product_option_type_date')->getConfigData('date_fields_order');
        $_fieldsOrder = str_replace(',', $_fieldsSeparator, $_fieldsOrder);

#        $monthsHtml = $this->_getSelectFromToHtml('month', 1, 12);
        $monthsHtml = $this->_getSelectFromToHtml('month', 1, 12, $iMonths); // aitoc code
#        $daysHtml = $this->_getSelectFromToHtml('day', 1, 31);
        $daysHtml = $this->_getSelectFromToHtml('day', 1, 31, $iDays); // aitoc code

        $_yearStart = Mage::getSingleton('catalog/product_option_type_date')->getYearStart();
        $_yearEnd = Mage::getSingleton('catalog/product_option_type_date')->getYearEnd();
#        $yearsHtml = $this->_getSelectFromToHtml('year', $_yearStart, $_yearEnd);
        $yearsHtml = $this->_getSelectFromToHtml('year', $_yearStart, $_yearEnd, $iYears); // aitoc code

        $_translations = array(
            'd' => $daysHtml,
            'm' => $monthsHtml,
            'y' => $yearsHtml
        );
        return strtr($_fieldsOrder, $_translations);
    }

    /**
     * Time (hh:mm am/pm) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getTimeHtml()
    {
// start aitoc code                
        $sHiddenField = ' <input type="hidden" name="cart['.$this->iCurrentItemId.'][cart_product_id]" value="'.$this->getOption()->getProductId().'"> '; // aitoc code
        
        if ($sValue = $this->getOption()->getCartValue())
        {
            $iMkTime = strtotime($sValue);
            
            if (Mage::getSingleton('catalog/product_option_type_date')->is24hTimeFormat()) 
            {
                $iHours     = date('G',$iMkTime);
            }
            else 
            {
                $iHours     = date('g',$iMkTime);
            }
            
            $iDayPart   = date('a',$iMkTime);
            $iMins      = date('i',$iMkTime);
            
        }
        else 
        {
            $iDayPart = 'am';
            $iHours = null;
            $iMins = null;
        }

// finish aitoc code   
        
        if (Mage::getSingleton('catalog/product_option_type_date')->is24hTimeFormat()) {
            $hourStart = 0;
            $hourEnd = 23;
            $dayPartHtml = '';
        } else {
            $hourStart = 1;
            $hourEnd = 12;
#            $dayPartHtml = $this->_getHtmlSelect('day_part')
            $dayPartHtml = $this->_getHtmlSelect('day_part', $iDayPart) // aitoc code
                ->setOptions(array(
                    'am' => Mage::helper('catalog')->__('AM'),
                    'pm' => Mage::helper('catalog')->__('PM')
                ))
                ->getHtml();
        }
#        $hoursHtml = $this->_getSelectFromToHtml('hour', $hourStart, $hourEnd);
        $hoursHtml = $this->_getSelectFromToHtml('hour', $hourStart, $hourEnd, $iHours); // aitoc code
#        $minutesHtml = $this->_getSelectFromToHtml('minute', 0, 59);
        $minutesHtml = $this->_getSelectFromToHtml('minute', 0, 59, $iMins); // aitoc code

#        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml;
        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml . $sHiddenField; // aitoc code
    }

    /**
     * Return drop-down html with range of values
     *
     * @param string $name Id/name of html select element
     * @param int $from  Start position
     * @param int $to    End position
     * @param int $value Value selected
     * @return string Formatted Html
     */
    protected function _getSelectFromToHtml($name, $from, $to, $value = null)
    {
        $options = array(
            array('value' => '', 'label' => '-')
        );
        for ($i = $from; $i <= $to; $i++) {
            $options[] = array('value' => $i, 'label' => $this->_getValueWithLeadingZeros($i));
        }
        return $this->_getHtmlSelect($name, $value)
            ->setOptions($options)
            ->getHtml();
    }

    /**
     * HTML select element
     *
     * @param string $name Id/name of html select element
     * @return Mage_Core_Block_Html_Select
     */
    protected function _getHtmlSelect($name, $value = null)
    {
        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $select = $this->getLayout()->createBlock('core/html_select')
//            ->setId('options_' . $this->getOption()->getId() . '_' . $name) // 1.4 fix
            ->setId('options_' . $this->getOption()->getId() . '_' . $this->iCurrentItemId . '_' . $name)
            ->setClass('product-custom-option datetime-picker' . $require)
//            ->setExtraParams('style="width:auto;" onchange="opConfig.reloadPrice()"')
            ->setExtraParams('style="width:auto;"') // magento 1.4 fix
#            ->setName('options[' . $this->getOption()->getId() . '][' . $name . ']');
            ->setName('cart['.$this->iCurrentItemId.'][cart_options]['.$this->getOption()->getid().'][' . $name . ']'); // aitoc code
        if (!is_null($value)) {
            $select->setValue($value);
        }
        return $select;
    }

    /**
     * Add Leading Zeros to number less than 10
     *
     * @param int
     * @return string
     */
    protected function _getValueWithLeadingZeros($value)
    {
        if (!$this->_fillLeadingZeros) {
            return $value;
        }
        return $value < 10 ? '0'.$value : $value;
    }
    
    // override parent
    public function getFormatedPrice()
    {
        if ($option = $this->getOption()) {
            return $this->_formatPrice(array(
                'is_percent' => ($option->getPriceType() == 'percent') ? true : false,
//                'pricing_value' => $option->getPrice(true)
                'pricing_value' => Mage::helper('aiteditablecart')->getOptionPrice($option->getProduct(), $option->getPriceType(), $option->getPrice(), true)
            ));
        }
        return '';
    }
    
}