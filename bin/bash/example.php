#!/usr/bin/php
<?php
/**
 * example.php in /bin/bash
 *
 * @category   MyProject
 * @package    MyProject_App_Binary
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

// include auto_prepend if missing
if (!defined('APPLICATION_PATH')) {
    require_once(realpath(dirname(__FILE__)) . '/../../includes/auto_prepend.php');
}

// exec script
$example = BaseZF_Console::factory('MyProject_Console_Example');
$example->run();

