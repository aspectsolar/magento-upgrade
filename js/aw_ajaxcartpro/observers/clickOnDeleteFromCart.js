var AW_AjaxCartProObserverObject = new AW_AjaxCartProObserver('clickOnDeleteFromCart');
Object.extend(AW_AjaxCartProObserverObject, {

    uiBlocks: ['progress', 'remove_confirmation'],

    _stopFns: [],

    run: function() {
        var me = this;
        var links = this._getLinks();
        links.each(function(link){
            var fn = me._observeFn.bind(link);
            link.observe('click', fn);
            link._onclick = link.onclick;
            link.onclick = function(e){};
            link.removeAttribute('onclick');
            me._stopFns.push(function(){
                link.stopObserving('click', fn);
                link.onclick = link._onclick;
                link._onclick = function(e){};
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
        var me = AW_AjaxCartPro.config.actionsObservers.clickOnDeleteFromCart;
        var link = this;
        if (link._onclick) {
            if (!link._onclick()) {
                event.stop();
                return;
            }
        }
        var url = link.getAttribute('href');
        me.fireCustom(url);
        event.stop();
    },

    _getLinks: function() {
        if (typeof(this.links) !== 'undefined') {
            return this.links;
        }

        var me = this;
        this.links = [];
        $$('a').each(function(l){
            if (l.href.indexOf('checkout/cart/delete') !== -1) {
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