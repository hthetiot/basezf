#!/usr/bin/php
<?php
# config-generator.php - a simple config generator from config template
#
# Usage:
# ./config-generator.php [configure|show] (config_destination_dir) (config_source_dir)
#
# Config file example:
# <code>
# # Description: My sample config
# #
# # Config template vars:
# # $PROJECT_PATH: Your project path (example: /home/jhondoe/project/basezf)
# #
#
# $PROJECT_PATH
# </code>
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
class configGenerator {

    /**
     *
     */
    const CONFIG_TEMPLATE_PREFFIX = '-dist';

    /**
     *
     */
    const CONFIG_TEMPLATE_PARSOR = '/^# ([A-Z$_]*): ([^\[]*)(\[default\: (.*)\])?/';

    /**
     *
     */
    protected $_destDir;

    /**
     *
     */
    protected $_tplDir;

    /**
     *
     */
    public function __construct($destDir, $tplDir)
    {
	// check params
	if (!is_dir($destDir)) {
	    throw new Exception('Non existing config destination dir "' . $destDir . '"');
	}

	if (!is_dir($tplDir)) {
	    throw new Exception('Non existing config template dir "' . $tplDir . '"');
	}

	$this->_destDir = $destDir;
	$this->_tplDir = $tplDir;
    }

    /**
     * My prompt func because realine is not default module
     */
    public function ask($string, $length = 1024)
    {
	static $tty;

	if (!isset($tty)) {

	    if (substr(PHP_OS, 0, 3) == "WIN") {
		$tty = fOpen("\con", "rb");
	    } else {
		if (!($tty = fOpen("/dev/tty", "r"))) {
		    $tty = fOpen("php://stdin", "r");
		}
	    }
	}

	echo $string;
	$result = trim(fGets($tty, $length));
	return $result;
    }

    /**
     * àtodo add file/dir params support
     */
    public function configure()
    {
	$tplFiles = glob($this->_tplDir . '/*' . self::CONFIG_TEMPLATE_PREFFIX);

	// display help
	echo 'Notice: tape "skip" to skip file, press Enter for default value.' . "\n";

	$filesVarsValues = array();
	foreach ($tplFiles as $tplFile) {

	    $destFile = str_replace(array($this->_tplDir, self::CONFIG_TEMPLATE_PREFFIX), array($this->_destDir, ''), $tplFile);
	    $fileVarsValues = array();
	    $fileVars = $this->_getTemplateVars($tplFile);

	    // override current destination file
	    if (is_file($destFile)) {

		$buffer = null;
		while (!in_array(strtolower($buffer), array('y', 'n', 'skip'))) {

		    $buffer = $this->ask('Overvrite existing config file "' . $destFile . '" [y/N] ? ');

		    if (empty($buffer)) {
			$buffer = 'n';
		    }
		}

		// skip file $tplFile from iteration on $tplFiles
		if (in_array(strtolower($buffer), array('n', 'skip'))) {
		    continue;
		}
	    }

	    // display current destination file and help
	    echo 'Setup config file "' . $destFile . '"' . "\n";

	    // ask vars value to prompt
	    foreach ($fileVars as $fileVar => $data) {

		// build question
		$question = ' - ' . $data['question'];

		// add default notice in question
		if (!empty($data['default'])) {
		    $question .= ' [default: ' . $data['default'] . ']';

		// add default notice in question for same vars name
		} else if (isset($filesVarsValues[$fileVar])) {
		    $question .= ' [default: ' . $filesVarsValues[$fileVar]. ']';
		}

		$question .= ' ? ';

		// ask until is not empty and if not have default
		$buffer = null;
		while (empty($buffer)) {

		    $buffer = $this->ask($question);

		    // skip file
		    if (strtolower($buffer) == 'skip') {
			break(3);
		    //use default value
		    } else if (empty($buffer) && !empty($data['default'])) {
			$buffer = $data['default'];

		    // use previous value
		    } else if (empty($buffer) && isset($filesVarsValues[$fileVar])) {
			$buffer = $filesVarsValues[$fileVar];
		    }
		}

		$fileVarsValues[$fileVar] = $buffer;
	    }

	    $filesVarsValues = array_merge($filesVarsValues, $fileVarsValues);
	    $this->_generateTemplate($tplFile, $destFile, $fileVarsValues);
	}
    }

    /**
     *
     */
    public function show()
    {
    }

    //
    // Core functions
    //

    /**
     * Generate config file from template file
     *
     *
     */
    protected function _generateTemplate($tplFile, $destFile, $vars)
    {
	// check tpl file
	if (!is_file($tplFile) || !is_readable($tplFile)) {
	    throw new Exception('Unable to read template config file "' . $tplFile . '"');
	}

	// check dest file
	if ((is_file($destFile) && !is_writable($destFile)) || !is_writable(dirname($destFile))) {
	    throw new Exception('Unable to write new config file "' . $destFile . '"');
	}

	$fileContent = file_get_contents($tplFile);
	$fileContent = str_replace(array_keys($vars), array_values($vars), $fileContent);

	if (is_file($destFile)) {
	    // @todo add backup
	    echo 'Warning: config file "' . $destFile . '" overwritten' . "\n";
	}

	file_put_contents($destFile, $fileContent, LOCK_EX);

	return $this;
    }

    /**
     * Get dist fiel vars
     *
     * @param string $tplFile path to template file
     * @return array template vars with default value and question
     */
    protected function _getTemplateVars($tplFile)
    {
	// check file
	if (!is_file($tplFile) || !is_readable($tplFile)) {
	    throw new Exception('Unable to read template config file "' . $tplFile . '"');
	}

	// get vars and questions
	$fileVars = array();
	$fileLines = file($tplFile);
	foreach ($fileLines as $line) {

	    $matches = array();
	    if (preg_match(self::CONFIG_TEMPLATE_PARSOR, $line, $matches)) {
		$fileVars[$matches[1]] = array(
		    'question'  => trim($matches[2]),
		    'default'   => (isset($matches[4]) ? $matches[4] : null),
		);
	    }
	}

	return $fileVars;
    }
}

/**
 * Script usage
 */
function usage()
{
    echo "Usage: \n";
    echo "  {$_SERVER['argv'][0]} [configure|show] (config_destination_dir) (config_source_dir)\n";
    echo "where:\n";
    echo "  configure       - build configs from templates\n";
    echo "  show            - show config variables values\n";
    exit;
}

// handle missing agruments
if( count($_SERVER['argv']) < 4 ) {
    usage();
    return;
}

// get args as vars
$destDir = $_SERVER['argv'][2];
$tplDir = $_SERVER['argv'][3];

try {

    // init class
    $configGenerator = new configGenerator($destDir, $tplDir);

    // handle possible first agruments
    switch ($_SERVER['argv'][1]) {

	case 'configure':
	{
	    $configGenerator->configure();
	    break;
	}

	case 'show':
	{
	    $configGenerator->show();
	    break;
	}

	default : usage();
    }

    exit(0);

} catch (Exception $e) {

    echo "Error: \n";
    echo '  ' . $e->getMessage() . "\n";
    exit(1);
}


