/**
 * FormSelectMass.js
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
        form: $empty,
        containers: $empty
    },

    options: {
        scroll: true,
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
                    container.store('errorValue', this.toQueryString(container));
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
                        this.processFieldValidation.delay(500, this, field);
                        return false;
                    }

                }.bind(this));

            } else {
                field.addEvent(eventName, this.processFieldValidation.bind(this, field));
            }

        }, this);
    },

    /**
     * Html builder/Fx
     */

    scrollField: function(field) {

        if (this.option.scroll) {
            var myFx = new Fx.Scroll(window).start(0, field.retrieve('formContainer').offsetTop - 50);
        }

        try {
            field.fireEvent('focus').focus();
        } catch(e){} //IE barfs if you call focus on hidden elements

    },

    addFieldErrors: function(field, errors) {

        var errorList = new Hash(errors);
        var container = field.retrieve('formContainer');

        // save error value
        container.store('errorValue', this.toQueryString(container));

        // build errors msg
        container.addClass('error');

        var errorList = new Element('ul', {'class': 'errors'});
        $H(errors).each(function(error, errorType) {
            var errorEntry = new Element('li').appendText(error);
            errorEntry.inject(errorList);
        });

        errorList.injectTop(container);
    },

    clearFieldErrors: function(field) {

        var container = field.retrieve('formContainer');

        if (container.hasClass('error')) {
            container.store('errorValue', null);
            container.removeClass('error');
            container.removeChild(container.getElement('.errors'));
        }
    },

    clearErrors: function() {

        this.form.getElements('div.error').each( function(container) {
            container.store('errorValue', null);
            container.removeClass('error');
            container.removeChild(container.getElement('.errors'));
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

    processFieldValidation: function(field)
    {
        var container = field.retrieve('formContainer');

        if (this.hasError(field) && container.retrieve('errorValue') == this.toQueryString(container)) {
            return;
        }


        container.setStyle('background', '#FFF9BF');
        container.setStyle.delay(1000, container, ['background', '']);


        this.beginTransaction();

        // clear error
        this.clearFieldErrors(field);

        var myRequest = this.getRequest({
            method: this.elements.form.get('method'),
            url: this.elements.form.get('action'),
            data: this.toQueryString(container)
        }, 'JSON', this);

        myRequest.send();
    },

    processValidation: function(formSubmit)
    {
        this.beginTransaction();
    },

    requestCallback: function(json)
    {
        try {

            $H(json).each(function(errors, field) {

                var field = $(field);

                this.clearFieldErrors(field);
                this.addFieldErrors(field, errors);
                this.scrollField(field);

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

		var queryString = [];
        var elements = root.getElements('input, select, textarea');
		elements.each(function(el){

			if (!el.name || el.disabled) return;
/*
            if (el.tagName.toLowerCase() == 'select') {

                var value = Element.getSelected(el).map(function(opt){
                    return opt.value;
                });

                if (value.length == 0) {
                    value = '';
                }

            } else if((el.type == 'radio' || el.type == 'checkbox') && !el.checked) {
                var value = '';
            } else {
                var value = el.value;
            }
*/
            // optimised version
			var value = (el.tagName.toLowerCase() == 'select') ? Element.getSelected(el).map(function(opt){
				return opt.value;
			}) : ((el.type == 'radio' || el.type == 'checkbox') && !el.checked) ? null : el.value;

            if (el.tagName.toLowerCase() == 'select' && value.length == 0) {
                value = '';
            }

			$splat(value).each(function(val){
				queryString.push(el.name + '=' + encodeURIComponent(val));
			});
		});

		return queryString.join('&');
	},
});
