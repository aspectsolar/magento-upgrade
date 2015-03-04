<?php
/**
 * @copyright   Copyright (c) 2009-11 Amasty
 */
class Amasty_Rules_Block_Adminhtml_Promo_Quote_Edit extends Mage_Adminhtml_Block_Promo_Quote_Edit
{
    public function __construct()
    {
        parent::__construct();
        $percent       = Amasty_Rules_Helper_Data::TYPE_XY_PERCENT;
        $fixed         = Amasty_Rules_Helper_Data::TYPE_XY_FIXED;
        $fixdisc       = Amasty_Rules_Helper_Data::TYPE_XY_FIXDISC;
        $setof_percent = Amasty_Rules_Helper_Data::TYPE_SETOF_PERCENT;
        $setof_fixed   = Amasty_Rules_Helper_Data::TYPE_SETOF_FIXED;
        
        // ampromo - is correct name, it is for comatibility with Auto Add Promo Items
        $this->_formScripts[] = " 
			function ampromo_hide() {
				$('rule_discount_qty').up().up().show();
				$('rule_discount_step').up().up().show();
				$('rule_apply_to_shipping').up().up().show();
				$('rule_actions_fieldset').up().show();
				
				$('rule_promo_sku').up().up().hide();
				$('rule_promo_cats').up().up().hide();
			
				if ('ampromo_cart' == $('rule_simple_action').value) {
				    $('rule_actions_fieldset').up().hide(); 
					$('rule_discount_qty').up().up().hide();
					$('rule_discount_step').up().up().hide();
					
					$('rule_apply_to_shipping').up().up().hide();
					$('rule_promo_sku').up().up().show();
				} 
				if ('ampromo_items' == $('rule_simple_action').value){
					$('rule_apply_to_shipping').up().up().hide();
					$('rule_promo_sku').up().up().show();
				}
				if ('$percent' == $('rule_simple_action').value || '$fixed' == $('rule_simple_action').value || '$fixdisc' == $('rule_simple_action').value || 
                '$setof_percent' == $('rule_simple_action').value || '$setof_fixed' == $('rule_simple_action').value){
					$('rule_promo_sku').up().up().show();
					$('rule_promo_cats').up().up().show();
				}				
			}
			ampromo_hide(); 
        ";
    }
}