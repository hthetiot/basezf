<?php
/**
 * TestHelper.php for App in /tests/
 *
 * @category   App
 * @package    App_UnitTest
 * @copyright  Copyright (c) 2008
 * @author     Harold Thetiot (hthetiot)
 */

//---------------------------------------------------------------------------
// Start output buffering

ob_start();

//---------------------------------------------------------------------------
// Maximize memory limit

ini_set('memory_limit', -1);

//---------------------------------------------------------------------------
// Define application environment

define('APPLICATION_ENV',  'test');

//---------------------------------------------------------------------------
// Include auto_prepend if missing

if (!defined('APPLICATION_PATH')) {
    require_once(realpath(dirname(__FILE__)) . '/../includes/auto_prepend.php');
}

