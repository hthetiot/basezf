<?php
/**
 * Abstract class in /BazeZF/Service/GetText/Translator
 *
 * @category   BazeZF
 * @package    BazeZF_Service_GetText
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Service_GetText_Translator_Abstract extends BaseZF_Service_GetText_Parsor
{
    abstract public function translate($toLocale, $hasFuzzy = true, $translateAll = false);
}
