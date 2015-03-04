var AW_AjaxCartProObserverObject = new AW_AjaxCartProObserver('clickOnButtonInCartPageForm');
Object.extend(AW_AjaxCartProObserverObject, {

    uiBlocks: ['progress'],

    _stopFns: [],

    run: function() {
        var me = this;
        var forms = this._getForms();
        forms.each(function(form){
            form.select('button[type=submit]').each(function(btn){
                var fn = me._observeFn.bind(btn);
                btn.observe('click', fn);
                me._stopFns.push(function(){
                    btn.stopObserving('click', fn);
                });
            })
        });
    },

    stop: function() {
        this._stopFns.each(function(fn){
            fn();
        });
        this._stopFns = [];
    },

    fireOriginal: function(url, parameters) {
        this.stop();
        if (this.lastClickedBtn) {
            this.lastClickedBtn.click();
        }
    },

    _observeFn: function(event) {
        var me = AW_AjaxCartPro.config.actionsObservers.clickOnButtonInCartPageForm;
        var targetObj = this;
        if (!targetObj) {
            return;
        }

        var action = targetObj.form.readAttribute('action') || '';
        var params = targetObj.form.serialize(true);
        if (typeof($(targetObj).getValue) === 'function') {
            params[targetObj.getAttribute('name')] = $(targetObj).getValue();
        }
        me.lastClickedBtn = targetObj;
        me.fireCustom(action, params);
        event.stop();
    },

    _getForms: function() {
        if (typeof(this.forms) !== 'undefined') {
            return this.forms;
        }

        var me = this;
        this.forms = [];
        $$('form').each(function(form){
            if (form.action.indexOf('checkout/cart/updatePost') !== -1) {
                me.forms.push(form);
            }
        });
        me._stopFns.push(function(){
            delete me.forms;
        });
        return this.forms;
    }
});
AW_AjaxCartPro.registerObserver(AW_AjaxCartProObserverObject);
delete AW_AjaxCartProObserverObject;