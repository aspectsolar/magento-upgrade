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

class Aitoc_Aiteditablecart_Block_Rewrite_FrontCheckoutCart extends Mage_Checkout_Block_Cart
{

    // override parent
    public function addItemRender($productType, $blockType, $template)
    {
    	/* {#AITOC_COMMENT_END#}
    	$iStoreId = Mage::app()->getStore()->getId();
        $iSiteId  = Mage::app()->getWebsite()->getId();
        
        $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aiteditablecart')->getLicense()->getPerformer();
        $ruler     = $performer->getRuler();
        {#AITOC_COMMENT_START#} */
    	
        if ($template == 'checkout/cart/item/default.phtml')
        {
            $template = 'aitcommonfiles/design--frontend--base--default--template--checkout--cart--item--default.phtml';
            
            /* {#AITOC_COMMENT_END#}
	        if (!($ruler->checkRule('store', $iStoreId, 'store') || $ruler->checkRule('store', $iSiteId, 'website')))
	        {
            	$template = 'checkout/cart/item/default.phtml';
        	}
        	{#AITOC_COMMENT_START#} */
        }
        
        if ($template == 'downloadable/checkout/cart/item/default.phtml')
        {
            $template = 'aitcommonfiles/design--frontend--base--default--template--downloadable--checkout--cart--item--default.phtml';
            
            /* {#AITOC_COMMENT_END#}
	        if (!($ruler->checkRule('store', $iStoreId, 'store') || $ruler->checkRule('store', $iSiteId, 'website')))
	        {
	            $template = 'downloadable/checkout/cart/item/default.phtml';
            }
        	{#AITOC_COMMENT_START#} */
        }
        
        return parent::addItemRender($productType, $blockType, $template);
    }
    
    public function _toHtml()
    {
        $sContent = parent::_toHtml();
        
        $sContent = str_replace('method="post"', 'method="post" enctype="multipart/form-data"', $sContent);
        
        $oBlock = $this->getLayout()->createBlock('aiteditablecart/cartJs');
        
        return $oBlock->_toHtml() . $sContent;
    }
}