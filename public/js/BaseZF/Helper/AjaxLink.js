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

    loadingElement: null,
    initialized: false,
    nbActiveRequest: 0,

    Extends: BaseZF.Class.Helper,

    initHelper: function() {

        // get abtract layer
        that = BaseZF.Helper.AjaxAbstract;

        if (that.initialized) {
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

    getRequest: function(options) {

        // get abtract layer
        that = BaseZF.Helper.AjaxAbstract;

        if (typeof(this.myRequest) != 'undefined') {
            this.myRequest.cancel();
        }

        options = $merge({
            method: 'get',
            evalScripts: false,
            onCancel: that.hideLoading,
            onRequest: that.showLoading,
            onSuccess: that.ajaxCallback.bindWithEvent(this)
        }
        , options);

        this.myRequest = new Request.HTML(options);

        return this.myRequest;
    },

    ajaxCallback: function(responseTree, responseElements, responseHTML, responseJavaScript) {

        // get abtract layer
        that = BaseZF.Helper.AjaxAbstract;

        var originElement = ($type(this) == 'element' ? $(this) : null)

        try {

            eval(responseJavaScript);

            if (responseHTML.length > 0) {
                new BaseZF.Class.Helper.run(responseHTML);
            }

        } catch (e) {
            throw e;
        }

        that.hideLoading();
    }
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

        that = this;

        element.addEvent('click', function(e)
        {
            new Event(e).stop();

            var myRequest = that.getRequest.run({
                url: this.href
            }, this);

            myRequest.send();

            return false;

        }, element);

         element.retrieve('ajaxlink:semaphore', true)
    }
});
