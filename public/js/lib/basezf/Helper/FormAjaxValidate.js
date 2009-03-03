/**
 * FormAjaxValidate.js
 *
 * @category   BaseZF_JS_Helper
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

if (typeof BaseZF == "undefined") var BaseZF = {};
if (typeof BaseZF.Helper == "undefined") BaseZF.Helper = {};

BaseZF.Helper.FormAjaxValidate = new Class({

    Extends: BaseZF.Class.Helper,
    Implements: BaseZF.Helper.AjaxAbstract,

    elements: {
        form: null,
        containers: []
    },

    focusElement: null,
    processingElement: [],

    options: {
        scroll: true
    },

    launcherSelector: function(root, options) {

        if (!$type(root)) {
            var root = document;
        }

        root.getElements('form.formAjaxValidate').each(function(element) {
            new BaseZF.Helper.FormAjaxValidate('element', element, options);
        }, this);
    },

    launcherElement: function(element, options) {

        // check semaphore
        if (0) {
        }

        // init datas
        this.elements.form = element;
        this.options = $merge(this.options, options);

        // init fields validators
        this.initFields();
    },

    initFields: function() {

        this.elements.containers = [];

        // get fields container required
        this.elements.form.getElements('div.required, div.optional').each( function(container) {

            // check semaphore
            if (0) {
            }

            // save container
            this.elements.containers.push(container);

            // get field of container
            this.getFields(container).each(function(field) {

                // save container to field
                field.store('formContainer', container);

                // add validation events
                this.initFieldEvents(field);

                // set current state
                if(this.hasError(field)) {
                    container.store('lastErrorValue', this.toQueryString(container));
                }

            }, this);

         }, this);
    },

    initFieldEvents: function(field) {

        var eventNames = new Array();

        switch (field.type) {
            case 'radio':
            case 'checkbox':
            case 'select-one':
            case 'select-multiple':
               eventNames.push('change');
               break;

           case 'password':
           case 'text':
               eventNames.push('onEnter');
               eventNames.push('blur');
               break;


           case 'reset':
           case 'submit':
               // field with no validators
               break;

            default:
               eventNames.push('blur');
               break;
        }

        // add event validation
        eventNames.each(function(eventName) {

            if (eventName == 'onEnter') {

                field.addEvent('keydown', function(e) {
                    if(new Event(e).code == Event.Keys.esc || new Event(e).code == Event.Keys.enter) {
                        $clear(this.validationTimer);
                        this.validationTimer = this.processFieldValidation.delay(500, this, field);
                        return false;
                    }
                }.bind(this));

            } else {

                field.addEvent(eventName, function(e) {

                    // direct process to validation asap have value
                    if (field.value.length > 0) {
                        this.processFieldValidation(field);

                    // delay validation cause is empty and can from a tab usage
                    } else {
                        $clear(this.validationTimer);
                        this.validationTimer = this.processFieldValidation.delay(500, this, field);
                    }

                }.bind(this));

            }

        }, this);

        field.addEvent('focus', function(e) {
            this.focusElement = field;
        }.bind(this));
    },

    /**
     * Html builder/Fx
     */

    scrollField: function(field) {

        if (this.options.scroll) {
            var myFx = new Fx.Scroll(window).start(0, field.retrieve('formContainer').offsetTop - 50);
        }

        try {
            field.fireEvent('focus').focus();
        } catch(e){} //IE barfs if you call focus on hidden elements

    },

    addFieldErrors: function(field, errorsMsg) {

        var container = field.retrieve('formContainer');
        var fieldValue = this.toQueryString(container);

        // update lastErrorValue
        container.store('lastErrorValue', fieldValue);

        // build errorsMsg
        container.addClass('error');
        var errorList = new Element('ul', {'class': 'errors'});
        $H(errorsMsg).each(function(error, errorType) {
            var errorEntry = new Element('li').appendText(error);
            errorEntry.inject(errorList);
        });

        errorList.injectTop(container);

        this.scrollField(field);
    },

    clearFieldErrors: function(field) {

        // get container
        var container = field.retrieve('formContainer');

        if (container.hasClass('error')) {
            container.removeClass('error');
            container.removeChild(container.getElement('.errors'));
            container.store('lastErrorValue', null);
        }
    },

    clearErrors: function() {

        this.form.getElements('div.error').each( function(container) {
            container.removeClass('error');
            container.removeChild(container.getElement('.errors'));
            container.store('lastErrorValue', null);
        });
    },

    hasError: function(field)
    {
        return field.retrieve('formContainer').hasClass('error');
    },

    /**
     * Ajax Validation
     */
    beginTransaction: function(nb)
    {
        // disable submit
        this.toggleFormSubmit(true);
    },

    commitTransaction: function()
    {
        // enable submit
        this.toggleFormSubmit(false);
    },

    roolBackTransaction: function()
    {
        // enable submit
        this.toggleFormSubmit(false);
    },

    updateFieldCache: function(field, errorsMsg) {

        // get container
        var container = field.retrieve('formContainer');

        // update cache
        var fieldValue = this.toQueryString(container);
        var validationCache = container.retrieve('validationCache', $H());
        if (!validationCache.has(fieldValue)) {
            validationCache.set(fieldValue, errorsMsg);
        }
    },

    processFieldValidation: function(field)
    {
        var container = field.retrieve('formContainer');

        // do not valid same error value
        if (
            this.hasError(field) &&
            container.retrieve('lastErrorValue') == this.toQueryString(container)
        ) {
            return;
        }

        // do not valid field on back jump
        if (
              $type(this.focusElement) &&
              this.focusElement != field &&
              this.hasError(this.focusElement)
        ) {
            return;
        }

        // disable form submit
        this.beginTransaction();

        this.processingElement.push(field);

        // clear field errors
        this.clearFieldErrors(field);

        // has values to validate
        var fieldValue = this.toQueryString(container);
        var validationCache = container.retrieve('validationCache', $H());

        if (validationCache.has(fieldValue)) {

            var errorMsg = validationCache.get(fieldValue);
            if (errorMsg !== null) {
                this.addFieldErrors(field, errorMsg);
            }

        } else {

            var myRequest = this.getRequest({
                method: this.elements.form.get('method'),
                url: this.elements.form.get('action'),
                data: fieldValue
            }, 'JSON', this);

            myRequest.send();
        }
    },

    processValidation: function(formSubmit)
    {
        // @todo
    },

    requestCallback: function(json, b)
    {
        try {

            // add error on field
            $H(json).each(function(errorsMsg, field) {

                var field = this.elements.form.getElement('[name^=' + field + ']');
                this.addFieldErrors(field, errorsMsg);

                // update cache
                this.updateFieldCache(field, errorsMsg);

                // remove from spool
                this.processingElement.erase(field);

            }, this);

            // update field cache with good response
            this.processingElement.each(function(field) {
                this.updateFieldCache(field, null);
            }, this);

            // hide loading
            this.hideLoading();

        } catch (e) {

            // force hide loading
            this.hideLoading(true);

            throw e;
        }
    },

    /**
     * Tools
     */

    getFieldCheckParent: function(field) {
        return this.elements.form.getElement('[name=' + field.name.replace('_check','') + ']')
    },

    getFieldCheck: function(field) {
        return this.elements.form.getElement('[name=' + field.name + '_check]')
    },

    getFields: function(root) {

        // get by name
        fieldElements = root.getElements('input, select, textarea');

        return fieldElements;
    },

    getFieldContainer: function(field) {
        return field.retrieve('formContainer');
    },

    toggleFormSubmit: function(state)
    {
        this.elements.form.getElement('input[type=submit]').disabled = (state) ? false : true ;
    },

    toQueryString: function(root){

        if (!$type(root)) {
            var root = this.elements.form;
        }

        var elementsValues = $H();
        var elements = root.getElements('input, select, textarea');

        elements.each(function(el) {

			if (!el.name || el.disabled) return;

            var value = '';
            var type = el.type;
            var tagName = el.tagName.toLowerCase();
            var name = el.name;



            var values = elementsValues.get(name);
            if ($type(values) != 'array') {
                values = [];
            }

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

                // is a field for check previous value ?
                var elParent = this.getFieldCheckParent(el);
                if ($type(elParent) && elParent.type == type) {

                    // validate current if has value
                    if(elParent.value.length == 0) {
                        name = elParent.name;
                        values = [elParent.value];
                    } else {
                        elementsValues.set(elParent.name, [elParent.value]);
                    }
                }
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
	},
});

