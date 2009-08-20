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

    Extends: BaseZF.Class.Helper,

    initHelper: function() {

        // has loading element
        if (!$type($('loading'))) {
            return;
        }

        // store loading element
        this.loadingElement = $('loading');

        // init loading if needed
        if (!$('loading').retrieve('ajaxAbstract:semaphore')) {
            this.initLoading();
        }
    },

    initLoading: function()
    {
        this.loadingElement.setStyles({
            'position': 'fixed',
            'top': '0px',
            'left': '0px'
        });

        this.loadingElement.store('ajaxAbstract:semaphore', true);
        this.loadingElement.store('ajaxAbstract:nbActiveRequest', 0);

        this.hideLoading(true);
    },

    hideLoading: function(force) {

        // get current transation level
        var nbActiveRequest = this.loadingElement.store(
            'ajaxAbstract:nbActiveRequest',
            this.loadingElement.retrieve('ajaxAbstract:nbActiveRequest', 0) - 1
        );

        if (
           this.loadingElement &&
            (
            $type(force) ||
            (nbActiveRequest <= 0 && this.loadingElement.hasClass('loading'))
            )
        ) {

            // hide loading
            this.loadingElement.removeClass('loading');
            this.loadingElement.fade(0);

            this.loadingElement.store('ajaxAbstract:nbActiveRequest', 0);
        }
    },

    showLoading: function() {

        var nbActiveRequest = this.loadingElement.store(
            'ajaxAbstract:nbActiveRequest',
            this.loadingElement.retrieve('ajaxAbstract:nbActiveRequest', 0) + 1
        );

        if (this.loadingElement && !this.loadingElement.hasClass('loading')) {
            this.loadingElement.addClass('loading');
            this.loadingElement.fade(1);
        }
    },

    getRequest: function(options, type, origin) {

        // add default options
        var options = $merge({
            method: 'get',
            evalScripts: false,
            noCache: true,
            link: 'cancel'
        }
        , options);

        // update request instance options
        if ($type(this.myRequest)) {

            this.myRequest.setOptions(options);

        // create this.request instance
        } else {

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

            // add events
            this.myRequest.addEvent('request', this.showLoading.bind(this));
            this.myRequest.addEvent('exception', this.showLoading.bind(this));
            this.myRequest.addEvent('failure', this.hideLoading.bind(this));
        }

        // set callback origin
        if (typeof(origin) != 'undefined') {
           requestCallback = this.requestCallback.bindWithEvent(origin)
        } else {
           requestCallback = this.requestCallback.bind(this)
        }

        // add callback event for success
        this.myRequest.removeEvents('success');
        this.myRequest.addEvent('success', this.hideLoading.bind(this));
        this.myRequest.addEvent('success', requestCallback);

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
