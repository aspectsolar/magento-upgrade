var AW_AjaxCartProObserverObject = new AW_AjaxCartProObserver('clickOnAddToCartInMiniWishlist');
Object.extend(AW_AjaxCartProObserverObject, {

    uiBlocks: ['progress', 'options', 'add_confirmation'],

    _stopFns: [],

    run: function() {
        var me = this;
        var links = this._getLinks();
        links.each(function(link){
            var fn = me._observeFn.bind(me);
            link.observe('click', fn);
            me._stopFns.push(function(){
                link.stopObserving('click', fn);
            });
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
        document.location.href = url;
    },

    _observeFn: function(event) {
        var url = $(event.target).getAttribute('href');
        this.fireCustom(url);
        event.stop();
    },

    _getLinks: function() {
        var mageVersion = AW_AjaxCartProConfig.data.mageVersion.split('.');
        if (mageVersion[0] < 2 && mageVersion[1] < 5) {
            return [];
        }

        if (typeof(this.links) !== 'undefined') {
            return this.links;
        }

        var me = this;
        this.links = [];
        $$('a').each(function(l){
            if (l.href.indexOf('wishlist/index/cart') !== -1) {
                me.links.push(l);
            }
        });
        me._stopFns.push(function(){
            delete me.links;
        });
        return this.links;
    }
});
AW_AjaxCartPro.registerObserver(AW_AjaxCartProObserverObject);
delete AW_AjaxCartProObserverObject;