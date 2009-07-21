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
        var originElement = ($type(this) == 'element' ? $(this) : null)

        try {

            if ($type(responseJavaScript) && responseJavaScript.length > 0) {
                eval(responseJavaScript);
            }

            if ($type(responseHTML) && responseHTML.length > 0) {
                new BaseZF.Class.Helper.run(responseHTML);
            }

        } catch (e) {
            throw e;
        }

        this.hideLoading();
    }
});

