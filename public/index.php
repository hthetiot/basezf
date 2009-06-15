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


// Register Error Handler
BaseZF_Error_Handler::registerErrorHandler();



/**
 * Initialize Application Configuration and Environment
 */
$application = new Zend_Application(

    // Application environment
    CONFIG_ENV,

    // Application Options
    array(

        // Bootstrap Options
        'bootstrap' => array(

            // Main bootstrap Class path
            'path' => PATH_TO_APPLICATION . '/Bootstrap.php',

            // Debug options
            'debug_enable'      => DEBUG_ENABLE,
            'debug_report'      => DEBUG_REPORT,
            'debug_report_from' => DEBUG_REPORT_FROM,
            'debug_report_to'   => DEBUG_REPORT_TO,
        ),

        // Autoloader Options
        'autoloadernamespaces'  => array(
            'Zend',
            'BaseZF',
            'MyProject'
        ),
    )
);

$application->bootstrap();
$application->run();

