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

class Aitoc_Aiteditablecart_Block_CatalogProductViewOptionsTypeSelect extends Mage_Catalog_Block_Product_View_Options_Type_Select
{

    public function getValuesHtml()
    {
        $_option = $this->getOption();

// start aitoc code                
        if ($_option->getItemId()) 
        {
            $iCurrentItemId = $_option->getItemId(); 
        }
        else 
        {
            $iCurrentItemId = uniqid('cart_item'); 
        }
        
        $sHiddenField = ' <input type="hidden" name="cart['.$iCurrentItemId.'][cart_product_id]" value="'.$_option->getProductId().'"> '; // aitoc code
        
// finish aitoc code                
        
        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN
            || $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
            $require = ($_option->getIsRequire()) ? ' required-entry' : '';
            $extraParams = '';
            $select = $this->getLayout()->createBlock('core/html_select')
                ->setData(array(
                    'id' => 'select_'.$_option->getId(),
                    'class' => $require.' product-custom-option'
                ));
                
// start aitoc code          

                if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN)
                {
                    $mCurrentValue = $_option->getCartValue();
                }
                else 
                {
                    $mCurrentValue = explode(',', $_option->getCartValue());
                }

                $select->setValue($mCurrentValue);
// finish aitoc code                
                
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
#                $select->setName('options['.$_option->getid().']')
                $select->setName('cart['.$iCurrentItemId.'][cart_options]['.$_option->getid().']') // aitoc code
                    ->addOption('', $this->__('-- Please Select --'));
            } else {
                
#                $select->setName('options['.$_option->getid().'][]');
                $select->setName('cart['.$iCurrentItemId.'][cart_options]['.$_option->getid().'][]'); // aitoc code
                $select->setClass('multiselect'.$require.' product-custom-option');
            }
            foreach ($_option->getValues() as $_value) {
                
#$nPrice = $_value->getPrice(true); // 1.4 fix
                
                $priceStr = $this->_formatPrice(array(
                    'is_percent' => ($_value->getPriceType() == 'percent') ? true : false,
//                    'pricing_value' => $_value->getPrice(true)
//                    'pricing_value' => $this->_getPrice($_value, true)
//                    'pricing_value' => Mage::helper('aiteditablecart')->getOptionPrice($_value, true)
                    'pricing_value' => Mage::helper('aiteditablecart')->getOptionPrice($_value->getOption()->getProduct(), $_value->getPriceType(), $_value->getPrice(), true)
                ), false);
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . $priceStr . ''
                );
            }
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
//            $select->setExtraParams('onchange="opConfig.reloadPrice()"'.$extraParams);
            $select->setExtraParams($extraParams); // aitoc code

            $selectHtml = $select->getHtml();
            
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE)
            {
                $sHiddenEmpty = '<input type="hidden" name="cart['.$iCurrentItemId.'][cart_options]['.$_option->getid().'][]" value="0" />';
                
                $selectHtml  = $sHiddenEmpty . $selectHtml; 
            }
            
//            return $select->getHtml();
            return $selectHtml . $sHiddenField;  // aitoc code
        }

        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO
            || $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
            ) {
//            $selectHtml = '<ul id="options-'.$_option->getId().'-list" class="options-list">';

            $selectHtml = '<ul id="options-'.$_option->getId().'-list" class="options-list" style="list-style-type:none">'; // aitoc code
            $require = ($_option->getIsRequire()) ? ' validate-one-required-by-name' : '';
            $arraySign = '';
            
// start aitoc code          
/*
                if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN)
                {
                    $mCurrentValue = $_option->getCartValue();
                }
                else 
                {
                    $mCurrentValue = explode(',', $_option->getCartValue());
                }
*/                
                    $mCurrentValue = explode(',', $_option->getCartValue());
// finish aitoc code                
            
            
            switch ($_option->getType()) {
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $class = 'radio';
                    if (!$_option->getIsRequire()) {
//                        $selectHtml .= '<li><input type="radio" id="options_'.$_option->getId().'" class="'.$class.' product-custom-option" name="options['.$_option->getId().']" onclick="opConfig.reloadPrice()" value="" checked="checked" /><span class="label"><label for="options_'.$_option->getId().'">' . $this->__('None') . '</label></span></li>';
                        $selectHtml .= '<li><input type="radio" id="options_'.$_option->getId().'" class="'.$class.' product-custom-option" name="cart['.$iCurrentItemId.'][cart_options]['.$_option->getId().']"  value="" checked="checked" /><span class="label"><label for="options_'.$_option->getId().'">' . $this->__('None') . '</label></span></li>';
                    }
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($_option->getValues() as $_value) {
                
                
// start aitoc code          
                if (in_array($_value->getOptionTypeId(), $mCurrentValue))
                {
                    $sChecked = 'checked="checked"';
                }
                else 
                {
                    $sChecked = '';
                }
// finish aitoc code                
                
                $count++;
                
#$nPrice = $_value->getPrice(true); // 1.4 fix
                
                $priceStr = $this->_formatPrice(array(
                    'is_percent' => ($_value->getPriceType() == 'percent') ? true : false,
//                    'pricing_value' => $_value->getPrice(true)
//                    'pricing_value' => $this->_getPrice($_value, true)
                    'pricing_value' => Mage::helper('aiteditablecart')->getOptionPrice($_value->getOption()->getProduct(), $_value->getPriceType(), $_value->getPrice(), true)
                ));
                $selectHtml .= '<li>' .
#                               '<input type="'.$type.'" class="'.$class.' '.$require.' product-custom-option" onclick="opConfig.reloadPrice()" name="options['.$_option->getId().']'.$arraySign.'" id="options_'.$_option->getId().'_'.$count.'" value="'.$_value->getOptionTypeId().'" />' .
                               '<input type="'.$type.'" class="'.$class.' '.$require.' product-custom-option"  name="cart['.$iCurrentItemId.'][cart_options]['.$_option->getid().']'.$arraySign.'" id="options_'.$_option->getId().'_'.$count.'" value="'.$_value->getOptionTypeId().'" '.$sChecked.' />' .
                               '<span class="label"><label for="options_'.$_option->getId().'_'.$count.'">'.$_value->getTitle().' '.$priceStr.'</label></span>';
                if ($_option->getIsRequire()) {
                    $selectHtml .= '<script type="text/javascript">' .
                                    '$(\'options_'.$_option->getId().'_'.$count.'\').advaiceContainer = \'options-'.$_option->getId().'-container\';' .
                                    '$(\'options_'.$_option->getId().'_'.$count.'\').callbackFunction = \'validateOptionsCallback\';' .
                                   '</script>';
                }
                $selectHtml .= '</li>';
            }
            $selectHtml .= '</ul>';
            
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX)
            {
                $sHiddenEmpty = '<input type="hidden" name="cart['.$iCurrentItemId.'][cart_options]['.$_option->getid().'][]" value="0" />';
                
                $selectHtml  = $sHiddenEmpty . $selectHtml; 
            }
            
            $selectHtml .= $sHiddenField; // aitoc code 
            return $selectHtml;
        }
    }
    
    // fix for prices for magento 1.4 
    private function _getPrice($_value, $flag=false)
    {
        if ($flag && $_value->getPriceType() == 'percent') {
//            $basePrice = $this->getOption()->getProduct()->getFinalPrice();

            if ($nSpecialPrice = $_value->getOption()->getProduct()->getSpecialPrice())
            {
                $basePrice = $nSpecialPrice;
            }
            else 
            {
                $basePrice = $_value->getOption()->getProduct()->getPrice();
            }

//            $price = $basePrice*($this->_getData('price')/100);
            $price = $basePrice*($_value->getData('price')/100);
            return $price;
        }
//        return $this->_getData('price');
        return $_value->getData('price');
    }
    
    
    // override parent
    public function getFormatedPrice()
    {
        if ($option = $this->getOption()) {
$nPrice = $option->getPrice(true); // 1.4 fix
            return $this->_formatPrice(array(
                'is_percent' => ($option->getPriceType() == 'percent') ? true : false,
//                'pricing_value' => $option->getPrice(true)
                'pricing_value' => Mage::helper('aiteditablecart')->getOptionPrice($option->getProduct(), $option->getPriceType(), $option->getPrice(), true)
            ));
        }
        return '';
    }
    
}