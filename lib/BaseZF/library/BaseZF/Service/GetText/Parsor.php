<?php
/**
 * Parsor class in /BazeZF/Service/GetText
 *
 * @category   BazeZF
 * @package    BazeZF_Service_GetText
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Service_GetText_Parsor
{
    /**
     *
     */
    protected $_filePath;

    /**
     *
     */
    protected $_fileHeader = array();

    /**
     *
     */
    protected $_fileData = array();

    /**
     *
     */
    protected $_fileStats = array();

    /**
     *
     */
    public function __construct($filePath)
    {
        $this->loadFile($filePath);
    }

    /**
     *
     */
    public function getFileStats()
    {
        return $this->_fileStats;
    }

    /**
     *
     */
    public function getFileHeader()
    {
        return $this->_fileHeader;
    }

    /**
     *
     */
    public function getFileData()
    {
        return $this->_fileData;
    }

    /**
     *
     */
    public function setFileData(array $fileData)
    {
        $this->_fileData = $fileData;

        return $this;
    }

    /**
     *
     */
    public function setFilePath($filePath)
    {
        $this->_filePath = $filePath;
        $this->_fileData = array();
        $this->_fileHeader = array();
        $this->_resetFileStats();

        return $this;
    }

    protected function _resetFileStats()
    {
        // reset data
        $this->_fileStats = array(
            'untranslated' => 0,
            'translated'   => 0,
            'fuzzy'        => 0,
            'translation'  => 0,
        );
    }

    /**
     *
     */
    public function loadFile($filePath)
    {
        $this->setFilePath($filePath);

        if (!is_file($filePath)) {
            throw new BaseZF_Service_GetText_Exception(sprintf('unable to found file "%s"', $gettextFile));
        }

        $lines = file($filePath);
        $lines[] = ''; // Adds a blank line at the end in order to ensure complete handling of the file

        $status='-';
        $matches = array();
        $sources = array();
        $isFuzzy = false;

        $msgid = '';
        $msgstr = '';

        foreach ($lines as $nbline => $line) {

            if (trim($line) == '') {

                // Blank line, go back to base status:
                if ($status == 't' && !empty($msgid)) {

                    // End of a translation
                    if (empty($msgstr)) {
                         $this->_fileStats['untranslated']++;
                    } else {
                        $this->_fileStats['translated']++;
                    }
                }

                // store only if has values
                if (!empty($msgid) || !empty($msgstr)) {
                    $this->_fileData[] = array(
                        'msgid'     => $msgid,
                        'msgstr'    => $msgstr,
                        'fuzzy'     => $isFuzzy,
                        'sources'   => $sources,
                    );
                }

                $status = '-';
                $msgid = '';
                $msgstr = '';
                $isFuzzy = false;
                $sources = array();

            // Encountered an original text
            } elseif (($status == '-') && preg_match( '#^msgid "(.*)"#', $line, $matches)) {

                $status = 'o';
                $msgid = $matches[1];
                $this->_fileStats['translation']++;

            // Encountered a translated text
            } elseif (($status == 'o') && preg_match( '#^msgstr "(.*)"#', $line, $matches)) {

                $status = 't';
                $msgstr = $matches[1];

            // Encountered a translated text
            } elseif (($status == 'o') && preg_match( '#^msgstr ""#', $line, $matches)) {

                $status = 't';
                $msgstr = '';

            // Encountered a followup line
            } elseif (preg_match('/^"(.*)"/', $line, $matches)) {

                if ($status == 'o') {
                    $msgid .= "\n" .$matches[1];
                } elseif ($status=='t') {
                    $msgstr .= "\n" . $matches[1];
                }

            // Encountered a source code location comment
            } elseif (($status == '-') && preg_match( '@^#:(.*)@', $line, $matches)) {

                $sources[] = trim($matches[1]);
            } elseif (($status == '-') && preg_match( '@^#(.*)@', $line, $matches) && empty($this->_fileData)) {

                $this->_fileHeader[] = $matches[1];

            } elseif (strpos($line, '#, fuzzy') === 0) {
                $this->_fileStats['fuzzy']++;
                $isFuzzy = true;
            }
        }

        return $this;
    }

    /**
     *
     */
    public function saveFile($newFilePath = null)
    {
        if (is_null($newFilePath)) {
            $newFilePath = $this->_filePath;
        }

        $fileBuffer = array();

        // add header
        foreach ($this->_fileHeader as $data) {
            $fileBuffer[] = '# ' . $data;
        }

        foreach ($this->_fileData as $data) {

            // add source
            foreach ($data['sources'] as $source) {
                $fileBuffer[] = '#: ' . $source;
            }

            // fuzzy
            if ($data['fuzzy']) {
                $fileBuffer[] = '#, fuzzy';
            }

            // add msgid
            $realMsgid = explode("\n", $data['msgid']);
            $firstMsgid = true;
            foreach ($realMsgid as $msgid) {
                if ($firstMsgid) {
                    $fileBuffer[] = 'msgid "' . $msgid . '"';
                    $firstMsgid = false;
                } else {
                    $fileBuffer[] = '"' . $msgid . '"';
                }
            }

            // add msgstr
            $realMsgstr = explode("\n", $data['msgstr']);
            $firstMsgstr = true;
            foreach ($realMsgstr as $msgstr) {
                if ($firstMsgstr) {
                    $fileBuffer[] = 'msgstr "' . $msgstr . '"';
                    $firstMsgstr = false;
                } else {
                    $fileBuffer[] = '"' . $msgstr . '"';
                }
            }

            $fileBuffer[] = '';
        }

        // check for write
        if (!is_file($newFilePath) && !is_writable(dirname($newFilePath))) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to create PO file on path "%s"', dirname($newFilePath)));
        } else if (!is_writable($newFilePath)) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to write PO file on path "%s"', $newFilePath));
        }

        file_put_contents($newFilePath, implode("\n", $fileBuffer), LOCK_EX);

        return $this;
    }
}
