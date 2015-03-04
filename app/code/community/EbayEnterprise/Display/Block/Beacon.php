<?php
/**
 * Copyright (c) 2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the eBay Enterprise
 * Magento Extensions End User License Agreement
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf
 *
 * @copyright   Copyright (c) 2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf  eBay Enterprise Magento Extensions End User License Agreement
 *
 */

class EbayEnterprise_Display_Block_Beacon extends Mage_Core_Block_Template
{
	protected $_order; // The order

	/**
	 * Get the last order.
	 * @return Mage_Sales_Model_Order
	 */
	protected function _getOrder()
	{
		if (!($this->_order instanceof Mage_Sales_Model_Order)) {
			$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
			if ($orderId) {
				$this->_order = Mage::getModel('sales/order')->load($orderId);
			}
		}
		return $this->_order;
	}
	/**
	 * Get the current customer quote
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote()
	{
		if (!($this->_quote instanceof Mage_Sales_Model_Quote)) {
			$this->_quote = Mage::getSingleton('checkout/session')->getQuote();
		}
		return $this->_quote;
	}
	/**
	 * Load a Quote object from an Order object
	 * @param Mage_Sales_Model_Order $order
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuoteFromOrder($order)
	{
		if (!($this->_quote instanceof Mage_Sales_Model_Quote)) {
			$this->_quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
		}
		return $this->_quote;
	}
	/**
	 * Get the current request scheme
	 * @return string
	 */
	protected function _getRequestScheme()
	{
		return Mage::app()->getRequest()->getScheme();
	}
	/**
	 * Determine which type of page the user is on
	 * default, home, product, checkout thanks
	 *
	 * @return string
	 */
	public function getPageType()
	{
		if ($this->hasData('page_type')) {
			return $this->getData('page_type');
		} else {
			return 'default';
		}
	}
	/**
	 * Get Fetchback beacon params based on the page type
	 */
	protected function _getParamsByPageType()
	{
		$pageType = $this->getPageType();
		switch ($pageType) {
			case 'product':
			return $this->_getProductParams();
			case 'cart':
			return $this->_getCartParams();
			case 'checkout_success':
			return $this->_getCheckoutSuccessParams();
			default:
			return $this->_getDefaultParams();
		}
	}
	/**
	 * Default params for beacon on all pages
	 */
	protected function _getDefaultParams()
	{
		return array(
			'cat' => '',
			'sid' => $this->helper('eems_display/config')->getSiteId(Mage::app()->getStore()->getId()),
			'name' => 'landing',
		);
	}
	/**
	 * Get the params for product page
	 * @return array
	 */
	protected function _getProductParams()
	{
		$currentProductId = (int) $this->getRequest()->getParam('id');
		$mageProduct = Mage::getModel('catalog/product')->load($currentProductId);
		return array_merge($this->_getDefaultParams(), array(
			'name' => 'landing',
			'browse_products' => $mageProduct->getSku(),
		));
	}
	/**
	 * Get the params for cart page
	 * @return array
	 */
	protected function _getCartParams()
	{
		$quote = $this->_getQuote();
		$params = $this->_getDefaultParams();
		if ($quote instanceof Mage_Sales_Model_Quote) {
			$cartedProducts = array_reduce($quote->getAllVisibleItems(), array($this, '_pidListFromCollection'));
			$params = array_merge($params, array(
				'abandon_products' => $cartedProducts,
			));
		}
		return $params;
	}
	/**
	 * Get the params for checkout thanks page
	 * @return array
	 */
	protected function _getCheckoutSuccessParams()
	{
		$params = $this->_getDefaultParams();
		$order = $this->_getOrder();
		if ($order instanceof Mage_Sales_Model_Order) {
			$quote = $this->_getQuoteFromOrder($order);
			$orderTotal = $quote->getSubtotalWithDiscount();
			$purchasedProducts = array_reduce($order->getAllVisibleItems(), array($this, '_pidListFromCollection'));
			$params = array_merge($params, array(
				'name' => 'success',
				'purchase_products' => $purchasedProducts,
				'crv' => $orderTotal,
				'oid' => $order->getIncrementId(),
			));
		}
		return $params;
	}
	/**
	 * array_reduce callback to get comma separated list of product ids
	 * @param string $result
	 * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item
	 * @return string
	 */
	protected function _pidListFromCollection($result, $item)
	{
		$productId = $item->getSku();
		$result .= (empty($result) ? '' : ',') . $productId;
		return $result;
	}
	/**
	 * Get the beacon url.
	 * @return String
	 */
	public function getBeaconUrl()
	{
		$params = $this->_getParamsByPageType();
		$url = $this->_getRequestScheme() . '://pixel.fetchback.com/serve/fb/pdj?' . http_build_query($params);
		return $url;
	}
	/**
	 * Whether or not to display the beacon.
	 */
	public function showBeacon()
	{
		return Mage::helper('eems_display/config')->getIsEnabled(Mage::app()->getStore()->getId());
	}
}
