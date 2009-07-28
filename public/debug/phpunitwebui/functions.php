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
 * Functions used by TestRunner
 *
 * $Id: functions.php 2 2009-03-06 11:11:06Z miezuit $
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

/**
 * Function that returns the length of the longest value of an array of strings.
 *
 * @param array of strings
 * @return int length of the longest value
 */

function maxLength($array) {
    array_walk($array, create_function('&$a', '$a = strlen($a);'));
    return max($array);
}

/**
 * Displays the menu form fields.
 *
 * @param FolderParser $folderParser folderParser object
 * @param string $folderId folder id
 */
function displayMenu($folderParser, $folderId)
{
    global $runSuites;

    $folder = $folderParser->getFolder($folderId);
    if(isset($folder['subfolders'])) {
	foreach($folder['subfolders'] as $subId) {

	    $subfolder = $folderParser->getFolder($subId);

	    $dir = str_replace(ROOT_DIR, '', $folder);
	    // set the display state:
	    $display = 'none';
	    $arrow = 'collapsed.png';
	    foreach($runSuites as $suiteId => $tests) {
		if(strstr($suiteId, $dir)) {
		    $display = 'block';
		    $arrow = 'expanded.png';
		    break;
		}
	    }

	    printf('<div class="groupName" onclick="toggleGroup(\'%s\')"><img id="arrow_%s" class="arrow" src="images/%s"/></span> %s</div>', $subId, $subId, $arrow, basename($subfolder['path']));
	    printf('<div class="group" id="%s" style="display: %s">', $subId, $display);
	    displayMenu($folderParser, $subId);
	    printf('</div>');
	}
    }

    if(isset($folder['suites'])) {
	foreach ($folder['suites'] as $suiteId) {
	    $suite = $folderParser->getSuite($suiteId);
	    // suite name is the short file name (with suffix detached)
	    $suiteName = str_replace(TEST_SUFFIX . '.php', '', basename($suite['path']));

	    // checked state
	    $checked = in_array($suiteId, $runSuites);

	    // Main box:
	    printf('<div class="suiteSelect"
				onclick="setCheckBox(\'%s\'); setCheckBoxes(\'%s\', document.getElementById(\'%s\').checked);"
				onmouseover="toggleTests(\'%s\', event, true);"
				onmouseout="toggleTests(\'%s\', event, false);"
				id="suiteSelect_%s"
				>', $suiteId, 'tests_' . $suiteId, $suiteId, $suiteId, $suiteId, $suiteId);

	    // checkbox for suite
	    printf('<input class="checkbox" onclick="this.checked = !this.checked;" type="checkbox" name="suite" id="%s" value="%s"%s/>', $suiteId, $suiteId, ($checked ? ' checked="checked"' : ''));
	    printf('<span class="checkboxFront%s"></span>', ($checked ? 'Set' : 'Unset'));

	    // arrow for toggling the suite tests box
	    printf('<img id="%s" class="arrow_suite" src="" fleoscmouseover="toggleTests(\'%s\', event, true);" fleoscmouseout="toggleTests(\'%s\', event, false);"/>', 'arrow_' . $suiteId, $suiteId, $suiteId);
	    printf('<span>');

	    if (in_array($suiteId, $runSuites)) {
		printf('<a href="#a_%s">%s</a>', $suiteId, $suiteName);
	    } else {
		echo $suiteName;
	    }
	    echo '</span>';
	    echo '</div>';

	    // Display the suite tests box:

	    // Set height and width of the tests box
	    $height = count($suite['tests']) * 17 + 43;
	    $width = max(maxLength($suite['tests']) * 8, 175);

	    // tests box
	    printf('<div id="%s" class="testSelectClosed" style="width: '. $width . 'px; height: ' . $height . 'px;" onmouseout="toggleTests(\'%s\', event, false);">', 'tests_' . $suiteId, $suiteId);
	    foreach($suite['tests'] as $test) {
		$testId = $suiteId . '@' . $test; // used the '@' string as delimiter
		// test box
		printf('<div class="testSelect" onclick="setCheckBox(\'%s\'); setCheckBoxSuite(\'%s\');">', $testId, $suiteId);

		// checkbox for test
		printf('<input class="checkbox" onclick="this.checked = !this.checked;" type="checkbox" name="runSuites[%s][]" id="%s" value="%s"%s/>', $suiteId, $testId, $test, ($checked ? ' checked="checked"' : ''));
		printf('<span class="checkboxFront%s"></span>', ($checked ? 'Set' : 'Unset'));

		echo $test;

		echo '</div>';
	    }
	    // buttons for select and clear all
	    printf('<p style="text-align: center;"><input class="button" style="width: 80px;" type="button" onclick="toggleAllTests(%s,%s);" value="%s"/>', "'$suiteId'", "false", CLEAR_ALL_LABEL);
	    echo '&nbsp;';
	    printf('<input class="button" style="width: 80px;" type="button" onclick="toggleAllTests(%s,%s);" value="%s"/></p>', "'$suiteId'", "true", SELECT_ALL_LABEL);
	    echo '&nbsp;';
	    echo '</div>';
	}
    }

    // buttons for select and clear all
    printf('<p><input class="button" style="width: 80px;" type="button" onclick="setCheckBoxes(%s,%s)" value="%s"/>',  "'$folderId'", "false", CLEAR_ALL_LABEL);
    echo '&nbsp;';
    printf('<input class="button" style="width: 80px;" type="button" onclick="setCheckBoxes(%s,%s)" value="%s"/></p>',  "'$folderId'", "true", SELECT_ALL_LABEL);
    echo '&nbsp;';
}

/**
 * Creates a new empty suite report array.
 *
 * @param string $suitePath suite path
 * @return array new empty suite report
 */

function getNewSuiteReport($suitePath)
{
    $suiteReport = array(
		       'suite'        => $suitePath,
		       'buffer'       => '',
		       'testsCount'   => 0,
		       'passedTests'  => array(),
		       'failedTests'  => array(),
		       'errorTests'   => array(),
		       'skippedTests' => array()
		   );
    return $suiteReport;
}

/**
 * Initialize the session variables that handle scheduled suites
 * and active test case to run
 *
 * It initializes the following session variables:
 *  $_SESSION['scheduledSuites'] // array of scheduled suites with test cases
 *  $_SESSION['scheduledSuite']  // current test suite number
 *  $_SESSION['scheduledTest']   // sheduled test number in current suite
 *  $_SESSION['testResults']     // all test results - array of suite reports
 *  $_SESSION['suiteReport']     // current suite report
 *  $_SESSION['runnedTests']     // runned tests count
 *  $_SESSION['testCount']       // total test count
 *
 * @return int number of test scheduled suites
 */
function initializeSuites()
{
    global $runSuites;
    global $testCase;
    global $folderParser;

    // create test schedule
    $_SESSION['testCount'] = 0;
    $_SESSION['scheduledSuites'] = array();
    $_SESSION['testResults'] = array();

    if (count($runSuites)) {
	// if run a single test case
	if (!is_null($testCase)) {
	    $suite = $runSuites[0];
	    $_SESSION['scheduledSuites'][] = array($suite => array($testCase));
	    $_SESSION['testCount'] = 1;
	} else {
	    foreach ($runSuites as $suiteId => $tests) {
		$_SESSION['scheduledSuites'][] = array($folderParser->getSuitePath($suiteId) => $tests);
		$_SESSION['testCount'] += count($tests);
	    }
	}
	$_SESSION['scheduledSuite'] = 0;
	$_SESSION['scheduledTest']  = 0;

	$_SESSION['runnedTests'] = 0;
    }

    return count($_SESSION['scheduledSuites']);
}

/**
 * Runs the current test case.
 *
 * @return array array(string $testClass, string $testName, string $testResult) executed test
 *               $testResult can be: passed, failed, error or skipped
 */
function runCurrentTestCase()
{
    $suite = key($_SESSION['scheduledSuites'][$_SESSION['scheduledSuite']]);
    $suiteTests = $_SESSION['scheduledSuites'][$_SESSION['scheduledSuite']][$suite];
    $testCase = $suiteTests[$_SESSION['scheduledTest']];

    // initialize suiteReport
    if($_SESSION['scheduledTest'] == 0) {
	$suiteReport = getNewSuiteReport($suite);
	$_SESSION['testResults'][] = $suiteReport;
    }
    $last = count($_SESSION['testResults']) - 1;
    $suiteReport = &$_SESSION['testResults'][$last];

    // run the test safe and create test case report
    $testCaseReport = runTestCaseRemote($suite, $testCase);

    $suiteReport['buffer'] .= $testCaseReport['buffer'];
    $suiteReport['testsCount']++;
    foreach(array('passedTests', 'failedTests', 'errorTests', 'skippedTests') as $testType) {
	if (count($testCaseReport[$testType])) {
	    $suiteReport[$testType][] = $testCaseReport[$testType][0];
	    $testClass = $testCaseReport[$testType][0]['name'];
	    $testName  = $testCaseReport[$testType][0]['class'];
	    $testResult = str_replace('Tests', '', $testType);
	    break;
	}
    }

    return array($testClass, $testName, $testResult);
}

/**
 * Increment counters for suite tests
 */
function incrementSuite()
{
    $suite = key($_SESSION['scheduledSuites'][$_SESSION['scheduledSuite']]);
    $suiteTests = $_SESSION['scheduledSuites'][$_SESSION['scheduledSuite']][$suite];

    // increment scheduled test
    $_SESSION['scheduledTest']++;
    $_SESSION['runnedTests']++;
    // if it was the last test of the suite
    if($_SESSION['scheduledTest'] == count($suiteTests)) {
	// increment scheduled suite
	$_SESSION['scheduledTest'] = 0;
	$_SESSION['scheduledSuite']++;
    }
}

/**
 * Stop tests and display the results
 *
 * @return void outputs directly
 */
function stopTestsAndDisplay()
{
    global $keepOpen;
    if (count($_SESSION['testResults'])) {
	displayResults($_SESSION['testResults'], $keepOpen);
    }
    resetTests();
}

/**
 * Resets testResults and sheduledSuites.
 */
function resetTests()
{
    $_SESSION['scheduledSuites'] = null;
    $_SESSION['testResults'] = null;
}

/**
 * In order to isolate the test from side effects we run it in a safe new instance of php
 *
 * @param string $suite suite path
 * @param string $testCase test case to run
 * @return array suite report
 */
function runTestCaseRemote($suite, $testCase)
{
    // prepare the uri parameters
    $args = sprintf("suite=%s&testCase=%s&XDEBUG_SESSION_START=%s", urlencode($suite), urlencode($testCase), uniqid());
    // prepare the command
    $uri = sprintf('http://%s%s?%s', $_SERVER['SERVER_NAME'], dirname($_SERVER['PHP_SELF']) . '/runTestCase.php', $args);

    $oldErrorReportingLevel = error_reporting(E_ERROR);

    // execute the http request
    $result = file_get_contents($uri);

    error_reporting($oldErrorReportingLevel);
    // check if an error has occured in the request
    if($result) {
	// the result should be a serialized representation of the suite report
	$report = unserialize($result);
    } else {
	$error = error_get_last();
	$details = sprintf('%s<br />in %s at line %s', $error['message'], $error['file'], $error['line']);

	$report = getNewSuiteReport($suite);
	$report['failedTests'][] = array(
					'name'    => $testCase,
					'class'   => getClassName($suite),
					'details' => sprintf('<div class="fatalError">%s</div>', $details)
				   );
    }

    return $report;
}

/**
 * Enters the script in safe error mode.
 *
 * In case of errors or unexpected end of the script we must not break the normal flow.
 * We treat this through an output buffering callback to normally exit the script
 * by completing and sending the suite report.
 */
function initSafeMode()
{
    global $successful;
    global $suiteReport;
    $successful = false;
    // initialize suite report
    $suiteReport = getNewSuiteReport($_REQUEST['suite']);

    // start output buffering with callback
    ob_start('ob_callback');
}

/**
 * Run the program in safe mode with two arguments: the suite and the testCase.
 * This function prints the suite report.
 *
 * @return boolean true if running on command line, false otherwise
 */
function runTestCaseSafe()
{
    if(isset($_REQUEST['suite']) && isset($_REQUEST['testCase']))
    {
	// enters the script in safe mode
	initSafeMode();

	global $suiteReport;
	global $successful;

	// run the test
	$result = runTestCase($_REQUEST['suite'], $_REQUEST['testCase']);

	// if we reach this point then the test run was successful
	$successful = true;

	// end output buffering
	ob_end_flush();

	$suiteReport = generateSuiteReport($_REQUEST['suite'], $suiteReport['buffer'], $result);

	echo serialize($suiteReport);

	return true;
    }
    return false;
}

/**
 * This function will be called when ob_end_flush() is called, or when
 * the output buffer is flushed to the cli at the end of the request.
 * If a fatal error happened in a test, it will suppress normal output
 * but instead will save it into the test results.
 *
 * @param string $buffer buffered output
 * @return string new output serialized suiteReport
 */
function ob_callback($buffer)
{
    global $successful;
    global $suiteReport;

    // the script terminated abnormally
    if(!$successful) {
	// update the suite report
	$suiteReport['buffer'] = ' ';
	$suiteReport['failedTests'][] = array(
					    'name'    => $_REQUEST['testCase'],
					    'class'   => getClassName($_REQUEST['suite']),
					    'details' => '<div class="fatalError">' . $buffer . '</div>'
					);
	return serialize($suiteReport);
    } else {
	$suiteReport['buffer'] = $buffer;
	return '';
    }
}

/**
 * Run a test suite and return the results.
 *
 * @param string $suite suite path
 * @param string $testCase test case to run
 * @param boolean $serialize OPTIONAL return serialized result
 * @return array $result
 */
function runTestCase($suite, $testCase)
{
    require_once $suite;
    $className = getClassName($suite);

//    require_once('PHPUnit/Framework/TestSuite.php');

    if(is_null($testCase)) {
	// if we run multiple test cases
	$suite_inst = new PHPUnit_Framework_TestSuite($className);
    } else {
	// if we run only one test case
	$suite_inst = new PHPUnit_Framework_TestSuite;
	$suite_inst->addTest(new $className($testCase));
    }

    // run the test suite
    $result = $suite_inst->run();

    return $result;
}

/**
 * Generates a suite report from given suite result object
 *
 * @param string $suite suite name
 * @param string $buffer output buffer of the suite run
 * @param object $result the results of the test suite
 * @return array the suite report array
 */
function generateSuiteReport($suite, $buffer, $result)
{
    // create an array with failed test names to separate them from passed tests
    $failedTests = array();
    foreach (array('failures', 'errors', 'notImplemented') as $section)
	foreach ($result->$section() as $test) {
	    $failedTests[] = $test->failedTest()->getName();
	}

    $details = $buffer;

    // passed tests
    $passedTests = array();
    foreach ($result->topTestSuite() as $test) {
	if(!in_array($test->getName(), $failedTests)) {
	    $passedTests[] = array(
		'name'    => $test->getName(),
		'class'   => get_class($test),
		'details' => $details
	    );
	}
    }
    // skipped tests
    $skippedTests = array();
    foreach ($result->notImplemented() as $test) {
	$skippedTests[] = array(
	    'name'    => $test->failedTest()->getName(),
	    'class'   => get_class($test->failedTest()),
	    'details' => $details
	);
    }
    // failed tests
    $failedTests = array();
    foreach ($result->failures() as $test) {
	$failedTests[] = array(
	    'name'    => $test->failedTest()->getName(),
	    'class'   => get_class($test->failedTest()),
	    'details' => str_replace('<', '&lt;', $test->toStringVerbose(true)) .
			   PHPUnit_Util_Filter::getFilteredStacktrace($test->thrownException(), false) .
			   $details
	);
    }
    // error tests
    $errorTests = array();
    foreach ($result->errors() as $test) {
	$errorTests[] = array(
	    'name'    => $test->failedTest()->getName(),
	    'class'   => get_class($test->failedTest()),
	    'details' => str_replace('<', '&lt;', $test->toStringVerbose(true)) .
			    PHPUnit_Util_Filter::getFilteredStacktrace($test->thrownException(), false) .
			    $details
	);
    }

    $suiteReport = array(
	'suite'        => $suite,
	'buffer'       => empty($buffer) ? '' : ' ',
	'testsCount'   => count($result->topTestSuite()),
	'passedTests'  => $passedTests,
	'failedTests'  => $failedTests,
	'errorTests'   => $errorTests,
	'skippedTests' => $skippedTests
    );

    return $suiteReport;
}

/**
 * Displays the test results given an array of suite reports
 *
 * @param array an array of suites reports
 * @param array an array of suites names to be displayed as opened
 * @return void outputs directly
 */
function displayResults($testResults, $keepOpen)
{
    global $folderParser;
    foreach($testResults as $suiteReport) {
	$suitePath  = $suiteReport['suite'];
	$suiteId    = $folderParser->getSuiteId($suitePath);
	$buffer     = $suiteReport['buffer'];
	$testsCount = $suiteReport['testsCount'];
	$shortSuite = str_replace(ROOT_DIR . '/', '', $suitePath);

	// display the results
	if (!empty($buffer))
	    $warning = ' w';
	else
	    $warning = '';
	if (in_array($suiteId, $keepOpen)) {
	    $open_icon = '-';
	    $open_style = ' style="display: block;"';
	} else {
	    $open_icon = '+';
	    $open_style = '';
	}
	echo <<<EOF
	<div class="suite">
	    <div class="suite_title" onclick="toggleSuite('$suiteId')">
		<a id="a_$suiteId" class="icon">$open_icon</a> <span name="$suiteId">$shortSuite</span><sup style="color: red;">$warning</sup>
		<span class="suite_controls">
EOF;
	printf('<span class="pass">%s</span>/<span class="fail">%s</span>',
	    $testsCount,
	    count($suiteReport['failedTests']) + count($suiteReport['errorTests']));

	echo <<<EOF
		    (<a href="javascript: controlSuite('$suiteId', true);">run only</a>|<a href="javascript: controlSuite('$suiteId', false);">exclude</a>|<a href='#' onClick="loadCodeCoverage('./?coverage=true&amp;runSuites[]=$suiteId'); return false;">code coverage</a>)
		</span>
	    </div>
	    <div class="suite_tests" id="suite_$suiteId"$open_style>
EOF;

	// passed tests
	foreach ($suiteReport['passedTests'] as $test) {
	    $details = $test['details'];
	    printf(
		"<p class=\"testcase_pass\">TestCase <a class=\"testcase\" href=\"javascript: controlSuite('%s', true, '%s');\"> <span class=\"suite\">%s</span>-&gt;<span class=\"case\">%s()</span> </a>: <span class=\"pass\">passed</span> %s</p>\n",
		$suiteId,
		$suiteId . '@' . $test['name'],
		$test['class'],
		$test['name'],
		empty($test['details']) ? ' ' : "<span class=\"exception\"><pre>$details</pre></span>"
	    );
	}
	// skipped tests
	foreach ($suiteReport['skippedTests'] as $test) {
	    printf(
		    "<p class=\"testcase_pass\">TestCase <span class=\"suite\">%s</span>-&gt;<span class=\"case\">%s()</span>: <span class=\"skip\">skipped</span> <span class=\"exception\"><pre>%s</pre></span></p>\n",
		$test['class'],
		$test['name'],
		$test['details']
	    );
	}
	// failed tests
	foreach ($suiteReport['failedTests'] as $test) {
	    printf(
		"<p class=\"testcase_fail\">TestCase <a class=\"testcase\" href=\"javascript: controlSuite('%s', true, '%s');\"> <span class=\"suite\">%s</span>-&gt;<span class=\"case\">%s()</span> </a>: <span class=\"fail\">failed</span> <span class=\"exception\"><pre>%s</pre></span></p>\n",
		$suiteId,
		$suiteId . '@' . $test['name'],
		$test['class'],
		$test['name'],
		$test['details']
	    );
	}
	// error tests
	foreach ($suiteReport['errorTests'] as $test) {
	    printf(
		"<p class=\"testcase_fail\">TestCase <a class=\"testcase\" href=\"javascript: controlSuite('%s', true, '%s');\"> <span class=\"suite\">%s</span>-&gt;<span class=\"case\">%s()</span> </a>: <span class=\"fail\">failed</span> <span class=\"exception\"><pre>%s</pre></span></p>\n",
		$suiteId,
		$suiteId . '@' . $test['name'],
		$test['class'],
		$test['name'],
		$test['details']
	    );
	}
	echo $buffer;
	echo "</div>\n</div>\n&nbsp;\n";
    }
}

/**
 * Creates an array with all test cases in a test file.
 *
 * @param string test file path
 * @return array test cases found in file
 */
function getTests($file)
{
    return getTestsWithScript($file);
}

/**
 * Creates an array with all test cases in a test file.
 * It uses an external perl script that parses the file based on regexes.
 * The script is fast and also skips methods in comment blocks.
 *
 * @param string test file path
 * @return array test cases found in file of false if no test found
 */
function getTestsWithScript($file)
{
    $command = dirname(__FILE__) . '/util/getTests.pl ' . $file . ' ' . TEST_CASE_PREFIX;
    $tests = shell_exec($command);
    if (strlen($tests) > 0) {
	return explode("\n", trim($tests));
    } else {
	return false;
    }
}

/**
 * Creates an array with all test cases in a test file.
 * It uses reflection class and getMethods.
 * Problems: this method is slow. Also it returns an emtpy array if there are parsing errors.
 *
 * @param string test file path
 * @return array test cases found in file
 */
function getTestsWithReflection($file)
{
    $className = getClassName($file);
    // We will get the tests name with the reflection class.
    // But we should not require the reflected class because of memory limitations.
    // Instead will run a script that generates all test method names based on TEST_CASE_PREFIX.
    $script = 'require_once("' .  $file . '"); $reflectionClass = new ReflectionClass("' . $className . '"); $methods = $reflectionClass->getMethods(); foreach($methods as $method) { echo $method->getName() . "\n"; }';
    $command = '/usr/bin/php -r \'' . $script . '\' | grep \'' . TEST_CASE_PREFIX . '\'';
    $tests = shell_exec($command);
    return split("\n", trim($tests));
}

/**
 * Gets the class name from a test file
 *
 * @param string test file path
 * @return string test class name
 */
function getClassName($file)
{
	$file = realpath($file);
    preg_match('/class (\w*)/', shell_exec("egrep \"^class\" $file"), $matches);
    if (empty($matches)){
		echo "installing egrep might speed up the tests and free up some memory";
		preg_match('/class (\w*)/', file_get_contents($file), $matches);
    }
    return isset($matches[1]) ? $matches[1] : null;
}

/**
 * Print the javascript response for Ajax request
 *
 * @param string $testClass the test class
 * @param string $testName last executed test
 * @param string $testResult 'passed', 'skipped', 'failed' or 'error'
 * @param boolean $return return the result instead of printing
 * @return string the response or outputs directly
 */
function printStatus($testClass, $testName, $testResult, $return = false)
{
    $testResults = array(
	'passed'  => '<span class="pass">pass</div>',
	'skipped' => '<span class="skip">skip</div>',
	'failed'  => '<span class="fail">fail</div>',
	'error'   => '<span class="fail">fail</div>',
    );

    $buffer = '';
    $buffer .= sprintf("document.getElementById('runnedTest').innerHTML = '%s-&gt;<b>%s</b>';\n", $testName, $testClass);
    $buffer .= sprintf("document.getElementById('testResult').innerHTML = '%s';\n", $testResults[$testResult]);
    $buffer .= sprintf("document.getElementById('loader').innerHTML = '%s/%s';\n", $_SESSION['runnedTests'], $_SESSION['testCount']);

    $loaderMaxWidth = 588;
    $loaderWidth = round(($loaderMaxWidth / $_SESSION['testCount']) * $_SESSION['runnedTests']);
    $buffer .= sprintf("document.getElementById('loader').style.width = '%spx';\n", $loaderWidth);
    // if was the last suite
    if($_SESSION['scheduledSuite'] == count($_SESSION['scheduledSuites'])) {
	$buffer .= sprintf("stopTests();");
    } else {
	$buffer .= sprintf("continueTests();");
    }

    if($return) {
	return $buffer;
    }

    echo $buffer;
}

/**
 * Check if a request is AjaxRequest
 *
 * @return boolean
 */
function isAjax()
{
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER ['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
}

/**
 * This function will parse command line parameters in the form
 * of name=value and place them in the $_REQUEST super global array.
 *
 * @return boolean true if running in cli and values were set, false otherwise
 */
function parse_cli_parameters()
{
    ini_set("register_argc_argv", "true");
    global $_REQUEST; global $argc; global $argv;
    if (php_sapi_name() == 'cli' && $argc > 0)
    {
	for ($i=1;$i < $argc;$i++)
	{
	    parse_str($argv[$i],$tmp);
	    $_REQUEST = array_merge($_REQUEST, $tmp);
	}
	return true;
    }

   return false;
}

/* EOF */