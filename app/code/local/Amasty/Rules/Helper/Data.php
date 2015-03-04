<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Rules
*/
class Amasty_Rules_Helper_Data extends Mage_Core_Helper_Abstract 
{
    const TYPE_CHEAPEST    = 'the_cheapest';
    const TYPE_EXPENCIVE   = 'the_most_expencive';
    const TYPE_FIXED       = 'fixed';
    const TYPE_EACH_N      = 'each_n';
    const TYPE_EACH_N_FIXDISC   = 'each_n_fixdisc';    
    const TYPE_GROUP_N     = 'group_n';
    const TYPE_GROUP_N_DISC     = 'group_n_disc';    
    const TYPE_XY_PERCENT  = 'buy_x_get_y_percent';
    const TYPE_XY_FIXED    = 'buy_x_get_y_fixed';
    const TYPE_XY_FIXDISC  = 'buy_x_get_y_fixdisc';
    const TYPE_AFTER_N_FIXED     = 'after_n_fixed';
    const TYPE_AFTER_N_DISC      = 'after_n_disc';
    const TYPE_AFTER_N_FIXDISC   = 'after_n_fixdisc';
    const TYPE_AMOUNT            = 'money_amount';
    const TYPE_SETOF_PERCENT     = 'setof_percent';      
    const TYPE_SETOF_FIXED       = 'setof_fixed';      
        
    public function getDiscountTypes($asOptions=false)
    {
        $types = array(
            self::TYPE_CHEAPEST        => $this->__('The Cheapest'),
            self::TYPE_EXPENCIVE       => $this->__('The Most Expensive'),
            self::TYPE_EACH_N          => $this->__('Each N-th with Percent Discount'),
            self::TYPE_EACH_N_FIXDISC  => $this->__('Each N-th with Fixed Discount'),    
            self::TYPE_FIXED           => $this->__('Each N-th for Fixed Price'),         
            self::TYPE_GROUP_N         => $this->__('Each Group of N for Fixed Price'),
            self::TYPE_GROUP_N_DISC    => $this->__('Each Group of N with Percent Discount'),                                  
            self::TYPE_XY_PERCENT      => $this->__('Buy X Get Y with Percent Discount'),   
            self::TYPE_XY_FIXDISC      => $this->__('Buy X Get Y with Fixed Discount'),
            self::TYPE_XY_FIXED        => $this->__('Buy X Get Y for Fixed Price'),
            self::TYPE_AFTER_N_DISC    => $this->__('All products after N with Percent Discount'),   
            self::TYPE_AFTER_N_FIXDISC => $this->__('All products after N with Fixed Discount'),   
            self::TYPE_AFTER_N_FIXED   => $this->__('All products after N for Fixed Price'),
            self::TYPE_AMOUNT          => $this->__('Get $Y for each $X spent'),
            self::TYPE_SETOF_PERCENT   => $this->__('Percent discount for product set'), 
            self::TYPE_SETOF_FIXED     => $this->__('Fixed price for product set'), 

        );
        
        if (!$asOptions){
            $values = array();
            foreach ($types as $k=>$v){
                $values[] = array(
                    'value' => $k, 
                    'label' => $v                
                );
            }
            $types = $values;
        }

        return $types;
    }
    
    public function getMembership($created)
    {
        return round((time() - strtotime($created))  /60 / 60 /24);
    }
}