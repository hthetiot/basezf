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

BaseZF.Helper.FormSelectMass = new Class({

    Extends: BaseZF.Class.Helper,

    elements: {
        container: $empty,
        label: $empty,
        values: $empty,
        options: $empty
    },

    launcherSelector: function(root, options) {

        if (!$type(root)) {
            var root = document;
        }

        root.getElements('div.selectMass').each(function(element) {
            new BaseZF.Helper.FormSelectMass('element', element, options);
        }, this);
    },

    launcherElement: function(element, options) {

        // store elements
        this.elements = {
            container: element.getElement('div.selectMassOptions'),
            label: element.getParent().getElement('div.selectMassLabel'),
            value: $pick(element.getParent().getElement('.selectMassValue'), element.getParent().getElement('.selectMassLabel')),
            options: element.getElements('label')
        };

        // init label event
        this.elements.label.addEvents({
            'mouseout': this.onMouseOut.bind(this),
            'mouseover': this.onMouseOver.bind(this),
            'click': this.toogle.bind(this),
            'mousedown': function() {
                return false;
            }
        });

        // init option container event
        this.elements.container.addEvents({
            'mouseout': this.onMouseOut.bind(this),
            'mouseover': this.onMouseOver.bind(this)
        });

        // init options event
        this.elements.options.each(function(el) {
           el.addEvent('click', this.add.bind(this, [el]));
        }, this);

        // first init
        this.add();
    },

    toogle: function() {
        this.elements.container.fade((this.elements.container.getStyle('visibility') == 'hidden' ? 'in' : 'out'));
    },

    add: function(el) {

        if ($type(el) == 'element') {
            input = el.getElement("input");

            if(input.type == 'radio') {
                input.checked = true;
                this.toogle();
            } else {
                input.checked = !input.checked;
            }
        }

        var values = '';
        var options = this.elements.container.getElementsByTagName("input");
        for(var i=0; i<options.length; i++) {
            var option = $(options[i]);
            if(option.checked == true) {
                values += (values != "" ? ", " : "") + option.getNext('span').get('html');
            }
        }

        this.elements.value.set('html', values);
    },

    onMouseOut: function() {

        var that = this
        this.wait = function() {
            if(that.elements.container.getStyle('visibility') != 'hidden') {
                that.elements.container.fade('out')
            }
        }.delay(500);
    },

    onMouseOver: function() {
        $clear(this.wait);
    }
});
