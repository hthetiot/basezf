#!/usr/bin/php5
<?php
# gettext-translator.php - an automatic gettext translator power by google adapter by default
#
# Usage:
# ./gettext-translator.php input_language output_language input_po_file output_po_file (adapter)
#
# @copyright  Copyright (c) 2008 BaseZF
# @author     Harold Thétiot (hthetiot)
# @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

// disable time limit and upgrade memory limit
set_time_limit(0);
ini_set('memory_limit', '256M');

/**
 * Main Class
 */
class gettextTranslator {

    protected $_fileHeader = array();
    protected $_fileData = array();
    protected $_fileStats = array();

    public function __construct() {

    }

    public function getFileStats()
    {
        return $this->_fileStats;
    }

    public function loadFile($gettextFile)
    {
        // reset data
        $this->_fileData = array();
        $this->_fileHeader = array();
        $this->_fileStats = array(
            'untranslated' => 0,
            'translated'   => 0,
            'fuzzy'        => 0,
        );

        if (!is_file($gettextFile)) {
            throw new Exception(sprintf('unable to found file "%s"', $gettextFile));
        }

        $lines = file($gettextFile);
        $lines[] = ''; // Adds a blank line at the end in order to ensure complete handling of the file

        $status='-';
        $matches = array();
        $sources = array();
        $isFuzzy = false;

        $msgid = '';
        $msgstr = '';

        foreach ($lines as $nbline => $line) {

            if(trim($line) == '' ) {

                // Blank line, go back to base status:
                if($status == 't' && !empty($msgid)) {

                    // End of a translation
                    if(empty($msgstr)) {
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
            } elseif(($status == '-') && preg_match( '#^msgid "(.*)"#', $line, $matches)) {

                $status = 'o';
                $msgid = $matches[1];
                $this->_fileStats['translation']++;

            // Encountered a translated text
            } elseif(($status == 'o') && preg_match( '#^msgstr "(.*)"#', $line, $matches)) {

                $status = 't';
                $msgstr = $matches[1];

            // Encountered a translated text
            } elseif(($status == 'o') && preg_match( '#^msgstr ""#', $line, $matches)) {

                $status = 't';
                $msgstr = '';

            // Encountered a followup line
            } elseif(preg_match('/^"(.*)"/', $line, $matches)) {

                if ($status == 'o') {
                    $msgid .= "\n" .$matches[1];
                } elseif ($status=='t') {
                    $msgstr .= "\n" . $matches[1];
                }

            // Encountered a source code location comment
            } elseif(($status == '-') && preg_match( '@^#:(.*)@', $line, $matches)) {

                $sources[] = trim($matches[1]);
            } elseif(($status == '-') && preg_match( '@^#(.*)@', $line, $matches) && empty($this->_fileData)) {

                $this->_fileHeader[] = $matches[1];

            } elseif(strpos($line,'#, fuzzy') === 0) {
                $this->_fileStats['fuzzy']++;
                $isFuzzy = true;
            }
        }
    }

    public function saveFile($newGettextFile)
    {
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
                    $firstMsgstr = false;
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

        file_put_contents($newGettextFile, implode("\n", $fileBuffer), LOCK_EX);
    }

    public function translate($inputLanguage, $outputLanguage, $adapter = 'google', $translateAll = false)
    {
        if (empty($this->_fileData)) {
            throw new Exception('Unable to translate an empty file');
        }

        $adapterFuncName = '_translateAdapter' . ucfirst(strtolower($adapter));
        if (!is_callable(array($this, $adapterFuncName))) {
            throw new Exception(sprintf('Unable to use adapter %s, missing func %s', $adapter, $adapterFuncName));
        }

        foreach ($this->_fileData as &$data) {

            if ((empty($data['msgstr']) || $translateAll == true) && !empty($data['msgid'])) {

                $newMsgstr = $this->$adapterFuncName($data['msgid'], $inputLanguage, $outputLanguage);

                if (!empty($newMsgstr)) {

                    $data['msgstr'] = $newMsgstr;
                    $this->_fileStats['translated']++;
                    $this->_fileStats['untranslated']--;

                    if (!$data['fuzzy']) {
                        $this->_fileStats['fuzzy']++;
                    }

                    $data['fuzzy'] = true;
                }
            }
        }
    }

    //
    // Adapters
    //

    /**
     *
     */
    static protected function _translateAdapterGoogle($string, $inputLanguage, $outputLanguage)
    {
        $url = "http://translate.google.com/translate_t?langpair=" . urlencode($inputLanguage . '|' . $outputLanguage)."&amp;";
        $data = "text=" . urlencode($string);
        $results = '';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $html = curl_exec($ch);

        // empty if error
        if(curl_errno($ch)) {
            return '';
        }

        curl_close ($ch);

        // find results
        $html = substr($html, strpos($html, "<div id=result_box dir=\"ltr\">"));
        $html = substr($html, 29);
        $html = substr($html, 0, strpos($html, "</div>"));

        // clean results
        $search = array('<br>', '&#39;');
        $replace = array("\n", "'");
        $results = str_replace($search, $replace, $html);

        return $results;
    }
}

/**
 * Script usage
 */
function usage()
{
    echo "Usage: \n";
    echo "  {$_SERVER['argv'][0]} input_language output_language input_po_file output_po_file (adapter)\n";
    echo "where:\n";
    echo "  input_language  - input language for translator (example: en, fr, it, ...)\n";
    echo "  output_language - output language for translator (example: en, fr, it, ...)\n";
    echo "  input_po_file   - intput po or pot file\n";
    echo "  output_po_file  - output po file\n";
    echo "  adapter         - translator adapter, currenlty only google is available\n";
    exit;
}

// handle missing agruments
if( count($_SERVER['argv']) < 3 ) {
    usage();
    return;
}

// get args as vars
$input_language = $_SERVER['argv'][1];
$output_language = $_SERVER['argv'][2];
$input_po_file = $_SERVER['argv'][3];
$output_po_file = $_SERVER['argv'][4];
$adapter = (empty($_SERVER['argv'][5]) ? 'google' : $_SERVER['argv'][5]);

try {

    // init class
    $gettextTranslator = new gettextTranslator();

    echo '- Load gettext file "' . $input_po_file . '": ';
    $gettextTranslator->loadFile($input_po_file);
    echo "done \n";

    // get stats
    $beforeTranslationStats = $gettextTranslator->getFileStats();
    $beforeTranslation = time();

    echo '- Translate file from "' . $input_language . '" to "' . $output_language . '" with adapter "' . ucfirst($adapter) . '": ';
    $gettextTranslator->translate($input_language, $output_language, $adapter);
    echo "done \n";

    // get stats
    $afterTranslation = time();
    $afterTranslationStats = $gettextTranslator->getFileStats();

    echo '- Save Translated gettext file "' . $output_po_file . '": ';
    $gettextTranslator->saveFile($output_po_file);
    echo "done \n";

    echo '- Translation Stats for "' . $output_po_file . '": ' . "\n";
    echo '  * Translation duration ' . ($afterTranslation - $beforeTranslation) . ' seconds' . "\n";
    foreach ($afterTranslationStats as $stats => $value) {
        echo '  * ' . ucfirst($stats) . ' = ' . $value . "\n";
    }


} catch (Exception $e) {

    echo "Error: \n";
    echo '  ' . $e->getMessage() . "\n";
    exit(1);
}

