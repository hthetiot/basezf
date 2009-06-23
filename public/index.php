<?php
/**
 * index.php
 *
 * Main Bootstrap launcher
 *
 * @category   MyProject
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

// include auto_prepend if missing
if (!defined('APPLICATION_PATH')) {
	require_once('../includes/auto_prepend.php');
}

// Register Error Handler
BaseZF_Error_Handler::registerErrorHandler();

try {

	// Test Zend Framework Version
	if (Zend_Version::compareVersion(ZF_VERSION) > 0) {
		trigger_error(sprintf('Please upgrade to a newer version of Zend Framework (require %s)', ZF_VERSION), E_USER_NOTICE);
	}

    // Initialize Application Configuration and Environment
    $application = new Zend_Application(APPLICATION_ENV, APPLICATION_CONFIG);
    $application->bootstrap();
	$application->run();

} catch (Exception $e) {

    // debug error enable ?
    if (defined('DEBUG_ENABLE') && DEBUG_ENABLE) {
        BaseZF_Error_Handler::debugException($e);
        exit();

    // report error enable ?
    } else if (defined('DEBUG_REPORT') && DEBUG_REPORT) {
        BaseZF_Error_Handler::sendExceptionByMail($e, DEBUG_REPORT_FROM, DEBUG_REPORT_TO);
    }

    // then display Service Temporarily Unavailable
	ob_start();
	header("HTTP/1.1 503 Service Temporarily Unavailable");
	header("Status: 503 Service Temporarily Unavailable");
	header("Retry-After: 120");
	header("Connection: Close");

echo <<<EOD
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
	<title>503 Service Temporarily Unavailable</title>
</head>
<body>
	<h1>Service Temporarily Unavailable</h1>
	<p>
		The server is temporarily unable to service your
		request due to maintenance downtime or capacity
		problems. Please try again later.
	</p>
</body>
</html>
EOD;

	echo ob_get_clean();

	// Exit with error status
	exit(1);
}

