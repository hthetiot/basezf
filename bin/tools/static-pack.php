#!/usr/bin/php5
<?php
# static-pack.php - A simple compressor bash script for CSS and JS files
#
# Usage:
# ./static-pack.php (js|css) <source_path> <dest_path>
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
class staticPack {

    CONST ADAPTER_YUICOMPRESSOR = 'yuicompressor';

    CONST ADAPTER_TIDYCSS = 'tidycss';

    static protected $_languageToAdapter = array(
        'js'    => self::ADAPTER_YUICOMPRESSOR,
        'css'   => self::ADAPTER_TIDYCSS,
    );

    static protected $_adapterConfig = array(
        self::ADAPTER_TIDYCSS => array(
            'name'      => 'TidyCss',
            'command'   => '{binPath}/csstidy {input} {params} {ouput}',
            'params'    => '--template=high --silent=false --merge_selectors=4',
        ),

        self::ADAPTER_YUICOMPRESSOR => array(
            'name'      => 'YuiCompressor',
            'command'   => 'java -jar {binPath}/yuicompressor.jar {params} {input} -o {ouput}',
            'params'    => '--charset UTF-8 --type js'
        ),
    );

    static protected $_rootPath;

    public function __construct($rootPath = null) {

        if (is_null($rootPath)) {
            $rootPath = realpath(dirname(__FILE__));
        }

        self::$_rootPath = $rootPath;
    }

    protected function _readConfig($configPath)
    {
        include(realpath(dirname(__FILE__)) . '/../../lib/Spyc.php');

        return spyc_load_file($configPath);
    }

    public function run($language, $configPath)
    {
        // read config
        $config = $this->_readConfig($configPath);

        foreach ($config as $pack => $files) {

            // is writable pack file ?
            echo  '    Compiling: "'. $pack . '"' . "\n";

            // add file
            foreach ($files as $file) {

                // is file ?
                echo '        Added "' . $file . '"' . "\n";
            }

            // process compression
            $this->process($language, $pack, $files);
        }
    }

    static protected function _createBufferForFiles(array $files)
    {
        $bufferData = '';
        foreach ($files as $file) {
            $buffer += file_get_contents(self::$_rootPath . $file);
        }

        $bufferFileName = tempnam(getcwd(), "static-pack-");
        file_put_contents($bufferFileName, $bufferData);

        return $bufferFileName;
    }

    public function process($language, $pack, $files)
    {
        $adapterConfig = self::_getLanguageAdapter($language);

        // create tmp buffer
        $bufferFile = self::_createBufferForFiles($files);

        echo '    Processing: compilation using ' . $adapterConfig['name'] . "\n";

        $commandVars =  array(
            '{binPath}'         => realpath(dirname(__FILE__)),
            '{input}'           => escapeshellarg($bufferFile),
            '{ouput}'           => escapeshellarg(self::$_rootPath . $pack),
            '{params}'          => $adapterConfig['params'],
        );

        $command = str_replace(
           array_keys($commandVars),
           array_values($commandVars),
           $adapterConfig['command']
        );

        if($results = exec($command)) {
            throw new Exception(sprintf('compilation error "%s"', $results));
        }

        // create tmp buffer
        unlink($bufferFile);

        echo '    Done' . "\n";
    }

    static function _getLanguageAdapter($language)
    {
        // is language with adapter
        if (!isset(self::$_languageToAdapter[$language])) {
            throw new Exception(sprintf('Bad language for value %s', $language));
        }

        // adapter exist
        if (!isset(self::$_adapterConfig[self::$_languageToAdapter[$language]])) {
            throw new Exception(sprintf('No adapter found for language value %s', $language));
        }

        return self::$_adapterConfig[self::$_languageToAdapter[$language]];
    }
}

function usage()
{
    echo "Usage: \n";
    echo "  {$_SERVER['argv'][0]} <language> <config_path> <root_path>\n";
    echo "where:\n";
    echo "  language  - \n";
    echo "  config_path  - \n";
    echo "  root_path - \n";
    exit;
}

// handle missing agruments
if( count($_SERVER['argv']) < 3 ) {
    usage();
    return;
}

// get args as vars
$language = $_SERVER['argv'][1];
$configPath = $_SERVER['argv'][2];
$rootPath = $_SERVER['argv'][3];

try {

    // init class
    $staticPack = new staticPack($rootPath);
    $staticPack->run($language, $configPath);

} catch (Exception $e) {

    echo "Error: \n";
    echo '  ' . $e->getMessage() . "\n";
    exit(1);
}
