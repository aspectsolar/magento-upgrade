<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.3.1
 * @license:     lMnQrjiQcxfPlbdwpFhsKslnzVPfVqO45IEZa4tVHs
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * Cartalert module observer
 *
 * @author Adjustware
 */
class AdjustWare_Cartalert_Model_Cartalert extends Mage_Core_Model_Abstract
{
    /**
     * @var Mage_Core_Model_Email_Template
     */
    protected $_tpl;

    protected $_templateCodes = array('first' => 'catalog/adjcartalert/template', 
                                      'second' => 'catalog/adjcartalert/template2', 
                                      'third' => 'catalog/adjcartalert/template3',
                                      'first_order' => 'catalog/adjcartalert/order_template',
                                      'second_order' => 'catalog/adjcartalert/order_template2', 
                                      'third_order' => 'catalog/adjcartalert/order_template3');

    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/cartalert');
    }
    
    public function generate($now){
        return $this->getResource()->generate($now);
    }

    /**
     * @param Mage_Core_Model_Store $store | null
     */
    public function preprocess($store=null){
        if ($this->getIsPreprocessed())
            return $this;
        $this->setIsPreprocessed(1);
        if (!strpos($this->getProducts(),'##'))
            return $this; // new or custom
    
        if (!$store)
            $store = Mage::app()->getStore($this->getStoreId());

        $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        
        $visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds();
        $status = Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();
        $items = array();
        $prod = explode('##,', substr($this->getProducts(), 0, -2));
        $mediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
        for ($i=0, $n=sizeof($prod); $i<$n; $i+=2){
            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getStoreId())
                ->load($prod[$i]);
            if(in_array($product->getStatus(),$status)&&in_array($product->getVisibility(),$visibility)&&$product->isSaleable())
            {
                $url = $baseUrl . 'catalog/product/view/id/'.$prod[$i];
                $name = $prod[$i+1];
                $imageTag = '';$hasImage = $product->getData('small_image');
                if((isset($hasImage))&&($hasImage!= 'no_selection') && file_exists($mediaPath.$hasImage))
                {
                    $imageTag ='<br><img src="'.Mage::helper('catalog/image')->init($product ,
                    'small_image')->resize(75).'" border="0" />';
                }
                $items[$prod[$i]] = '<a href="'.$url.'">'.$name.$imageTag.'</a>'; //to omit duplicates
            }        
        }

        $this->setProducts(join("<br />\n", $items));
        $this->setIsPreprocessed(1);
        return $this;
    }

    /**
     * @return bool
     */
    public function send(){
        $storeId = $this->getStoreId();
        $store = Mage::app()->getStore($storeId); 
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $oldStore = Mage::app()->getStore();
        Mage::app()->setCurrentStore($store);
        
        $this->preprocess($store);
        
        $history = Mage::getModel('adjcartalert/history');
        $this->_tpl = Mage::getModel('core/email_template');
        
        $history = $this->_send($history, $store);
        
        Mage::app()->setCurrentStore($oldStore);

        $translate->setTranslateInline(true);  
        
        if(strlen($this->getProducts())>0)
        {
            $isSent = $this->_tpl->getSentSuccess();
            if (!$isSent){
                $this->setStatus('invalid')->save();
            }
            else
            {
                Mage::dispatchEvent('adjustware_cartalert_alert_send_after', array('quote'=>$this,'history'=>$history));
            }
            return $isSent;
        }
        else
        {
            return 1;
        }
    }

    /**
     * @param AdjustWare_Cartalert_Model_History $history
     * @return AdjustWare_Cartalert_Model_History
     */
    protected function _send($history)
    {
        $storeId = $this->getStoreId();
        $store = Mage::app()->getStore($storeId); 

        if(strlen($this->getProducts())>0){
            try {
                $history = $this->_setHistoryData($history);
                $url = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

                $couponCode = '';
                if ($this->getFollowUp() == Mage::getStoreConfig('catalog/adjcartalert/coupon_step', $store)) {
                    $couponCode = $this->_createCoupon($store);
                } 
                
                if($couponCode){
                    $history->setCouponCode($couponCode)->save();
                }
                
                $tplVars = array(
                    'website_name' => $store->getWebsite()->getName(),
                    'group_name'   => $store->getGroup()->getName(),
                    'store_name'   => $store->getName(), 
                    'store_url'    => $url,
                    'products'     => $this->getProducts(),
                    'customer_name'=> $this->getCustomerName(),
                    'recover_url'  => $url . 'alerts/recover/cart/id/'.$history->getId().'/code/'.$history->getRecoverCode(),
                    'unsubscribe_url' => $url . 'alerts/unsubscribe/cart/id/'.$history->getId().'/code/'.$history->getRecoverCode(),
                    'real_quote'   => $history->getQuoteId(),
                    'coupon'       => $couponCode,
                    'coupon_days'  => Mage::getStoreConfig('catalog/adjcartalert/coupon_days', $store),
                );
                if(version_compare(Mage::getVersion(), '1.7', '<')){
                    $tplVars['logo_url'] = Mage::getDesign()->getSkinUrl('images/logo_email.gif', array('_area'=>'frontend'));
                    $tplVars['logo_alt'] = '';
                }
                
                $this->_sendTransactional($tplVars, $this->getCustomerEmail());
                $bccEmail = Mage::getStoreConfig('catalog/adjcartalert/bcc');                    
                if($bccEmail){
                    $this->_sendTransactional($tplVars, $bccEmail);
                }

                return $history;
            }
            catch (Exception $e){
                //todo: remove coupon if any
                $history->delete();
                return $history;
            }
        }
    }

    /**
     * @param array $tplVars
     * @param string $toAddress
     */
    protected function _sendTransactional($tplVars, $toAddress)
    {
        $storeId = $this->getStoreId();
        $store = Mage::app()->getStore($storeId);

        $this->_tpl->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
            ->sendTransactional(
                Mage::getStoreConfig($this->_getTemplateCode(), $store),
                Mage::getStoreConfig('catalog/adjcartalert/identity', $store),
                $toAddress,
                $this->getCustomerName(),
                $tplVars
                );
    }

    /**
     * @param AdjustWare_Cartalert_Model_History $history
     * @return AdjustWare_Cartalert_Model_History
     */
    protected function _setHistoryData($history)
    {
        $history->setSentAt(now())
            ->setCustomerName($this->getCustomerName())
            ->setCustomerEmail($this->getCustomerEmail())
            ->setTxt($this->getProducts())
            ->setQuoteId($this->getQuoteId())
            ->setCustomerId($this->getCustomerId())
            ->setRecoverCode(md5(uniqid()))
            ->setFollowUp($this->getFollowUp())
            ->setQuoteIsActive($this->getQuoteIsActive())
            ->save();

        return $history;
    }

    /**
     * @return string
     */
    protected function _getTemplateCode()
    {
        if($this->getQuoteIsActive()){
            return $this->_templateCodes[$this->getFollowUp()];
        }

        return $this->_templateCodes[$this->getFollowUp() . '_order'];
    }

    /**
     * @return string
     */
    public function getCustomerName(){
        if (!$this->getCustomerFname() && !$this->getCustomerFname())
            return Mage::helper('adjcartalert')->__('Friend');
        return $this->getCustomerFname() . ' ' . $this->getCustomerLname();
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    protected function _createCoupon($store)
    {
      	$couponData = array();
        $couponData['name']      = 'Alert #' . $this->getId();
        $couponData['is_active'] = 1;
        $couponData['website_ids'] = array(0 => $store->getWebsiteId());
        $couponData['coupon_code'] = strtoupper($this->getId() . uniqid()); // todo check for uniq in DB
        $couponData['uses_per_coupon'] = 1;
        $couponData['uses_per_customer'] = 1;
        $couponData['from_date'] = ''; //current date

        $days = Mage::getStoreConfig('catalog/adjcartalert/coupon_days', $store);
//        $date = Mage::helper('core')->formatDate(date('Y-m-d', time() + $days*24*3600));
        $date = date('Y-m-d', Mage::getModel('core/date')->timestamp(time() + $days*24*3600));
        $couponData['to_date'] = $date;
        
        $couponData['uses_per_customer'] = 1;
        $couponData['simple_action']   = Mage::getStoreConfig('catalog/adjcartalert/coupon_type', $store);
        $couponData['discount_amount'] = Mage::getStoreConfig('catalog/adjcartalert/coupon_amount', $store);
        $couponData['conditions'] = array(
            1 => array(
                'type'       => 'salesrule/rule_condition_combine',
                'aggregator' => 'all',
                'value'      => 1,
                'new_child'  =>'', 
            )
        );
        
        $couponData['actions'] = array(
            1 => array(
                'type'       => 'salesrule/rule_condition_product_combine',
                'aggregator' => 'all',
                'value'      => 1,
                'new_child'  =>'', 
            )
        );
        
        //create for all customer groups
        $couponData['customer_group_ids'] = array();
        
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load();

        $found = false;
        foreach ($customerGroups as $group) {
            if (0 == $group->getId()) {
                $found = true;
            }
            $couponData['customer_group_ids'][] = $group->getId();
        }
        if (!$found) {
            $couponData['customer_group_ids'][] = 0;
        }

        $couponData['coupon_type'] = 2; // Need to use coupon code - fix for 1.4.1.0
        
        try { 
            $model = Mage::getModel('salesrule/rule');
            $model->loadPost($couponData);
            $model->save();      
        } 
        catch (Exception $e){
            //print_r($e); exit;
            $couponData['coupon_code'] = '';   
        }
        
        return $couponData['coupon_code'];

    }        
}