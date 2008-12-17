/**
 * BaseZF.js
 *
 * @category   BaseZF_JS
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

if (typeof BaseZF == "undefined") var BaseZF = {};
if (typeof BaseZF.Class == "undefined") BaseZF.Class = {};
if (typeof BaseZF.Helper == "undefined") BaseZF.Helper = {};

// Some vars
BaseZF.version = '1.0';
BaseZF.debug = false;

// Debug and log function
BaseZF.log = function() {

}

// Js GetText handler
if (typeof _ == "undefined") {
  _ = function(s) {
    return s
  };
}
