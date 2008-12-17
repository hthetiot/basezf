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

error_reporting(E_ALL);
ini_set('display_errors', 'on');

//---------------------------------------------------------------------------
// Locale settings

ini_set('mbstring.internal_encoding', 'utf-8');
ini_set('mbstring.script_encoding', 'utf-8');
date_default_timezone_set('Europe/Paris');

//---------------------------------------------------------------------------
// Define usefull paths

define('PATH_BASE',             realpath(dirname(__FILE__) . '/..'));
define('PATH_TO_INCLUDES',      realpath(dirname(__FILE__)));
define('PATH_TO_BIN',           PATH_BASE . '/bin');
define('PATH_TO_LIBRARY',       PATH_BASE . '/library');
define('PATH_TO_DOCUMENT_ROOT', PATH_BASE . '/public');
define('PATH_TO_APPLICATION',   PATH_BASE . '/app');
define('PATH_TO_CONFIG',        PATH_BASE . '/etc');
define('PATH_TO_LOCALES',       PATH_TO_APPLICATION . '/locales');
define('PATH_TO_CONTROLLERS',   PATH_TO_APPLICATION . '/controllers');
define('PATH_TO_VIEWS',         PATH_TO_APPLICATION . '/views');
define('PATH_TO_HELPERS',       PATH_TO_VIEWS . '/helpers');
define('PATH_TO_LAYOUTS',       PATH_TO_VIEWS . '/layouts');

//---------------------------------------------------------------------------
// Include missing functions from library path

require_once PATH_TO_LIBRARY . '/missing_functions.php';

//---------------------------------------------------------------------------
// detect SSL

if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
    define_if_not('BASE_HTTP_SCHEME', 'https://');
} else {
    define_if_not('BASE_HTTP_SCHEME', 'http://');
}

//---------------------------------------------------------------------------
// Include local_auto_prepend.php if available

if (!defined('NO_AUTO_PREPEND_LOCAL') && is_readable(PATH_TO_INCLUDES . '/auto_prepend_local.php')) {
	require_once(PATH_TO_INCLUDES . '/auto_prepend_local.php');
}

//---------------------------------------------------------------------------
// Config  (can be defined before auto_prepend include)

define_if_not('CONFIG_ENV', 'production');
define_if_not('CONFIG_FILE', PATH_TO_CONFIG . '/config.xml');
define_if_not('CONFIG_ACL_FILE', PATH_TO_CONFIG . '/acl.xml');
define_if_not('CONFIG_ACL_ROUTES_FILE', PATH_TO_CONFIG . '/acl_routes.xml');

//---------------------------------------------------------------------------
// External variable env

// main url
define_if_not('MAIN_URL', 'bahu.com');
define_if_not('BASE_URL', BASE_HTTP_SCHEME . MAIN_URL);

// sub url
define_if_not('BASE_URL_HOME_MEMBER', 'http://%s.' . MAIN_URL);

// cdn url use BASE_HTTP_SCHEME to be secure too
define_if_not('CDN_URL_JS', BASE_HTTP_SCHEME . 'design.' . MAIN_URL);
define_if_not('CDN_URL_CSS', BASE_HTTP_SCHEME . 'design.' . MAIN_URL);
define_if_not('CDN_URL_DESIGN', BASE_HTTP_SCHEME . 'design.' . MAIN_URL);

// mail
define_if_not('MAIL_DEFAULT_SENDER', 'noreply@' . MAIN_URL);
define_if_not('MAIL_DEFAULT_SENDER_NAME', 'BaseZF');

// cookies
define_if_not('COOKIES_DOMAIN', '.' . MAIN_URL);

//---------------------------------------------------------------------------
// ZendFramework Path

defined('PATH_TO_ZF') or define('PATH_TO_ZF', '/home/httpd/phpinclude/ZendFrameWork/release-1.7.0/library');

//---------------------------------------------------------------------------
// file inclusion & autoload

set_include_path(
    PATH_TO_ZF . PATH_SEPARATOR .
    PATH_TO_INCLUDES . PATH_SEPARATOR .
    PATH_TO_LIBRARY . PATH_SEPARATOR .
    get_include_path()
);

//---------------------------------------------------------------------------
// Start Zend Loader

require_once 'Zend/Loader.php';

Zend_Loader::registerAutoload();


