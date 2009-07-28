/**
 * AjaxAbstract.js
 *
 * @category   BaseZF_JS_Helper
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

if (typeof BaseZF == "undefined") var BaseZF = {};
if (typeof BaseZF.Helper == "undefined") BaseZF.Helper = {};

BaseZF.Helper.AjaxAbstract = {

    loadingElement: $empty,
    initialized: false,
    nbActiveRequest: 0,

    Extends: BaseZF.Class.Helper,

    initHelper: function() {

        // get abtract layer
        that = BaseZF.Helper.AjaxAbstract;

        if (!$type($('loading')) || that.initialized) {
            return;
        }

        // init loading div
        that.loadingElement = $('loading');
        that.loadingElement.setStyles({
            'position': 'fixed',
            'top': '0px',
            'left': '0px'
        });

        that.initialized = true;
        that.hideLoading(true);
    },

    hideLoading: function(force) {

        // get abtract layer
        that = BaseZF.Helper.AjaxAbstract;

        that.nbActiveRequest--;

        if (
           that.loadingElement &&
            (
                $type(force) ||
                (that.nbActiveRequest <= 0 && that.loadingElement.hasClass('loading'))
            )
        ) {

            // hide loading
            that.loadingElement.removeClass('loading');
            that.loadingElement.fade(0);
            that.nbActiveRequest = 0;
        }
    },

    showLoading: function() {

        // get abtract layer
        that = BaseZF.Helper.AjaxAbstract;

        that.nbActiveRequest++;

        if (that.loadingElement && !that.loadingElement.hasClass('loading')) {
            that.loadingElement.addClass('loading');
            that.loadingElement.fade(1);
        }
    },

    getRequest: function(options, type, origin, singleton) {

        // one request per helper
        if ($type(singleton) && $type(this.myRequest)) {
            this.myRequest.cancel();
        }

        // set callback origin
        if (typeof(origin) != 'undefined') {
           requestCallback = this.requestCallback.bindWithEvent(origin)
        } else {
           requestCallback = this.requestCallback.bind(this)
        }

        options = $merge({
            method: 'get',
            evalScripts: false,
            onSuccess: requestCallback,
            noCache: true
        }
        , options);

        switch (type) {
            case 'JSON':
                this.myRequest = new Request.JSON(options);
                break;

            case 'HTML':
                this.myRequest = new Request.HTML(options);
                break;

            default:
                this.myRequest = new Request(options);
                break;
        }

        // add event
        this.myRequest.addEvent('request', this.showLoading);
        this.myRequest.addEvent('exception', this.showLoading);
        this.myRequest.addEvent('failure', this.hideLoading);
        this.myRequest.addEvent('success', this.hideLoading);

        return this.myRequest;
    }
    /*

    // example callback for Request.HTML
    requestCallback: function(responseTree, responseElements, responseHTML, responseJavaScript) {
    }

    // example callback for Request.JSON
    requestCallback: function(json) {
    }
    */
};
