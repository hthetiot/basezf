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
            'debug_enable'          => false,
            'debug_report'          => true,
            'debug_report_from'     => null,
            'debug_report_to'       => null,
        ),

        // Autoloader Options
        'autoloadernamespaces'  => array(
            'Zend',
            'BaseZF',
            'MyProject',
            'App',
        ),
    )
);

$application->bootstrap();
$application->run();

