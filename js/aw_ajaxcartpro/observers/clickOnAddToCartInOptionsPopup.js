var AW_AjaxCartProObserverObject = new AW_AjaxCartProObserver('clickOnAddToCartInOptionsPopup');
Object.extend(AW_AjaxCartProObserverObject, {

    uiBlocks: ['progress', 'options', 'add_confirmation'],

    _oldSubmitFn: null,

    run: function() {
        var targetObj = this._getTargetObj();
        if (!targetObj) {
            return null;
        }
        this._oldSubmitFn = targetObj.form.submit;
        targetObj.form.submit = this._observeFn.bind(this);
        return null;
    },

    stop: function() {
        var targetObj = this._getTargetObj();
        if (!targetObj) {
            return;
        }
        targetObj.form.submit = this._oldSubmitFn;
    },

    fireOriginal: function(url, parameters) {
        var targetObj = this._getTargetObj();
        if (!targetObj) {
            return;
        }
        this.stop();
        targetObj.submit();
    },

    _observeFn: function() {
        var targetObj = this._getTargetObj();
        if (!targetObj) {
            return;
        }
        var action = targetObj.form.readAttribute('action') || '';
        var params = targetObj.form.serialize(true);
        this.fireCustom(action, params);
    },

    _getTargetObj: function() {
        var targetObj = false;
        if (typeof(productAddToCartFormAcp) != 'undefined') {
            targetObj = productAddToCartFormAcp;		
        }
        if (!targetObj) {
            return false;
        }
        return targetObj;
    }
});
AW_AjaxCartPro.registerObserver(AW_AjaxCartProObserverObject);
delete AW_AjaxCartProObserverObject;