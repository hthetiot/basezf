<?php
/**
 * Array class in /BazeZF/Service/GetText/Translator
 *
 * @category   BazeZF
 * @package    BazeZF_Service_GetText
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
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
