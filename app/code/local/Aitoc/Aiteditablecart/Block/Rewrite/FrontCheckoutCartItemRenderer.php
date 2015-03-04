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

class Aitoc_Aiteditablecart_Block_Rewrite_FrontCheckoutCartItemRenderer  extends Mage_Checkout_Block_Cart_Item_Renderer
{
    // aitoc code    
    
    protected $_optionRenders = array();

    public function __construct()
    {
        parent::__construct();
        
        $this->addOptionRenderer(
            'default',
//            'catalog/product_view_options_type_default',
            'aiteditablecart/catalogProductViewOptionsTypeDefault',
            'catalog/product/view/options/type/default.phtml'
        );
        
        $this->addOptionRenderer(
            'text',
//            'catalog/product_view_options_type_text',
            'aiteditablecart/catalogProductViewOptionsTypeText',
            'aiteditablecart/checkout_cart_options_item_text.phtml'
        );
        
        $this->addOptionRenderer(
            'file',
//            'catalog/product_view_options_type_file',
            'aiteditablecart/catalogProductViewOptionsTypeFile',
            'aiteditablecart/checkout_cart_options_item_file.phtml'
        );
        
        $this->addOptionRenderer(
            'select',
            'aiteditablecart/catalogProductViewOptionsTypeSelect',
            'catalog/product/view/options/type/select.phtml'
        );
        
        $this->addOptionRenderer(
            'date',
            'aiteditablecart/catalogProductViewOptionsTypeDate',
            'aiteditablecart/checkout_cart_options_item_date.phtml'
        );
    }

    /**
     * Set product object
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Block_Product_View_Options
     */
    public function setProduct(Mage_Catalog_Model_Product $product = null)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Add option renderer to renderers array
     *
     * @param string $type
     * @param string $block
     * @param string $template
     * @return Mage_Catalog_Block_Product_View_Options
     */
    public function addOptionRenderer($type, $block, $template)
    {
        $this->_optionRenders[$type] = array(
            'block' => $block,
            'template' => $template,
            'renderer' => null
        );
        return $this;
    }

    /**
     * Get option render by given type
     *
     * @param string $type
     * @return array
     */
    public function getOptionRender($type)
    {
        if (isset($this->_optionRenders[$type])) {
            return $this->_optionRenders[$type];
        }

        return $this->_optionRenders['default'];
    }

    public function getGroupOfOption($type)
    {
        $group = Mage::getSingleton('catalog/product_option')->getGroupByType($type);

        return $group == '' ? 'default' : $group;
    }

    /**
     * Get product options
     *
     * @return array
     */
    public function getOptions()
    {
// start aitoc code        
        $aExtraProductOptionsData = $this->getExtraProductOptionsData();
        
        $aUnsortedOptionList = $this->getProduct()->getOptions();

        if (!$aUnsortedOptionList) return false;
        
        $aOptionOrderHash = array();
        $aOptionList = array();
        
        foreach ($aUnsortedOptionList as $iKey => $option) 
        {
            $aOptionOrderHash[$iKey] = $option->getSortOrder();
        }        
        
        asort($aOptionOrderHash);
        
        foreach ($aOptionOrderHash as $iKey => $sVal) 
        {
            $aOptionList[] = $aUnsortedOptionList[$iKey];
        } 
                
        foreach ($aOptionList as $iKey => $option) 
        {
            if (!empty($aExtraProductOptionsData[$option->getOptionId()]))
            {
                $option->setCartValue($aExtraProductOptionsData[$option->getOptionId()]['cart_value']);
                $option->setFormatValue($aExtraProductOptionsData[$option->getOptionId()]['format_value']);
            }
            else 
            {
                $option->setCartValue(null);
                $option->setFormatValue(null);
            }
            
            $option->setItemId($this->getItem()->getId());
            $option->setProduct($this->getProduct()); // magento 1.4 fix
            
            $aOptionList[$iKey] = $option;
        }
        
        return $aOptionList;
// finish aitoc code        
    }

    public function hasOptions()
    {
        if ($this->getOptions()) {
            return true;
        }
        return false;
    }

    public function getJsonConfig()
    {
        $config = array();

        $aOptions = $this->getOptions();
        
        if ($aOptions)
        {
            foreach ($this->getOptions() as $option) {
                /* @var $option Mage_Catalog_Model_Product_Option */
                $priceValue = 0;
                if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                    $_tmpPriceValues = array();
                    foreach ($option->getValues() as $value) {
                        /* @var $value Mage_Catalog_Model_Product_Option_Value */
                	   $_tmpPriceValues[$value->getId()] = Mage::helper('core')->currency($value->getPrice(true), false, false);
                    }
                    $priceValue = $_tmpPriceValues;
                } else {
                    $priceValue = Mage::helper('core')->currency($option->getPrice(), false, false);
                }
                $config[$option->getId()] = $priceValue;
            }
        }

        return Zend_Json::encode($config);
    }

    
    public function getOptionHtml($option)
    {
        $renderer = $this->getOptionRender(
            $this->getGroupOfOption($option->getType())
        );
        if (is_null($renderer['renderer'])) {
            $renderer['renderer'] = $this->getLayout()->createBlock($renderer['block'])
                ->setTemplate($renderer['template']);
        }
        return $renderer['renderer']
            ->setProduct($this->getProduct())
            ->setOption($option)
            ->toHtml();
            
    }
    
    // aitoc func
    public function getProductType()
    {
        return $this->getProduct()->getTypeId();
    }
    
    public function getExtraProductOptionsData()
    {
        $options = array();
        if ($optionIds = $this->getItem()->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $this->getProduct()->getOptionById($optionId)) {
                    
                    $quoteItemOption = $this->getItem()->getOptionByCode('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setQuoteItemOption($quoteItemOption);
                        
                    $options[$option->getId()] = array(
                        'cart_value' => $quoteItemOption->getValue(), //aitoc code
                        'format_value' => $group->getFormattedOptionValue($quoteItemOption->getValue()),
                    );
                }
            }
        }
        return $options;
    }
    
    
    // for config    
    
}