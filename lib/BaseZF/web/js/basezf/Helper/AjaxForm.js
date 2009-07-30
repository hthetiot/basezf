/**
 * AjaxForm.js
 *
 * @category   BaseZF_JS_Helper
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

if (typeof BaseZF == "undefined") var BaseZF = {};
if (typeof BaseZF.Helper == "undefined") BaseZF.Helper = {};

/**
 * AjaxForm Helper Class
 */
BaseZF.Helper.AjaxForm = new Class({

    Extends: BaseZF.Class.Helper ,
    Implements: BaseZF.Helper.AjaxAbstract,

    launcherSelector: function(root, options) {

        if (!$type(root)) {
            var root = document;
        }

        root.getElements('form.ajaxForm').each(function(element) {
            this.launcherElement(element, options);
        }, this);
    },

    launcherElement: function(element, options) {

        if (element.retrieve('ajaxForm:semaphore')) {
            return;
        }

        var that = this;

        element.addEvent('submit', function(e)
        {
            new Event(e).stop();

            var myRequest = that.getRequest({
                url: this.action,
                method: this.method,
                data: that.toQueryString(this)
            }, 'HTML', this);

            myRequest.send();

            return false;

        }, element);

        element.retrieve('ajaxForm:semaphore', true);
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
    },

    toQueryString: function(root){

        var elementsValues = $H();
        var elements = root.getElements('input, select, textarea');

        elements.each(function(el) {

            if (!el.name || el.disabled) return;

            var value = '';
            var type = el.type;
            var tagName = el.tagName.toLowerCase();
            var name = el.name;
            var values = $splat(elementsValues.get(name));

            if (tagName == 'select') {

                Element.getSelected(el).each(function(opt){
                    values.push(opt.value);
                });

            } else if (type == 'radio' && el.checked) {
                values.push(el.value);
            } else if (type == 'checkbox' && el.checked) {
                values.push(el.value);
            } else if (type == 'password' || type == 'text' || type == 'textarea') {
                values.push(el.value);
            }

            elementsValues.set(name, values);

        }, this);

        var queryString = [];
        elementsValues.each(function(values, name) {

            if (values.length == 0) {
                queryString.push(name + '=');
            } else {
                values.each(function(value) {
                    queryString.push(name + '=' + encodeURIComponent(value));
                });
            }
        });

        return queryString.join('&');
    }
});

