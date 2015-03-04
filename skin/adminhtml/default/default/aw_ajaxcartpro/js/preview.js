var AJAXCARTPRO_CONFIRMATIONBLOCK = {
    //contentSelector: null, //must be implemented
    //confirmationSelector: null, //must be implemented
    overlaySelector: '#acp-overlay',

    iframe: null,

    preview: function(url) {
        var me = this;
        this.clear();
        var value = $$(this.contentSelector)[0].getValue();

        me.createIFrame();

        var form = new Element('form');
        form.setAttribute('action', url);
        var input = new Element('textarea');
        input.setAttribute('name', 'textToGenerate');
        input.innerHTML = value;
        form.appendChild(input);
        this.iframe.contentDocument.body.appendChild(form);
        form.submit();


        this.iframe.observe('load', function(event){me.onPreviewLoad(event);});
    },

    createIFrame: function() {
        this.iframe = new Element('iframe');
        this.iframe.setAttribute('scrolling', 'no');
        this.iframe.setAttribute('frameborder', 'no');
        this.iframe.setAttribute('allowtransparency', '');
        this.iframe.setStyle({
            position: 'fixed',
            top: '0px',
            left: '0px',
            width: '100%',
            height: '100%',
            'z-index': '1000',
            display: 'none'
        });
        document.body.appendChild(this.iframe);
    },

    clear: function (){
        if (this.iframe) {
            try {
                this.iframe.remove();
            } catch (e) {
                
            }
            this.iframe = null;
        }
    },

    onPreviewLoad: function (event) {
        this.iframe.contentDocument.body.setStyle({
            background: 'none'
        });
        var overlay = $(this.iframe.contentDocument.body).select(this.overlaySelector)[0];
        var me = this;
        overlay.observe('click', function(){
            me.clear();
        });
        var block = $(this.iframe.contentDocument.body).select(this.confirmationSelector)[0];
        overlay.removeClassName('ajaxcartpro-box-hide');
        block.removeClassName('ajaxcartpro-box-hide');
        this._addHandlersToBlock(block);

        $(this.iframe.contentDocument.body).innerHTML = '';
        $(this.iframe.contentDocument.body).appendChild(overlay);
        $(this.iframe.contentDocument.body).appendChild(block);

        this.iframe.setStyle({
            display: 'block'
        });

        var position = this._collectCenter(block);
        block.setStyle({
            'left': position[0] + 'px',
            'top':  position[1] + 'px'
        });
    },

    _collectCenter: function(el) {
        var x = document.viewport.getWidth()/2 - el.getWidth()/2;
        var y = document.viewport.getHeight()/2 - el.getHeight()/2;
        if (x < 50) {
            x = 50;
            el.setStyle({
                width: (document.viewport.getWidth() - 100) + 'px'
            })
        }
        if (y < 50) {
            y = 50;
            el.setStyle({
                height: (document.viewport.getHeight() - 100) + 'px'
            })
        }
        return [x, y];
    },

    _addHandlersToBlock: function(el) {
        var me = this;
        var buttons = el.select('button, a');
        buttons.each(function(btn){
            btn.observe('click', function(){
                me.clear()
            });
        });
    }
};

//HACK
Event.observe(window, 'load', function(){
    var el = $('ajaxcartpro_confirmation_removeproductconfirmationcontent_inherit');
    if (el) {
        el.click();
        el.click();
    }

    var el = $('ajaxcartpro_confirmation_addproductconfirmationcontent_inherit');
    if (el) {
        el.click();
        el.click();
    }
});