<?php
/**
 * Abstract class in /BazeZF/Service/GetText/Translator
 *
 * @category   BazeZF
 * @package    BazeZF_Service_GetText
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

abstract class BaseZF_Service_GetText_Translator_Abstract extends BaseZF_Service_GetText_Parsor
{
    public function translate($options, $hasFuzzy = true, $translateAll = false)
    {
        $this->_resetFileStats();

        foreach ($this->_fileData as $index => $data) {

            $this->_fileStats['translation']++;

            if (
                ($translateAll || mb_strlen($data['msgstr']) == 0) &&
                mb_strlen($data['msgid']) > 0
            ) {

                $translatedData = $this->_translateData($data, $options);

                if ($translatedData != $data) {
                    $translatedData['fuzzy'] = $hasFuzzy;
                    $this->_fileStats['translated']++;
                } else {
                    $this->_fileStats['untranslated']++;
                }

                $this->_fileData[$index] = $translatedData;
            }
        }

        return $this;
    }

    abstract protected function _translateData($data, $options);
}
