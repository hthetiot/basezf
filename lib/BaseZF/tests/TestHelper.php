<?php
/**
 * TestHelper.php for BaseZF in /tests/
 *
 * @category   BaseZF
 * @package    BaseZF_UnitTest
 * @copyright  Copyright (c) 2008
 * @author     Harold Thetiot (hthetiot)
 */

//---------------------------------------------------------------------------
// Start output buffering
ob_start();

//---------------------------------------------------------------------------
// Set PHP Errors Reporting

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'on');

//---------------------------------------------------------------------------
// Maximize memory limit
ini_set('memory_limit', -1);

//---------------------------------------------------------------------------
// Locale settings

ini_set('mbstring.internal_encoding', 'utf-8');
ini_set('mbstring.script_encoding', 'utf-8');
date_default_timezone_set('GMT');

//---------------------------------------------------------------------------
// Define usefull paths

define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));

//---------------------------------------------------------------------------
// file inclusion & autoload

set_include_path(

    // frameworks
    BASE_PATH   . '/library' . PATH_SEPARATOR .
    BASE_PATH   . '/tests' . PATH_SEPARATOR .

    '/home/hthetiot/src/ZendFramework-git/library/'.

    get_include_path()
);

//---------------------------------------------------------------------------
// Start Zend Loader

require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);
$autoloader->suppressNotFoundWarnings(true);


