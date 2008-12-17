<?php
/**
 * index.php
 *
 * Main Bootstrap
 *
 * @category   MyProject
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class Bootstrap extends BaseZF_Bootstrap
{
    protected $_controllerModules = array(
        'default',
        'example',
    );
}

try {

    Bootstrap::run();

} catch (Exception $e) {

    MyProject::sendExceptionByMail($e);

    header('');
    exit(0);
}

