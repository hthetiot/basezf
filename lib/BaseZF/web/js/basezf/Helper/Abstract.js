/**
 * Helper.js
 *
 *
 * @category   BaseZF_JS_Helper
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

if (typeof BaseZF == "undefined") var BaseZF = {};
if (typeof BaseZF.Helper == "undefined") BaseZF.Helper = {};

/**
 * Helper abstract class
 */
BaseZF.Helper.Abstract = new Class({

    Implements: [Options],

    options: {
        debug: true
    },

    initialize: function(initializeName, initializeArgs, options) {

        // init helper
        this.initHelper();

        var initializeArgs = $A(arguments).erase(initializeName);
        var initializeFunc = 'initialize-' + initializeName;

        try {
            eval('this.' + initializeFunc.camelCase() + '.run(initializeArgs, this);');
        } catch (e) {
            if (this.options.debug) {
                alert('Helper Error : ' + initializeFunc.camelCase() + '/' + e.message);
            }
        }
    },

    initHelper: function() {

    },

    getOptionFromElement: function(element, nameSpace) {

        if (!element.getProperty('data')) {
            if (!element.getProperty('rel')) {
            return $H();
            } else {
            var data = element.getProperty('rel');
            }
        } else {
            var data = element.getProperty('data');
        }

        eval('var options = $H(' + data.toString() + ');');

        if ($type(nameSpace)) {
            if (options.has(nameSpace)) {
                return options.get(nameSpace);
            } else {
                return $H();
            }
        }

        return options;
    },

    getOptionFromElementClass: function(element, classOptions)
    {
        var newOptions = $H();

        $H(classOptions).each(function(value, key){
            if(element.hasClass(key)) {
                newOptions = $merge(newOptions, value);
            }
        });

        return newOptions;
    }
});


// set default helper root
BaseZF.Helper.Root = document;


/**
 * Helper run class
 */
BaseZF.Helper.run = new Class({

    initialize: function(root) {

        if ($type(root)) {
            this.setRootElement(root);
        }

        $H(BaseZF.Helper).each( function (helper, name) {

            // exclude abstract className
            if (typeof(helper) == 'function' && !name.test('Abstract') && !name.test('run')) {

                helperClass = new helper('selector');
                delete(helperClass);
            }
        });

        this.setDefaultRootElement();
    },

    setDefaultRootElement: function()
    {
        BaseZF.Helper.Root = document;
    },

    setRootElement: function(root)
    {
        BaseZF.Helper.Root = root;
    }
});

// launch at domready
window.addEvent('domready', function() {
    new BaseZF.Helper.run();
});
