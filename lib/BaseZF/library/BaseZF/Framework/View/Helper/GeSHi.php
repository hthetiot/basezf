<?php
/**
 * BaseZF_Framework_View_Helper_GeSHi class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

// Include GeSHi Engine
require_once('geshi.php');

class BaseZF_Framework_View_Helper_GeSHi extends BaseZF_Framework_View_Helper_Abstract
{
    protected static $_geshiStylesheet = array();

    protected static $_geshiInstance;

    public function GeSHi($source, $language, $lineNumbers = false)
    {
        // little singleton pattern for GeSHi Class
        if (!isset(self::$_geshiInstance)) {

            require_once 'geshi.php';
            $geshi = self::$_geshiInstance = new GeSHi();
            $geshi->enable_classes();

        } else {
            $geshi = self::$_geshiInstance;
        }

        // set data for geshi
        $geshi->set_source($source);
        $geshi->set_language($language);

        if ($lineNumbers) {
            $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
        } else {
            $geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);
        }

        $xhtml = array();
        $value = $geshi->parse_code();

        if ($value !== false && in_array($language, self::$_geshiStylesheet) === false) {
            self::$_geshiStylesheet[] = $language;
            $this->view->headStyle()->appendStyle($geshi->get_stylesheet());
        }

        $xhtml[] = $value;

        return implode("\n", $xhtml);
    }
}

