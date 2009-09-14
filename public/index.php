<?php
/**
 * index.php
 *
 * Main Bootstrap launcher
 *
 * @category   MyProject
 * @package    MyProject_App
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

// include auto_prepend if missing
if (!defined('APPLICATION_PATH')) {
    require_once(realpath(dirname(__FILE__)) . '/../includes/auto_prepend.php');
}

try {

    // Register Error Handler to convert PHP error has Exception
    BaseZF_Error_Handler::registerErrorHandler();

    // Test Zend Framework Version
    BaseZF_Version::checkZendVersion(ZF_VERSION);

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
        BaseZF_Error_Handler::sendExceptionByMail($e, DEBUG_REPORT_FROM, DEBUG_REPORT_TO, DEBUG_REPORT_SUBJECT);
    }

    // then display Service Temporarily Unavailable
    ob_start();
    header("HTTP/1.1 503 Service Temporarily Unavailable");
    header("Status: 503 Service Temporarily Unavailable");
    header("Retry-After: 120");
    header("Connection: Close");
    echo file_get_contents('./unavailable.html');
    echo ob_get_clean();

    // Exit with error status
    exit(1);
}

