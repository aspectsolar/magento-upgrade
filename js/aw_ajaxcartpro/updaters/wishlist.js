var AW_AjaxCartProUpdaterObject = new AW_AjaxCartProUpdater('wishlist', ['.my-account']);
Object.extend(AW_AjaxCartProUpdaterObject, {
    updateOnUpdateRequest: true,
    updateOnActionRequest: false,

    beforeUpdate: function(html){
        return null;
    },

    update: function(html) {
        this.beforeUpdate(html);
        var selector = this.selectors[0];
        $$(selector)[0].innerHTML = html;
        this._evalScripts(html);
        this._evalEnterpriseWishlistScripts();
        this.afterUpdate(html, this.selectors);
        return true;
    },

    afterUpdate: function(html, selectors){
        return null;
    },

    _evalEnterpriseWishlistScripts: function() {
        if (!Enterprise || !Enterprise.Wishlist) {
            return null;
        }
        //copy-paste from skin/../js/scripts.js for compatibility with Magento EE Multiple Wishlists
        $('wishlist-view-form').select('.split-button').each(function(node) {
            if (!$(node).hasClassName('split-button-created')) {
                new Enterprise.Widget.SplitButton(node);
            }
        });
        //copy-paste from skin/../js/enterprise/wishlist.js for compatibility with Magento EE Multiple Wishlists
        $$('#wishlist-table div.description').each(function(el) { Enterprise.textOverflow(el); });
        return null;
    }
});
AW_AjaxCartPro.registerUpdater(AW_AjaxCartProUpdaterObject);
delete AW_AjaxCartProUpdaterObject;