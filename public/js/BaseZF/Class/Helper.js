/**
 * Helper.js
 *
 *
 * @category   Bahu
 * @package    Bahu.JS_Core
 * @copyright  Copyright (c) 2008 Bahu
 * @author     Harold Thétiot (hthetiot)
 */

if (typeof BaseZF == "undefined") var BaseZF = {};
if (typeof BaseZF.Class == "undefined") BaseZF.Class = {};
if (typeof BaseZF.Helper == "undefined") BaseZF.Helper = {};

/**
 * Helper abstract class
 */
BaseZF.Class.Helper = new Class({

    Implements: [Options],

    options: {
        debug: true
    },

    initialize: function(launcherName, launcherArgs, options) {

        // init helper
        this.initHelper();

        var launcherArgs = $A(arguments).erase(launcherName);
        var launcherFunc = 'launcher-' + launcherName;

        try {
            eval('this.' + launcherFunc.camelCase() + '.run(launcherArgs, this);');
        } catch (e) {
            if (this.options.debug) {
                alert('Helper Error : ' + launcherFunc.camelCase() + '/' + e.message);
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
BaseZF.Class.Helper.Root = document;


/**
 * Helper run class
 */
BaseZF.Class.Helper.run = new Class({

    initialize: function(root) {

        if ($type(root)) {
            this.setRootElement(root);
        }

        $H(BaseZF.Helper).each( function (helper, name) {
            if (typeof(helper) == 'function') {
                helperClass = new helper('selector');
                delete(helperClass);
            }
        });

        this.setDefaultRootElement();
    },

    setDefaultRootElement: function()
    {
        BaseZF.Class.Helper.Root = document;
    },

    setRootElement: function(root)
    {
        BaseZF.Class.Helper.Root = root;
    }
});

// launch at domready
window.addEvent('domready', function() {
	new BaseZF.Class.Helper.run();
});
