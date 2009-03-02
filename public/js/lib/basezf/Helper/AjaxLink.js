/**
 * AjaxLink.js
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

    getRequest: function(options, type, origin) {

        // one request per helper
        if (typeof(this.myRequest) != 'undefined') {
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
            onCancel: this.hideLoading,
            onFailure: this.hideLoading,
            onRequest: this.showLoading,
            onSuccess: requestCallback,

        }
        , options);

        if (typeof(type) != 'undefined') {
            eval('this.myRequest = new Request.' + type + '(options);');
        } else {
            this.myRequest = new Request(options);
        }

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

/**
 * AjaxLink Helper Class
 */
BaseZF.Helper.AjaxLink = new Class({

    Extends: BaseZF.Class.Helper,
    Implements: BaseZF.Helper.AjaxAbstract,

    launcherSelector: function(root, options) {

        if (!$type(root)) {
            var root = document;
        }

        root.getElements('a.ajaxLink').each(function(element) {
            this.launcherElement(element, options);
        }, this);
    },

    launcherElement: function(element, options) {

        if (element.retrieve('ajaxlink:semaphore')) {
            return;
        }

        var that = this;

        element.addEvent('click', function(e)
        {
            new Event(e).stop();

            var myRequestParams = [{url: this.href}, 'HTML', element];
            var myRequest = that.getRequest.run(myRequestParams, that);

            myRequest.send();

            return false;

        }, element);

        element.retrieve('ajaxlink:semaphore', true);
    },

    requestCallback: function(responseTree, responseElements, responseHTML, responseJavaScript) {

        // get abtract layer
        that = BaseZF.Helper.AjaxAbstract;

        var originElement = ($type(this) == 'element' ? $(this) : null)

        try {

            eval(responseJavaScript);

            if ($type(responseHTML) && responseHTML.length > 0) {
                new BaseZF.Class.Helper.run(responseHTML);
            }

        } catch (e) {
            throw e;
        }

        that.hideLoading();
    }
});

