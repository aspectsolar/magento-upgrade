
/**
 * Shopping Cart Editor
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aiteditablecart
 * @version      2.1.2
 * @license:     n/a
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
var AitocCart = Class.create();
AitocCart.prototype = {

    initialize: function(productOptionsCssQuery, qtyCssQuery, url)
    {
        this.url = url;
        this.productOptionsCssQuery = productOptionsCssQuery;
        this.qtyCssQuery = qtyCssQuery;
        this.addListeners();
    },
    
    addListeners: function()
    {
        $$(this.productOptionsCssQuery).each(function(element)
        {
            element.select('input', 'select', 'textarea').each(
                function(input)
                {
                    if (input.type.toLowerCase() == 'radio' || input.type.toLowerCase() == 'checkbox') {
                        input.observe('click', this.updatePost.bind(this));
                    } else {
                        element.observe('keydown', this.keyDown.bind(this));
                        input.observe('change', this.updatePost.bind(this));
                    }
                }.bind(this)
            );
        }.bind(this));
        $$(this.qtyCssQuery).each(function(element){
            element.observe('keydown', this.keyDown.bind(this));
            element.observe('change', this.updatePost.bind(this));
        }.bind(this));    
    },
    
    keyDown: function(event)
    {
        if(typeof(keyInterval)!='undefined')
        {
            clearInterval(keyInterval);
        }
        keyInterval = setTimeout(this.updatePost.bind(this),1500);
    },
    
    updatePost: function(event)
    {
        if(typeof(updateIsRunning)!='undefined' && updateIsRunning)
        {
            return;
        }
		var validator = new Validation($('shopping-cart-table').up('form'));
        if (validator && validator.validate()) {
			updateIsRunning = 1;
			setTimeout(function(){
				var form = $('shopping-cart-table').up('form');
				/**
				* multiattribute product with shopping cart editor
				*/
				for (var i=0;i<$(form).elements.length;i++)
				{
					if($(form).elements[i].id.indexOf('attribute')==0 && $(form).elements[i].value == "")
					{updateIsRunning = 0; return ;}
				}
    
				$('loading-mask').show();
				var params = Form.serialize(form);
    
				var request = new Ajax.Request(
					this.url,
					{
						method:'post',
						parameters: params,
						onSuccess: function(e){
							if (e && e.responseText){
								try{
									response = eval('(' + e.responseText + ')');
								}
								catch (error) {
									response = {};
								}
							}
							$('loading-mask').hide();
							$$('.cart').first().replace(response.totals);
							this.addListeners();
							if (typeof(aitCheckout) != 'undefined') {
                                aitCheckout.refresh(['payment', 'review']);
                            }
							updateIsRunning = 0;
						}.bind(this),
						onFailure: function(e){
							$('loading-mask').hide();
							updateIsRunning = 0;
						}
					}
				);        
        
			}.bind(this),100);
		}
	}
}