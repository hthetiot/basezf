<?php
/**
 * Copyright (c) 2009  Radu Gasler <miezuit@gmail.com>
 *
 *  This file is free software: you may copy, redistribute and/or modify it  
 *  under the terms of the GNU General Public License as published by the  
 *  Free Software Foundation, either version 2 of the License, or (at your  
 *  option) any later version.
 *
 *  This file is distributed in the hope that it will be useful, but  
 *  WITHOUT ANY WARRANTY; without even the implied warranty of  
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU  
 *  General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License  
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 *
 * Run a test case in safe mode.
 *
 * This script is called from index.php through a http request
 * Request parameters: suite and testCase
 *
 * $Id: runTestCase.php 2 2009-03-06 11:11:06Z miezuit $
 *
 * $Rev: 2 $
 *
 * $LastChangedBy: miezuit $
 *
 * $LastChangedDate: 2009-03-06 13:11:06 +0200 (V, 06 mar. 2009) $
 *
 * @author Radu Gasler <miezuit@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html     GPL License
 * @version 0.1
 */

ini_set('display_errors', 'On');

error_reporting(E_ALL | E_STRICT);

require_once 'config.php';
require_once 'functions.php';
session_start();
if(defined('PHPUNIT_DIR')) {
    set_include_path(get_include_path() . PATH_SEPARATOR . PHPUNIT_DIR);
}

// run test case from test suite, print serialized suite report and exit
runTestCaseSafe();

/* EOF */
