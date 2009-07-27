<?php
/**
 * auto_prepend.php for MyProject in /includes/
 *
 * @category   MyProject
 * @package    MyProject
 * @copyright  Copyright (c) 2008
 * @author     Harold Thétiot (hthetiot)
 */

$t_start = microtime(true);

//---------------------------------------------------------------------------
// Set PHP Errors Reporting

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'on');

//---------------------------------------------------------------------------
// Locale settings

ini_set('mbstring.internal_encoding', 'utf-8');
ini_set('mbstring.script_encoding', 'utf-8');
date_default_timezone_set('Europe/Paris');

//---------------------------------------------------------------------------
// Define usefull paths

define('BASE_PATH',          realpath(dirname(__FILE__) . '/..'));
define('INCLUDE_PATH',       realpath(dirname(__FILE__)));
define('BIN_PATH',           BASE_PATH . '/bin');
define('LIBRARY_PATH',       BASE_PATH . '/lib');
define('PUBLIC_PATH',        BASE_PATH . '/public');
define('APPLICATION_PATH',   BASE_PATH . '/app');
define('CONFIG_PATH',        BASE_PATH . '/etc');
define('LOCALES_PATH',       BASE_PATH . '/locale');

//---------------------------------------------------------------------------
// Include missing functions from library path

require_once LIBRARY_PATH . '/missing_functions.php';

//---------------------------------------------------------------------------
// Include local_auto_prepend.php if available

if (!defined('NO_AUTO_PREPEND_LOCAL') && is_readable(INCLUDE_PATH . '/auto_prepend_local.php')) {
    require_once(INCLUDE_PATH . '/auto_prepend_local.php');
}

//
// Following define_if_not cann be defined before auto_prepend include or inauto_prepend_local
//

//---------------------------------------------------------------------------
// Application options

define_if_not('APPLICATION_ENV',  'production');
define_if_not('APPLICATION_CONFIG',   CONFIG_PATH . '/config.ini');

//---------------------------------------------------------------------------
// External variable env

define_if_not('BASE_URL', 'myproject.com');
define_if_not('BASE_URL_SCHEME', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://'));
define_if_not('MAIN_URL', BASE_URL_SCHEME . BASE_URL);

//---------------------------------------------------------------------------
// Debug options

define_if_not('DEBUG_ENABLE', false);
define_if_not('DEBUG_REPORT', true);
define_if_not('DEBUG_REPORT_FROM', 'debug@' . BASE_URL);
define_if_not('DEBUG_REPORT_TO', 'dev@' . BASE_URL);

//---------------------------------------------------------------------------
// Frameworks Path

define_if_not('ZF_PATH', '/usr/share/php/ZendFrameWork/release-1.8.4');
define_if_not('ZF_VERSION', '1.8.4');

define_if_not('BASEZF_PATH', LIBRARY_PATH . '/BaseZF');
define_if_not('MYPROJECT_PATH', INCLUDE_PATH);


//---------------------------------------------------------------------------
// file inclusion & autoload

set_include_path(

    // load ZF lib
    ZF_PATH . '/library' . PATH_SEPARATOR .
    ZF_PATH . '/library/incubator' . PATH_SEPARATOR .

    // load others lib
    BASEZF_PATH . '/library' . PATH_SEPARATOR .
    MYPROJECT_PATH . PATH_SEPARATOR .
    INCLUDE_PATH . PATH_SEPARATOR .
    LIBRARY_PATH . PATH_SEPARATOR .

    get_include_path()
);


//---------------------------------------------------------------------------
// Start Zend Loader

require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);

