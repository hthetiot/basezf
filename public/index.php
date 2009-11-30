<?php
/**
 * Main Bootstrap launcher
 *
 * PHP Version 5.2.11 or 5.3
 *
 * @category MyProject
 * @package  MyProject_App
 * @author   Harold Thetiot <hthetiot+basezf@gmail.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://github.com/hthetiot/basezf
 */

// Include auto_prepend if missing
if (!defined('APPLICATION_PATH')) {
    include_once realpath(dirname(__FILE__)) . '/../includes/auto_prepend.php';
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

    // report error enable ?
    if (defined('DEBUG_REPORT') && DEBUG_REPORT) {
        BaseZF_Error_Handler::sendExceptionByMail(
            $e,
            DEBUG_REPORT_FROM,
            DEBUG_REPORT_TO,
            DEBUG_REPORT_SUBJECT
        );
    }

    // debug error enable ?
    if (defined('DEBUG_ENABLE') && DEBUG_ENABLE) {
        BaseZF_Error_Handler::debugException($e);
        exit(1);
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

