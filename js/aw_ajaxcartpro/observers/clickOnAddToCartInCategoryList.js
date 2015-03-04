var AW_AjaxCartProObserverObject = new AW_AjaxCartProObserver('clickOnAddToCartInCategoryList');
Object.extend(AW_AjaxCartProObserverObject, {

    uiBlocks: ['progress', 'options', 'add_confirmation'],

    _oldSetLocation: null,

    run: function() {
        this._oldSetLocation = setLocation;
        setLocation = this._observeFn.bind(this);
    },

    stop: function() {
        setLocation = this._oldSetLocation;
    },

    fireOriginal: function(url, parameters) {
        this._oldSetLocation(url);
    },

    _observeFn: function(url) {
        var mageVersion = AW_AjaxCartProConfig.data.mageVersion.split('.');
        var is14XAndLess = (mageVersion[0] < 2 && mageVersion[1] < 5);
        var is18X = (mageVersion[0] === "1" && mageVersion[1] === "8");
        if (
            (url.indexOf('.html') !== -1 && url.indexOf('.html?') === -1 && is18X) ||
            (url.indexOf('options=cart') !== -1) ||
            (url.indexOf('checkout/cart/add') !== -1) ||
            ((url.indexOf('wishlist/index/cart') !== -1) && !is14XAndLess)
        ) {
            this.fireCustom(url);
        } else {
            this.fireOriginal(url);
        }
    }
});
AW_AjaxCartPro.registerObserver(AW_AjaxCartProObserverObject);
delete AW_AjaxCartProObserverObject;