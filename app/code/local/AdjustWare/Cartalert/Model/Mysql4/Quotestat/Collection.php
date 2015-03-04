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
class AdjustWare_Cartalert_Model_Mysql4_Quotestat_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/quotestat');
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        foreach ($this->_items as $item) {
            $item->setStatus('Just abandoned');
            if($item->getAlertNumber())
            {
                $item->setStatus('Reminded '.$item->getAlertNumber().' time(s)');
            }
            if($item->getRecoveryDate())
            {
                $item->setStatus('Recovered');
            }
            if($item->getOrderDate())
            {
                $item->setStatus('Ordered');
            }
            $item->setCurrency($currency);
        }
        return $this;
    }    
    
}