var AW_AjaxCartProConfig = {
    actionsObservers: {},
    targetsToUpdate:  {},
    data: {},
    
    addUpdater: function(updater) {
        this.targetsToUpdate[updater.name] = updater;
    },
    addObserver: function(observer) {
        this.actionsObservers[observer.name] = observer;
    },
    addSystemData: function(data) {
        if (typeof(data.custom) != 'undefined') {
            this.addUpdaterData(data.custom);
        }
        Object.extend(this.data, data);
    },
    addUpdaterData: function(customData) {
        for (var item in customData) {
            if (this.targetsToUpdate[item]) {
                this.targetsToUpdate[item].selectors = customData[item].selectors || null;
                this.targetsToUpdate[item].parentSelector = customData[item].parentSelector || null;
            }
        }
    }
};

var AW_AjaxCartPro = {

    config: AW_AjaxCartProConfig,

    init: function(config) {
        this.connector = AW_AjaxCartProConnector;
        this.ui = AW_AjaxCartProUI;

        this.config.addSystemData(config);
        this.startObservers();
    },

    registerUpdater: function(updater) {
        this.config.addUpdater(updater);
    },

    registerObserver: function(observer) {
        this.config.addObserver(observer);
    },

    startObservers: function(name) {
        var me = this;
        if (Object.isString(name)) {
            var observer = this.config.actionsObservers[name];
            if (observer) {
                observer.run();
            }
        } else {
            if (typeof(this.config.actionsObservers) == 'object') {
                Object.keys(this.config.actionsObservers).each(function(k){
                    me.config.actionsObservers[k].run();
                });
            }
        }
    },

    stopObservers: function(name) {
        var me = this;
        if (Object.isString(name)) {
            var observer = this.config.actionsObservers[name];
            if (observer) {
                observer.stop();
            }
        } else {
            if (typeof(this.config.actionsObservers) == 'object') {
                Object.keys(this.config.actionsObservers).each(function(k){
                    me.config.actionsObservers[k].stop();
                });
            }
        }
    },

    callUpdaters: function(blocks) {
        var me = this;
        var blocks = blocks || {};
        var isSuccessUpdate = true;

        this.ui.beforeUpdate(me.msg);
        Object.keys(blocks).each(function(k){
            if (!isSuccessUpdate || blocks[k] === null) {
                return;
            }
            var result = true;
            try {
                result = me.config.targetsToUpdate[k].update(blocks[k]);
            } catch(e) {
                result = false;
                if (window.console) {
                    console.log(e.name);
                }
            }
            isSuccessUpdate = isSuccessUpdate && result;
        });
        this.ui.afterUpdate(me.msg);
        delete me.msg;
        return isSuccessUpdate;
    },

    fire: function(url, parameters, observer) {
        var me = this;
        this.ui.observer = observer;
        this.ui.beforeFire();

        var parameters = parameters || {};
        parameters['block[]'] = [];
        Object.keys(me.config.targetsToUpdate).each(function(k){
            if (me.config.targetsToUpdate[k].updateOnActionRequest) {
                parameters['block[]'].push(k);
            }
        });
        this.connector.sendRequest(url, parameters,
            function(response) {
                var isResponseHasBlock = Object.values(response.block).without(null).length > 0;
                if (isResponseHasBlock) {
                    var isSuccessUpdate = me.callUpdaters(response.block);
                    if (!isSuccessUpdate) {
                        document.location.reload();
                        return;
                    }
                    me.stopObservers();
                    me.startObservers();
                    return;
                }
                if (response.redirect_to) {
                    me.msg = response.msg;
                    me.fire(response.redirect_to, parameters, observer);
                    return;
                }
                me.update(function(json){
                    document.location.reload();
                }, {
                    actionData: Object.toJSON(response.action_data)
                });
            },
            function(json){
                observer.fireOriginal(url, parameters);
            }
        );
    },

    update: function(failureFn, additionalParams) {
        var me = this;
        failureFn = failureFn || function(){};

        var url = document.location.pathname + document.location.search;
        var parameters = additionalParams;
        parameters['block[]'] = [];
        Object.keys(me.config.targetsToUpdate).each(function(k){
            if (me.config.targetsToUpdate[k].updateOnUpdateRequest) {
                parameters['block[]'].push(k);
            }
        });
        this.connector.sendRequest(url, parameters,
            function(json) {
                var isSuccessUpdate = me.callUpdaters(json.block);
                if (!isSuccessUpdate) {
                    failureFn(json);
                    return;
                }
                me.stopObservers();
                me.startObservers();
                me.ui.afterFire(parameters);
            },
            failureFn
        );
    }
};

var AW_AjaxCartProConnector = {

    defaultParameters: {
        awacp: 1,
        no_cache: 1 //parameter is required for Magento EE FPC compatibility
    },

    sendRequest: function(url, parameters, success, failure) {
        var me = this;
        var parameters = parameters || {};
        var failure = failure || function(){};
        var success = success || function(){};

        url = url.replace('https://','http://');
        if (window.location.protocol == 'https'){
            url = url.replace('http://','https://');
        }

        Object.extend(parameters, this.defaultParameters);
        var options = {
            parameters : parameters,
            method: 'get', //method must be GET for implement 'no_cache' parameter
            onSuccess: this.onSuccessFn(success, failure),
            onFailure: this.onFailureFn(success, failure)
        };
        //cross domain request possible fix
        url = url.replace(/http[^:]*:/, document.location.protocol);
        new Ajax.Request(url, options);
    },

    onSuccessFn: function(success, failure) {
        return function(transport) {
            try {
                eval("var json = " + transport.responseText + " || {}");
            } catch(e) {
                failure({});
                return;
            }
            if (!json.success) {
                failure(json);
                return;
            }
            success(json);
        };
    },

    onFailureFn: function(success, failure) {
        return function(transport) {
            failure(json);
        };
    }
};

var AW_AjaxCartProUI = {

    observer: null,
    blocks: {},

    config: AW_AjaxCartProConfig.data,

    hideCls: 'ajaxcartpro-box-hide',
    showCls: 'ajaxcartpro-box-show',

    overlayCssSelector: '#acp-overlay',

    beforeFire: function() {
        return this._call('beforeFire', arguments);
    },

    afterFire: function() {
        return this._call('afterFire', arguments);
    },

    beforeUpdate: function(msg) {
        return this._call('beforeUpdate', arguments);
    },

    afterUpdate: function() {
        return this._call('afterUpdate', arguments);
    },

    registerBlock: function(block) {
        var block = block || {};
        if (!block.name) {
            return;
        }
        this.blocks[block.name] = block;
    },

    showBlock: function(el) {
        el = this._initEl(el);
        if (!el) {
            return false;
        }
        this._resizeBlock(el);
        var me = this;
        el.onWindowResizeHandler = function(e) {
            me._resizeBlock(el);
        };
        Event.observe(window, 'resize', el.onWindowResizeHandler);
        Event.observe(window, 'scroll', el.onWindowResizeHandler);
        this._show(el);
        var overlay = $$(this.overlayCssSelector)[0];
        overlay.observe('click', this._clickOnOverlay.bind(this));
        this._show(overlay);
        return true;
    },

    hideBlock: function(el) {
        el = this._initEl(el);
        if (!el) {
            return false;
        }

        el.setStyle({
            height: 'auto', width: 'auto'
        });

        var me = this;
        var activeBlocks = 0;
        Object.keys(me.blocks).each(function(blockName){
            var block = me.blocks[blockName];
            if (block.enabled === true) {
                activeBlocks++;
            }
        });
        if (activeBlocks === 0) {
            var overlay = $$(this.overlayCssSelector)[0];
            overlay.stopObserving('click', this._clickOnOverlay.bind(this));
            this._hide(overlay);
        }
        this._hide(el);
        if (typeof(el.onWindowResizeHandler) === 'function') {
            Event.stopObserving(window, 'resize', el.onWindowResizeHandler);
            Event.stopObserving(window, 'scroll', el.onWindowResizeHandler);
            delete el.onWindowResizeHandler;
        }
        return true;
    },

    _clickOnOverlay: function(e) {
        $$('.aw-acp-continue').each(function(el){
            if (!el.click) {
                //safary for windows compatibility
                if ( document.createEvent ) {
                    var evt = document.createEvent('MouseEvents');
                    evt.initEvent('click', true, false);
                    el.dispatchEvent(evt);
                } else if( document.createEventObject ) {
                    el.fireEvent('onclick') ;
                } else if (typeof node.onclick == 'function' ) {
                    el.onclick();
                }
            } else {
                el.click();
            }
        });
    },

    _call: function(fnName, args) {
        var me = this;
        Object.keys(me.blocks).each(function(blockName){
            if (me.observer.uiBlocks.indexOf(blockName) === -1) {
                return;
            }
            var block = me.blocks[blockName];
            if (typeof(block[fnName]) == 'function') {
                block[fnName](args);
            }
        });

        var isSuccessCall = true;
        Object.keys(me.blocks).each(function(blockName){
            if (!isSuccessCall) {
                return;
            }
            var block = me.blocks[blockName];
            if (block.enabled === true) {
                isSuccessCall = isSuccessCall && me.showBlock(block.cssSelector);
            } else {
                isSuccessCall = isSuccessCall && me.hideBlock(block.cssSelector);
            }
        });
        return isSuccessCall;
    },

    _show: function(el) {
        el.removeClassName(this.hideCls);
        el.addClassName(this.showCls);
    },

    _hide: function(el) {
        el.removeClassName(this.showCls);
        el.addClassName(this.hideCls);
    },

    _initEl: function(el) {
        if (Object.isString(el)) {
            el = $$(el);
            if (el.length > 0) {
                el = el[0];
            } else {
                return false;
            }
        }
        el = $(el);
        if (!el) {
            return false;
        }
        return el;
    },

    _collectPos: function(el, horPos, verPos) {
        var x, y;
        var elWidth = el.getWidth();
        var docWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

        switch(horPos) {
            case 'center':
                x = docWidth/2 - elWidth/2;
                break;
            case 'left':
                x = 50;
                break;
            case 'right':
                x = docWidth - elWidth;
                break;
            default:
                //error
        }

        var elHeight = el.getHeight();
        var docHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

        switch(verPos) {
            case 'top':
                y = 50;
                break;
            case 'center':
                y = docHeight/2 - elHeight/2;
                break;
            case 'bottom':
                y = docHeight - elHeight;
                break;
            default:
                //error
        }
        return [x, y];
    },

    _resizeBlock: function(el) {
        el.setStyle({
            height: 'auto', width: 'auto'
        });
        var docWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        var docHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        var xy = this._collectPos(el, 'center', this.config.dialogsVAlign);
        if (xy[0] < 50) {
            xy[0] = 50;
            el.setStyle({
                width: (docWidth - 100) + 'px'
            });
        }
        if (xy[1] < 50) {
            xy[1] = 50;
            el.setStyle({
                height: (docHeight - 100) + 'px'
            });
        }

        var left = xy[0];
        var top = xy[1];
        var isIOS = ( navigator.userAgent.match(/Android|BlackBerry|Tablet|Mobile|iPhone|iPad|iPod|Opera Mini/g) ? true : false );
        if (isIOS) {
            el.setStyle({'position': 'absolute'});
            left += window.pageXOffset?window.pageXOffset:0;
            top += window.pageYOffset?window.pageYOffset:0;
        }
        el.setStyle({
            'left': left + 'px',
            'top': top + 'px'
        });
    }
};


var AW_AjaxCartProObserver = Class.create();
AW_AjaxCartProObserver.prototype = {
    name: null,

    uiBlocks: [],

    initialize: function(name) {
        this.name = name;
    },

    run: function() {
        return null;
    },

    stop: function() {
        return null;
    },

    fireOriginal: function(url, parameters) {
        document.location.href = url;
        return null;
    },

    fireCustom: function(url, parameters) {
        var parameters = parameters || {};
        AW_AjaxCartPro.fire(url, parameters, this);
    }
};

var AW_AjaxCartProUpdater = Class.create();
AW_AjaxCartProUpdater.prototype = {
    config: AW_AjaxCartProConfig.data,
    selectors: null,
    parentSelector: null,
    name: null,

    initialize: function(name, selectors, parentSelector) {
        this.name = name;
        this.selectors = selectors || null;
        this.parentSelector = parentSelector || null;
    },

    beforeUpdate: function(html) {
        return null;
    },

    afterUpdate: function(html) {
        return null;
    },

    update: function(html) {
        this.beforeUpdate(html);

        var me = this;
        var selectors = this.selectors;
        if (selectors === null) {
            selectors = this._getRootSelectors(html);
        }
        var storage = new Element('div');
        storage.innerHTML = html;
        if (storage.childElements().length != selectors.length && storage.childElements().length > 0) {
            return false;
        }
        if (!this._checkSelectorsOnUnique(selectors)) {
            return false;
        }

        selectors.each(function(cssSelector){
            var part = storage.select(cssSelector)[0];
            var target = null;
            me._getSelectorsToTarget(cssSelector).each(function(selector){
                if (target !== null) {
                    return;
                }
                if ($$(selector).length > 0) {
                    target = $$(selector)[0];
                }
            });
            if (!target) {
                return;
            }
            if (!part) {
                target.parentNode.removeChild(target);
                return;
            }
            target.parentNode.replaceChild(part, target);
        });
        delete storage;

        this._evalScripts(html);

        this.afterUpdate(html, selectors);
        return true;
    },

    _getRootSelectors: function(html) {
        var div = new Element('div');
        div.innerHTML = html;
        var selectors = [];
        div.childElements().each(function(el){
            selectors.push(this._getCssSelectorsByElement(el));
        }, this);
        delete div;
        return selectors;
    },

    _checkSelectorsOnUnique: function(selectors) {
        var isUnique = true;
        selectors.each(function(cssSelector){
            var possibleSelectors = this._getSelectorsToTarget(cssSelector);
            var selectorToCheck = null;
            possibleSelectors.each(function(selector){
                if (selectorToCheck !== null) {
                    return;
                }
                if ($$(selector) > 0) {
                    selectorToCheck = $$(selector)[0];
                }
            });
            if ($$(selectorToCheck).length > 1) {
                isUnique = false;
            }
        }, this);
        return isUnique;
    },

    _getSelectorsToTarget: function(selector) {
        var selectors = [];
        if (this.parentSelector !== null) {
            this.parentSelector.each(function(parent){
                var selectorToTarget = parent + ' ' + selector;
                selectors.push(selectorToTarget);
            });
        } else {
            selectors.push(selector);
        }
        return selectors;
    },

    _getCssSelectorsByElement: function(element) {
        element = $(element);
        var cssSelector = element.tagName.toLowerCase();
        $H({'id': 'id', 'className': 'class'}).each(function(pair) {
            var property = pair.first(),
            attribute = pair.last(),
            value = (element[property] || '').toString();
            if (value) {
                if (attribute === 'id') {
                    cssSelector += '#' + value;
                } else {
                    value = value.split(' ').join('.');
                    cssSelector += '.' + value;
                }
            }
        });
        return cssSelector;
    },

    _evalScripts: function(html) {
        var scripts = html.extractScripts();
        scripts.each(function(script){
            try {
                //FIX CDATA comment
                script = script.replace('//<![CDATA[', '').replace('//]]>', '');
                script = script.replace('/*<![CDATA[*/', '').replace('/*]]>*/', '');
                eval(script.replace(/var /gi, ""));
            } catch(e){
                if(window.console) {
                    console.log(e.message);
                }
            }
        });
    }
};



var AW_AjaxCartProUIBlocks = [
    {
        cssSelector: '#ajaxcartpro-progress',
        name: 'progress',
        enabled: false,
        beforeFire: function(args){
            //hide messages blocks
            $$('.messages').each(function(el){
                el.hide();
            });
            if (!AW_AjaxCartPro.config.data.useProgress) {
                return;
            }
            this.enabled = true;
        },
        afterFire: function(args){},
        beforeUpdate: function(args){},
        afterUpdate: function(args){
            this.enabled = false;
        }
    },
    {
        name: 'add_confirmation',
        cssSelector: '#ajaxcartpro-add-confirm',
        enabled: false,
        beforeFire: function(args){
            this.enabled = false;
        },
        afterFire: function(args){
            eval("var actionDataAsJson = " + args[0].actionData);
            actionDataAsJson = actionDataAsJson || {};
            var isConfirmationEnabled = AW_AjaxCartPro.config.data.addProductConfirmationEnabled;
            if ("confirmation_enabled" in actionDataAsJson) {
                isConfirmationEnabled = actionDataAsJson.confirmation_enabled;
            }
            if (!isConfirmationEnabled) {
                return;
            }
            this.enabled = true;
            this._cntBtnInit(actionDataAsJson);
			
			window.location = "http://www.aspectsolar.com/checkout/cart/";
        },
        beforeUpdate: function(args){},
        afterUpdate: function(args){},

        _cntBtnInit: function(actionDataAsJson) {
            var cntBtn = $$(this.cssSelector)[0].select('.aw-acp-continue')[0];
            if (!cntBtn) {
                cntBtn = new Element('div');
                cntBtn.addClassName('aw-acp-continue');
                cntBtn.setStyle({
                    'display': 'none'
                });
                $$(this.cssSelector)[0].appendChild(cntBtn);
            }
            cntBtn.stopObserving('click', this._cntBtnOnClick.bind(this));
            cntBtn.observe('click', this._cntBtnOnClick.bind(this));

            var counter = AW_AjaxCartPro.config.data.addProductCounterBeginFrom;
            if ("counter_begin_from" in actionDataAsJson) {
                counter = actionDataAsJson.counter_begin_from;
            }
            if (counter > 0) {
                this._initCounterForBtn(cntBtn, counter);
            }
        },

        _cntBtnOnClick: function(event) {
            this.enabled = false;
            AW_AjaxCartProUI.hideBlock(this.cssSelector);
            event.stop();
        },

        _initCounterForBtn: function(cntBtn, counter) {
            var originalTxt = cntBtn.innerHTML;
            cntBtn.innerHTML = originalTxt + ' (' + counter + ')';
            var intId = setInterval(function(){
                counter--;
                if (counter === 0) {
                    if (!cntBtn.click) {
                        //safary for windows compatibility
                        if ( document.createEvent ) {
                            var evt = document.createEvent('MouseEvents');
                            evt.initEvent('click', true, false);
                            cntBtn.dispatchEvent(evt);
                        } else if( document.createEventObject ) {
                            cntBtn.fireEvent('onclick') ;
                        } else if (typeof node.onclick == 'function' ) {
                            cntBtn.onclick();
                        }
                    } else {
                        cntBtn.click();
                    }
                }
                cntBtn.innerHTML = originalTxt + ' (' + counter + ')';
            }, 1000);
            var clearIntervalFn = function(e){
                clearInterval(intId);
                cntBtn.stopObserving('click', this.bind(this));
            };
            cntBtn.observe('click', clearIntervalFn.bind(clearIntervalFn));
        }
    },
    {
        name: 'remove_confirmation',
        cssSelector: '#ajaxcartpro-remove-confirm',
        enabled: false,
        beforeFire: function(args){
            this.enabled = false;
        },
        afterFire: function(args){
            eval("var actionDataAsJson = " + args[0].actionData);
            actionDataAsJson = actionDataAsJson || {};
            var isConfirmationEnabled = AW_AjaxCartPro.config.data.removeProductConfirmationEnabled;
            if ("confirmation_enabled" in actionDataAsJson) {
                isConfirmationEnabled = actionDataAsJson.confirmation_enabled;
            }
            if (!isConfirmationEnabled) {
                return;
            }
            this.enabled = true;
            this._cntBtnInit(actionDataAsJson);
        },
        beforeUpdate: function(args){},
        afterUpdate: function(args){},

        _cntBtnInit: function(actionDataAsJson) {
            var cntBtn = $$(this.cssSelector)[0].select('.aw-acp-continue')[0];
            if (!cntBtn) {
                cntBtn = new Element('div');
                cntBtn.addClassName('aw-acp-continue');
                cntBtn.setStyle({
                    'display': 'none'
                });
                $$(this.cssSelector)[0].appendChild(cntBtn);
            }
            cntBtn.stopObserving('click', this._cntBtnOnClick.bind(this));
            cntBtn.observe('click', this._cntBtnOnClick.bind(this));

            var counter = AW_AjaxCartPro.config.data.removeProductCounterBeginFrom;
            if ("counter_begin_from" in actionDataAsJson) {
                counter = actionDataAsJson.counter_begin_from;
            }
            if (counter > 0) {
                this._initCounterForBtn(cntBtn, counter);
            }
        },

        _cntBtnOnClick: function(event) {
            this.enabled = false;
            AW_AjaxCartProUI.hideBlock(this.cssSelector);
            event.stop();
        },

        _initCounterForBtn: function(cntBtn, counter) {
            var originalTxt = cntBtn.innerHTML;
            cntBtn.innerHTML = originalTxt + ' (' + counter + ')';
            var intId = setInterval(function(){
                counter--;
                if (counter === 0) {
                    if (!cntBtn.click) {
                        //safary for windows compatibility
                        if ( document.createEvent ) {
                            var evt = document.createEvent('MouseEvents');
                            evt.initEvent('click', true, false);
                            cntBtn.dispatchEvent(evt);
                        } else if( document.createEventObject ) {
                            cntBtn.fireEvent('onclick') ;
                        } else if (typeof node.onclick == 'function' ) {
                            cntBtn.onclick();
                        }
                    } else {
                        cntBtn.click();
                    }
                }
                cntBtn.innerHTML = originalTxt + ' (' + counter + ')';
            }, 1000);
            var clearIntervalFn = function(e){
                clearInterval(intId);
                cntBtn.stopObserving('click', this.bind(this));
            };
            cntBtn.observe('click', clearIntervalFn.bind(clearIntervalFn));
        }
    },
    {
        name: 'options',
        rootCssSelector: '#acp-configurable-block',
        cssSelector: '#acp-product-options',
        enabled: false,
        beforeFire: function(args){},
        afterFire: function(args){},
        beforeUpdate: function(args){},
        afterUpdate: function(args){
            var el = $$(this.cssSelector);
            if (el.length === 1) {
                this.enabled = true;
                var msg = args[0];
                this._addMsgBlock(msg);
                this._appearGroupedBlock();
                this._appearGiftBlock();
                this._appearAWGiftBlock();
                this._cancelBtnInit();
                this._addToCartBtnInit();
            }
        },

        _addMsgBlock: function(msg) {
            if (!msg || !msg.length || msg.length < 1) {
                return;
            }
            var msgBlock = $$('.acp-msg-block')[0];
            msgBlock.addClassName('messages');
            AW_AjaxCartProUI._show(msgBlock);
            msgBlock.appendChild(new Element('li'));
            msgBlock = msgBlock.select('li')[0];
            msgBlock.innerHTML = '';
            var typeCount = {'error':0,'warning':0,'notice':0};
            msg.each(function(message){
                switch(message.type) {
                    case 'error':
                        typeCount.error++;
                        break;
                    case 'warning':
                        typeCount.warning++;
                        break;
                    case 'notice':
                        typeCount.notice++;
                        break;
                    default:
                }
            });
            var type = 'notice';
            if (typeCount.warning > 0) {
                type = 'warning';
            }
            if (typeCount.error > 0) {
                type = 'error';
            }
            switch(type) {
                case 'error':
                    msgBlock.addClassName('error-msg');
                    msgBlock.removeClassName('notice-msg');
                    msgBlock.removeClassName('warning-msg');
                    break;
                case 'warning':
                    msgBlock.addClassName('warning-msg');
                    msgBlock.removeClassName('error-msg');
                    msgBlock.removeClassName('notice-msg');
                    break;
                case 'notice':
                    msgBlock.addClassName('notice-msg');
                    msgBlock.removeClassName('error-msg');
                    msgBlock.removeClassName('warning-msg');
                    break;
                default:
                    AW_AjaxCartProUI._hide(msgBlock);
            }
            msg.each(function(message){
                msgBlock.innerHTML += message.text + '<br />';
            });
        },

        _appearGroupedBlock: function() {
            var possibleSelectors = ['table.grouped-items-table', 'table#super-product-table'];
            var groupedBlock = null;
            possibleSelectors.each(function(selector){
                if (groupedBlock !== null) {
                    return;
                }
                groupedBlock = $('acp-product-type-data').select(selector);
                if (groupedBlock.length === 0) {
                    groupedBlock = null;
                    return;
                }
                groupedBlock = groupedBlock[0];
            });
            AW_AjaxCartProUI._show($('acp-product-type-data'));
            $('acp-product-type-data').childElements().each(function(el){
                if (el === groupedBlock || el.tagName.toLocaleLowerCase() === 'script') {
                    return;
                }
                AW_AjaxCartProUI._hide(el);
            });
            return null;
        },

        _appearGiftBlock: function() {
            var giftBlock = $('acp-product-type-data').select('.giftcard-send-form');
            if (giftBlock.length === 0) {
                return;
            }
            giftBlock = giftBlock[0];
            AW_AjaxCartProUI._show($('acp-product-type-data'));
            AW_AjaxCartProUI._show(giftBlock.up());
            giftBlock.up().childElements().each(function(el){
                if (el === giftBlock || el.tagName.toLocaleLowerCase() === 'script') {
                    return;
                }
                AW_AjaxCartProUI._hide(el);
            });
            return null;
        },

        _appearAWGiftBlock: function() {
            var giftBlock = $('acp-product-type-data').select('.aw-gc-form');
            if (giftBlock.length === 0) {
                return;
            }
            giftBlock = giftBlock[0];
            AW_AjaxCartProUI._show($('acp-product-type-data'));
            AW_AjaxCartProUI._show(giftBlock);
            return null;
        },

        _cancelBtnInit: function() {
            var cancelBtn = $$(this.cssSelector)[0].select('.aw-acp-continue')[0];
            cancelBtn.stopObserving('click', this._cancelBtnOnClick.bind(this));
            cancelBtn.observe('click', this._cancelBtnOnClick.bind(this));
        },

        _cancelBtnOnClick: function(event) {
            this._hideBlock();
            event.stop();
        },

        _addToCartBtnInit: function() {
            var addToCartButton = $$(this.cssSelector)[0].select('.aw-acp-checkout')[0];
            addToCartButton.stopObserving('click', this._addToCartBtnOnClick.bind(this));
            addToCartButton.observe('click', this._addToCartBtnOnClick.bind(this));
        },

        _addToCartBtnOnClick: function(event) {
            if(productAddToCartFormAcp.validator && productAddToCartFormAcp.validator.validate()){
                 productAddToCartFormAcp.form.submit();
                 this._hideBlock();
            }
            event.stop();
        },

        _hideBlock: function() {
            this.enabled = false;
            AW_AjaxCartProUI.hideBlock(this.cssSelector);
            $$(this.rootCssSelector)[0].down().remove();
        }
    }
];


AW_AjaxCartProUIBlocks.each(function(block){
    AW_AjaxCartProUI.registerBlock(block);
});





