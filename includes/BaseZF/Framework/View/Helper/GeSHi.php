<?php
/**
 * GeSHi.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_GeSHi extends BaseZF_Framework_View_Helper_Abstract
{
    static $_GESHI_STYLESHEET = array();

    static $_GESHi_INSTANCE = null;

    public function GeSHi($source = '', $language = '', $lineNumbers = false)
    {
        // little singleton pattern for GeSHi Class
        if (self::$_GESHi_INSTANCE === null) {

            require_once(PATH_TO_LIBRARY . '/geshi.php');
            $geshi = self::$_GESHi_INSTANCE = new GeSHi();
            $geshi->set_language_path(PATH_TO_LIBRARY . '/geshi');
            $geshi->enable_classes();

        } else {
            $geshi = self::$_GESHi_INSTANCE;
        }

        if (!empty($source)) {
            $geshi->set_source($source);
        }

        if (!empty($language)) {
            $geshi->set_language($language);
        }

        if ($lineNumbers) {
            $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
        } else {
            $geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);
        }

        $xhtml = array();
        $value = $geshi->parse_code();

        if ($value !== false && in_array($language, self::$_GESHI_STYLESHEET) === false) {
            self::$_GESHI_STYLESHEET[] = $language;
            $xhtml[] = '<style>';
            $xhtml[] = $geshi->get_stylesheet();
            $xhtml[] = '</style>';
        }

        $xhtml[] = $value;

        return implode("\n", $xhtml);
    }
}

