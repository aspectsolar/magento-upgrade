var AW_AjaxCartProUpdaterObject = new AW_AjaxCartProUpdater('sidebar');
Object.extend(AW_AjaxCartProUpdaterObject, {
    updateOnUpdateRequest: true,
    updateOnActionRequest: false,
    
    beforeUpdate: function(html){
        return null;
    },
    
    afterUpdate: function(html, selectors){
        var me = this;
        //call mage function
        if (typeof(truncateOptions) === 'function') {
            truncateOptions();
        }

        selectors.each(function(selector){
            me._effect(selector);
        });
        return null;
    },

    _effect: function(obj) {
        var el = $$(obj)[0];
        if (typeof(el) == 'undefined') {
            return null;
        }
        switch(this.config.cartAnimation) {
            case 'opacity':
                el.hide();
                new Effect.Appear(el);
                break;
            case 'grow':
                el.hide();
                new Effect.BlindDown(el);
                break;
            case 'blink':
                new Effect.Pulsate(el);
                break;
            default:
        }
    }
});
AW_AjaxCartPro.registerUpdater(AW_AjaxCartProUpdaterObject);
delete AW_AjaxCartProUpdaterObject;