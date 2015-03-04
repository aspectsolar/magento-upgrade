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

class Aitoc_Aiteditablecart_Block_Rewrite_FrontBundleCheckoutCartItemRenderer  extends Mage_Bundle_Block_Checkout_Cart_Item_Renderer
{
    // aitoc code    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->addRenderer(
            'select',
            'aiteditablecart/bundleCatalogProductViewTypeBundleOptionSelect'
        );
        
        $this->addRenderer(
            'multi',
            'aiteditablecart/bundleCatalogProductViewTypeBundleOptionMulti'
        );
        
        $this->addRenderer(
            'radio',
            'aiteditablecart/bundleCatalogProductViewTypeBundleOptionRadio'
        );
        
        $this->addRenderer(
            'checkbox',
            'aiteditablecart/bundleCatalogProductViewTypeBundleOptionCheckbox'
        );
    }
    
    
    protected $_optionRenderers = array();
    protected $_options = null;
    protected $_parentRendererObject = null;

    public function getDefaultOptions()
    {
        return $this->getParentRendererObject()->getOptions();
    }
        
    public function getDefaultOptionHtml($option)
    {
        return $this->getParentRendererObject()->getOptionHtml($option);
    }    

    protected function getParentRendererObject()
    {
        if (!$this->_parentRendererObject)
        {
            $this->_parentRendererObject = new Aitoc_Aiteditablecart_Block_Rewrite_FrontCheckoutCartItemRenderer();
    
            $this->_parentRendererObject->setItem($this->getItem());
            $this->_parentRendererObject->setLayout($this->getLayout());
            $this->_parentRendererObject->setProduct($this->getProduct());
        }
        
        return $this->_parentRendererObject;
    }
    
    public function getBundleOptions()
    {
// start aitoc code
        $aExtraOptionsData = $this->_getBundleOptionsValues();
// finish aitoc code

        $this->_options = array();
        
        if (!$this->_options) {
            $this->getProduct()->getTypeInstance(true)->setStoreFilter($this->getProduct()->getStoreId(), $this->getProduct());

            $optionCollection = $this->getProduct()->getTypeInstance(true)->getOptionsCollection($this->getProduct());

            $selectionCollection = $this->getProduct()->getTypeInstance(true)->getSelectionsCollection(
                $this->getProduct()->getTypeInstance(true)->getOptionsIds($this->getProduct()),
                $this->getProduct()
            );

            $force = false;
            if (Mage::registry('aiteditablecart_ajax_totals')) {
                //force update selections, because it will only store selected one
                $force = true;
                Mage::unregister('aiteditablecart_ajax_totals');
            }
            $this->_options = $optionCollection->appendSelections($selectionCollection, $force, false);
            
            if ($this->_options)
            {
                foreach ($this->_options as $iKey => $option)
                {
                    if (!empty($aExtraOptionsData[$option->getOptionId()]))
                    {
                        $option->setCartValue($aExtraOptionsData[$option->getOptionId()]['cart_value']);
                        $option->setCartQty($aExtraOptionsData[$option->getOptionId()]['cart_qty']);
                    }
                    else 
                    {
                        $option->setCartValue(array());
                        $option->setCartQty(null);
                    }
                    
                    $option->setItemId($this->getItem()->getId());
                    
                    $this->_options[$iKey] = $option;
                    
                }
            }
        }
        return $this->_options;
    }

    public function _getDefaultOptionHtml($option)
    {
            $renderer = $this->getOptionRender(
                $this->getGroupOfOption($option->getType())
            );
            
            d($renderer, 1);
            if (is_null($renderer['renderer'])) {
                $renderer['renderer'] = $this->getLayout()->createBlock($renderer['block'])
                    ->setTemplate($renderer['template']);
            }
            return $renderer['renderer']
                ->setProduct($this->getProduct())
                ->setOption($option)
                ->toHtml();
            
    }
    
    public function getBundleOptionHtml($option)
    {
        if (!isset($this->_optionRenderers[$option->getType()])) {
            return $this->__('There is no defined renderer for "%s" option type', $option->getType());
        }
        
        if (Mage::registry('current_product'))
        {
            Mage::unregister('current_product');
        }
        Mage::register('current_product', $this->getProduct());        
        
        return $this->getLayout()->createBlock($this->_optionRenderers[$option->getType()])
            ->setOption($option)->toHtml();
    }
    
    
    public function hasOptions()
    {
        $this->getOptions();
        if (empty($this->_options) || !$this->getProduct()->isSalable()) {
            return false;
        }
        return true;
    }

    public function getJsonConfig()
    {
		Mage::app()->getLocale()->getJsPriceFormat();
        $store = Mage::app()->getStore();
#        $optionsArray = $this->getOptions();
        $optionsArray = $this->getBundleOptions();
        $options = array();
        $selected = array();

        foreach ($optionsArray as $_option) {
            if (!$_option->getSelections()) {
                continue;
            }
            $option = array (
                'selections' => array(),
                'title'   => $_option->getTitle(),
                'isMulti' => ($_option->getType() == 'multi' || $_option->getType() == 'checkbox')
            );

            $selectionCount = count($_option->getSelections());

            foreach ($_option->getSelections() as $_selection) {
                $_qty = !($_selection->getSelectionQty()*1)?'1':$_selection->getSelectionQty()*1;
                $selection = array (
                    'qty' => $_qty,
                    'customQty' => $_selection->getSelectionCanChangeQty(),
                    'price' => Mage::helper('core')->currency($_selection->getFinalPrice(), false, false),
                    'priceValue' => Mage::helper('core')->currency($_selection->getSelectionPriceValue(), false, false),
                    'priceType' => $_selection->getSelectionPriceType(),
                    'tierPrice' => $_selection->getTierPrice(),
                    'name' => $_selection->getName(),
                    'plusDisposition' => 0,
                    'minusDisposition' => 0,
                );
        		$responseObject = new Varien_Object();
        		$args = array('response_object'=>$responseObject, 'selection'=>$_selection);
        		Mage::dispatchEvent('bundle_product_view_config', $args);
        		if (is_array($responseObject->getAdditionalOptions())) {
        			foreach ($responseObject->getAdditionalOptions() as $o=>$v) {
        				$selection[$o] = $v;
        			}
        		}
                $option['selections'][$_selection->getSelectionId()] = $selection;				

                $isDefault = $_selection->getIsDefault();
				$isTheOnlyAndRequired = ($selectionCount == 1) && $_option->getRequired();
				$isCartValue = in_array($_selection->getSelectionId(), $_option->getCartValue());

				if (($isDefault || $isTheOnlyAndRequired || $isCartValue) && $_selection->isSalable())
				{
                    $selected[$_option->getId()][] = $_selection->getSelectionId();
                }
            }
            $options[$_option->getId()] = $option;
        }

        $config = array(
            'options' => $options,
            'selected' => $selected,
            'bundleId' => $this->getProduct()->getId(),
            'priceFormat' => Mage::app()->getLocale()->getJsPriceFormat(),
            'basePrice' => Mage::helper('core')->currency($this->getProduct()->getPrice(), false, false),
            'priceType' => $this->getProduct()->getPriceType(),
            'specialPrice' => $this->getProduct()->getSpecialPrice()
        );		

        return Zend_Json::encode($config);
    }

    public function addRenderer($type, $block)
    {
        $this->_optionRenderers[$type] = $block;
    }

    public function ________getOptionHtml($option)
    {
        if (!isset($this->_optionRenderers[$option->getType()])) {
            return $this->__('There is no defined renderer for "%s" option type', $option->getType());
        }
        
        return $this->getLayout()->createBlock($this->_optionRenderers[$option->getType()])
            ->setOption($option)->toHtml();
    }
    
// aitoc code
    protected function _getBundleOptionsValues()
    {
        $aOptionsValues = array();

        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $this->getProduct()->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption =  $this->getItem()->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = unserialize($optionsQuoteItemOption->getValue());
        
        if ($bundleOptionsIds) {
            /**
            * @var Mage_Bundle_Model_Mysql4_Option_Collection
            */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $this->getProduct());

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $this->getItem()->getOptionByCode('bundle_selection_ids');

            $selectionsCollection = $typeInstance->getSelectionsByIds(
                unserialize($selectionsQuoteItemOption->getValue()),
                $this->getProduct()
            );

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $aValues = array();
                    $bundleSelections = $bundleOption->getSelections();
                    foreach ($bundleSelections as $bundleSelection) {
                        $aValues[] = $bundleSelection->getSelectionId();
                    }
                    $aOptionsValues[$bundleOption->getOptionId()]['cart_value'] = $aValues;
                    $aOptionsValues[$bundleOption->getOptionId()]['cart_qty'] = $this->_getSelectionQty($bundleSelection->getSelectionId());
                }
            }
        }
		
        return $aOptionsValues;
    }    

}