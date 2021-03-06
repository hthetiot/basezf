<?php
/**
 * BaseZF_Service_GetText_Translator_Array class in /BazeZF/Service/GetText/Translator
 *
 * @category  BazeZF
 * @package   BazeZF_Service_GetText
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Service_GetText_Translator_Array extends BaseZF_Service_GetText_Translator_Abstract
{
    protected function _translateData($data, $options)
    {
        if (array_key_exists($data['msgid'], $options)) {
            $data['msgstr'] = $options[$data['msgid']];
        }

        return $data;
    }
}
