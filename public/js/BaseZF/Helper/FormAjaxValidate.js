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
        fields: $empty
    },

    options: {
        scroll: true,
        action: $empty
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

        // init datas
        this.elements.form = element;
        this.elements.fields = this.getFields();
        this.options = $merge(this.options, options);

    },

    /**
     * Html builder/Fx
     */

    scrollToError: function(field) {
    },

    addErrors: function(fieldsMsgs) {
    },

    clearErrors: function(fields) {
    },

    hasError: function(field)
    {
    },

    /**
     * Ajax Validation
     */
    beginTransaction: function(nb)
    {
        // disable submit
    },

    commitTransaction: function()
    {
    },

    roolBackTransaction: function()
    {
    },

    processValidation: function(formSubmit)
    {
        var formSubmit = ($type(formSubmit) ? true : false);
    },

    callbackValidation: function(json)
    {
    },

    /**
     * Tools
     */
    initFieldEvents: function(field) {
    },

    initFieldEvents: function(field) {

        var eventNames = new Array();

        switch (field.type) {
            case 'checkbox':
            case 'select-one':
               eventNames.push('change');
               break;

           case 'text':
               eventNames.push('onEnter');
               eventNames.push('blur');
               break;

            default:
               eventNames.push('blur');
               break;
        }

        eventNames.each(function(eventName) {


        });
    },

    getFields: function(root) {

        if (!$type(root)) {
            var root = this.elements.form;
        }

        // init response
        fields = $H();

        // get by name
        fieldElements = root.getElements('input, select, textarea');
        fieldElements.each(function(v, k) {
            //console.info(v.get('name'));

        });

        return fields;
    },

    getFieldContainer: function(field) {

    },

    toQueryString: function(root){

        if (!$type(root)) {
            var root = this.elements.form;
        }

		var queryString = [];
		root.getElements('input, select, textarea').each(function(el){
			if (!el.name || el.disabled) return;
			var value = (el.tagName.toLowerCase() == 'select') ? Element.getSelected(el).map(function(opt){
				return opt.value;
			}) : ((el.type == 'radio' || el.type == 'checkbox') && !el.checked) ? null : el.value;
			$splat(value).each(function(val){
				queryString.push(el.name + '=' + encodeURIComponent(val));
			});
		});
		return queryString.join('&');
	},
});
