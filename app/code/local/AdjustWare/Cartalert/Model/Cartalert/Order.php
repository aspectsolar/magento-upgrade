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
class AdjustWare_Cartalert_Model_Cartalert_Order extends AdjustWare_Cartalert_Model_Cartalert
{
    const CARTALERT_INSTANCE_TYPE = 'order';

    public function getResource()
    {
        return Mage::getResourceSingleton('adjcartalert/cartalert_order');
    }
}