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

    Extends: BaseZF.Helper.Abstract,
    Implements: BaseZF.Helper.AjaxAbstract,

    elements: {
        form: null,
        containers: []
    },

    focusField: null,
    lastField: null,

    options: {
        scroll: true
    },

    initializeSelector: function(root, options) {

        if (!$type(root)) {
            var root = document;
        }

        root.getElements('form.formAjaxValidate').each(function(element) {
            new BaseZF.Helper.FormAjaxValidate('element', element, options);
        }, this);
    },

    initializeElement: function(element, options) {

        // check semaphore
        if ($type(this.elements.form) == 'element') {
            return;
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

           case 'file':
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
                        this.validationTimer = this.processFieldValidation.delay(250, this, field);
                        return false;
                    }
                }.bind(this));

            } else {

                field.addEvent(eventName, function(e) {

                    var isEmpty = (field.value.length == 0);

                    if (field.retrieve('formContainer').hasClass('optional') && isEmpty) {
                        return;
                    } else if (eventName != 'blur') {
                        this.processFieldValidation(field);
                        return;
                    }

                    // direct process to validation asap have value
                    if (!isEmpty) {
                        this.processFieldValidation(field);

                    // delay validation cause is empty and can from a tab usage
                    } else {
                        $clear(this.validationTimer);
                        this.validationTimer = this.processFieldValidation.delay(250, this, field);
                    }

                }.bind(this));
            }

        }, this);

        field.addEvent('focus', function(e) {
            this.focusField = field;
        }.bind(this));
    },

    /**
     * Html builder/Fx
     */

    scrollToField: function(field) {

        if ($type(this.options.scroll)) {

            var scroll = new Fx.Scroll(document, {
                wait: false,
                duration: 1500,
                offset: {'x': -100, 'y': -50},
                transition: Fx.Transitions.Quad.easeInOut
            });

            scroll.toElement(field);
        }

        try {
            field.fireEvent('focus').focus();
        } catch(e){
            //IE barfs if you call focus on hidden elements
        }
    },

    addFieldErrors: function(field, errorsMsg, clear) {

        var container = field.retrieve('formContainer');
        var fieldValue = this.toQueryString(container);

        if ($type(clear)) {
            container.getElement('.error').destroy();
        }

        // update lastErrorValue
        container.store('lastErrorValue', fieldValue);

        // build errorsMsg
        container.addClass('error');
        container.removeClass('validate');

        var errorList = new Element('ul', {'class': 'errors'});
        $H(errorsMsg).each(function(error, errorType) {
            var errorEntry = new Element('li').appendText(error);
            errorEntry.inject(errorList);
        });

        errorList.injectTop(container);
    },

    clearFieldErrors: function(field) {

        // get container
        var container = field.retrieve('formContainer');

        if (container.hasClass('error')) {
            container.getElement('.errors').destroy();
            container.store('lastErrorValue', null);
            container.removeClass('error');
        }
    },

    clearErrors: function() {

        this.elements.containers.each( function(container) {
            container.getElement('.error').destroy();
            container.store('lastErrorValue', null);
            container.removeClass('error');
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
        // is a field for check previous value ?
        var parentField = this.getParentField(field);
        if ($type(parentField) == 'element') {

            // validate parent field if has value and no error
            if(this.hasError(parentField)) {
                this.scrollToField(parentField);
            } else if (parentField.value.length > 0) {
                field = parentField;
            }
        }

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
              $type(this.focusField) &&
              this.focusField != field &&
              this.hasError(this.focusField)
        ) {
            return;
        }

        // disable form submit
        this.beginTransaction();

        // clear field errors
        this.clearFieldErrors(field);

        // has values to validate
        var fieldValue = this.toQueryString(container);
        var validationCache = container.retrieve('validationCache', $H());

        if (validationCache.has(fieldValue)) {

            var errorMsg = validationCache.get(fieldValue);
            if (errorMsg !== null) {
                this.addFieldErrors(field, errorMsg);
                this.scrollToField(field);
            }

        } else {

            // set field has loading
            container.removeClass('validate');
            container.addClass('loading');

            var myRequest = this.getRequest({
                method: this.elements.form.get('method'),
                url: this.elements.form.get('action'),
                data: fieldValue,
                link: 'chain'
            }, 'JSON');

            myRequest.send();
        }
    },

    requestCallback: function(json, b)
    {
        // reset last field
        this.lastField = null;

        try {

            var fieldErrors = $H(json);

            // add error on field
            fieldErrors.each(function(errorsMsg, field) {

                var field = this.elements.form.getElement('[name^=' + field + ']');

                // ignore missing fields
                if($type(field) == false) {
                    return;
                }

                var container = field.retrieve('formContainer');

                // add error is needed
                if (errorsMsg != null) {
                    this.addFieldErrors(field, errorsMsg);
                    this.lastField = field;
                // add validate
                } else {
                    container.addClass('validate');
                }

                // update cache
                this.updateFieldCache(field, errorsMsg);

                // hide loading
                container.removeClass('loading');

            }, this);

            if ($type(this.lastField) == 'element') {
                this.scrollToField(this.lastField);
            }

        } catch (e) {

            throw e;
        }
    },

    /**
     * Tools
     */

    getParentField: function(field) {

        if (field.name.contains('_check')) {
            return this.elements.form.getElement('[name=' + field.name.replace('_check','') + ']')
        }
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

                // is a field for check previous value ?
                var parentField = this.getParentField(el);
                if ($type(parentField)) {
                    elementsValues.set(parentField.name, [parentField.value]);
                }

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
    },
});

