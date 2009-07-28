<?php
/**
 * Copyright (c) 2009  Radu Gasler <miezuit@gmail.com>
 *
 *  This file is free software: you may copy, redistribute and/or modify it
 *  under the terms of the GNU General Public License as published by the
 *  Free Software Foundation, either version 2 of the License, or (at your
 *  option) any later version.
 *
 *  This file is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 *
 * Dynamic test runner for PHPUnit
 *
 * This script does not use templates, since it should depend only
 * on PHPUnit and the presence of testcase files.
 *
 * $Id: index.php 2 2009-03-06 11:11:06Z miezuit $
 *
 * $Rev: 2 $
 *
 * $LastChangedBy: miezuit $
 *
 * $LastChangedDate: 2009-03-06 13:11:06 +0200 (V, 06 mar. 2009) $
 *
 * @author Radu Gasler <miezuit@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html     GPL License
 * @version 0.1
 */

ini_set('display_errors', 'On');

error_reporting(E_ALL | E_STRICT);

require_once 'config.php';
require_once 'functions.php';
require_once 'lib/FolderParser.php';

if(defined('PHPUNIT_DIR')) {
    set_include_path(get_include_path() . PATH_SEPARATOR . PHPUNIT_DIR);
}

// Definitions:
define('RUN_SELECTED_LABEL', 'Run selected');
define('SELECT_ALL_LABEL', 'Select All');
define('CLEAR_ALL_LABEL', 'Clear All');
define('RESET_TESTER_LABEL', 'Reload Tests');


// start the session
session_start();

// reset tester
if(isset($_REQUEST['reset']) && ('true' === $_REQUEST['reset'])) {
    session_destroy();
    session_start();
    // we will not run any suites in this case
    unset($_REQUEST['runSuites']);
}

// create the folderParser object
$folderParser = new FolderParser(ROOT_DIR, $exclude);

// Process the request:

$runSuites = array();
$testCase = null;

if (isset($_REQUEST['runSuites']) && is_array($_REQUEST['runSuites'])) {
    $runSuites = $_REQUEST['runSuites'];
}

if (isset($_REQUEST['keepOpen'])) {
    $keepOpen = trim($_REQUEST['keepOpen']);
} else {
    $keepOpen = '';
}
$keepOpen = explode(' ', $keepOpen);

// if is set code coverage
if (isset($_REQUEST['coverage']) && 'true' === $_REQUEST['coverage']) {
    // get the group name and the file name
    $suite = $runSuites[0];

    // set the file name
    $file = $folderParser->getSuitePath($suite);

    // set the class name
    $className = getClassName($file);

    // set the output dir for the code coverage
    $relativePath       = str_replace(ROOT_DIR . '/', '', $file);
    $codeCoveragePath   = str_replace(TEST_SUFFIX . '.php', '', $relativePath);
    $codeCoverageTarget = str_replace(TEST_SUFFIX, '', $className) . '.php.html';

    $codeCoverageDir = CODE_COVERAGE_DIR . '/' . $codeCoveragePath;

    $command = PHPUNIT . " --coverage-html $codeCoverageDir $className $file";
    //echo $command; die();

    // run the code coverage
    exec($command);

    // redirect to the code coverage dir
	if (isAjax()){
	echo file_get_contents(basename(CODE_COVERAGE_DIR) . "/$codeCoveragePath/" . PRECEDE_TESTS.$codeCoverageTarget);
    }else{
	header(sprintf("Location: " . basename(CODE_COVERAGE_DIR) . "/$codeCoveragePath/" . PRECEDE_TESTS.$codeCoverageTarget));
    }

//    echo('Loading...');
    exit(0);
}

if (isAjax()) {
    // action can be: 'START', 'CONTINUE' or 'STOP'
    if (isset($_GET['action'])) {
	switch($_GET['action'])
	{
	    case 'START':
		if(0 == initializeSuites()) {
		    printf('0'); // no tests to run
		}
	    break;

	    case 'CONTINUE':
		list($testClass, $testName, $testResult) = runCurrentTestCase();
		incrementSuite();
		printStatus($testClass, $testName, $testResult);
	    break;

	    case 'STOP':
		stopTestsAndDisplay();
	    break;
	}
    }
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Test Suites</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<link href="css/container.css" rel="stylesheet" type="text/css" />
<link href="css/coveragestyle.css" rel="stylesheet" type="text/css" />

<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/contextMenu.css" rel="stylesheet" type="text/css" />
<script src="js/jquery-1.2.6.js" type="text/javascript"></script>
<script src="js/jquery.contextMenu.js" type="text/javascript"></script>
<script src="js/functions.js" type="text/javascript"></script>

<script src="js/yahoo-dom-event.js" type="text/javascript"></script>
<script src="js/container-min.js" type="text/javascript"></script>
<script>
    var useAjax = <?php echo USE_AJAX ? 'true' : 'false' ?>;
    $(document).ready(onDocumentLoad);
</script>
</head>
<body>
<!-- Context menu -->
<ul id="myContextMenu" class="contextMenu">
    <li class="menuRun">
	<a href="#menuRun"><?php echo RUN_SELECTED_LABEL; ?></a>
    </li>
    <li class="menuReload">
	<a href="#menuReload"><?php echo RESET_TESTER_LABEL; ?></a>
    </li>
</ul>
<!-- Main menu and form -->
<div id="menu">
<form id="menuForm" method="post" action="./">
<?php
    if($folderParser->getRootFolderId() !== 0) {
	displayMenu($folderParser, $folderParser->getRootFolderId());
    } else {
	echo 'No tests found. Please check out your config file.<br /><br />';
    }
?>
<input type="hidden" name="keepOpen" id="keepOpen" value="<?php echo $keepOpen ?>"/>
<input type="hidden" name="coverage" id="coverage" value="false" />
<input type="hidden" name="reset" id="reset" value="false" />
<div>
    <input class="button" style="width: 80px;" type="button" name="action" id="submitForm" value="<?php echo RUN_SELECTED_LABEL ?>" onclick="runTests()" />
</div>
<p>
    <input class="button" style="width: 80px;" type="button" name="action" id="resetButton" value="<?php echo RESET_TESTER_LABEL ?>" onclick="resetTester()" />
</p>
</form>
</div>
<!-- Test Results -->
<div id="status">
 <div id="loaderContainer"><div id="loader"></div></div>
 <table><tr><td><div id="runnedTest"></div></td><td><div id="testResult"></div></td></tr></table>
 <input class="button" style="width: 80px;" type="button" name="action" value="Stop" onclick="forceStop()" />
 <div id="buffer" style="display: none;"></div>
</div>
<div id="suites">

<?php
    flush();
    if (0 !== initializeSuites()) {
	while($_SESSION['runnedTests'] < $_SESSION['testCount']) {
	    list($testClass, $testName) = runCurrentTestCase();
	    incrementSuite();
	}
	stopTestsAndDisplay();
    }
?>
</div>
	<div id="codeCoverage">
		<button onclick="closeCoverage();return false;">Close</button>
		<div id="subCodeCoverage"></div>
	</div>
<div style="display:none;z-index: 155407;overflow:auto; background-color: red; position: absolute; top: 400px; width: 100%; height: 100%; opacity: 0.7; color:#000000"
	id="teste">
aici facem testetle
</div>
</html>
<?php
}
/*EOF*/
