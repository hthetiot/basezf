<?php
/**
 * MyProject_Routes class in /MyProject/
 *
 * @category   MyProject_Core
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class MyProject_Routes extends BaseZF_Routes
{
    static public function &fetch($nameSpace = null)
    {
        static $routes;

        if ($nameSpace === null) {
            $nameSpace = MyProject_Routes::getCurrentNameSpace();
        }

        // do not create multiple instance of routes
        if (!isset($routes[$nameSpace])) {

            $routes = array(
                'error-404' => new Zend_Controller_Router_Route('404',
                    array('module' 		=> 'default',
                    'controller' 	=> 'error',
                    'action'     	=> 'error404')
                ),

                'error-500' => new Zend_Controller_Router_Route('500',
                    array('module' 		=> 'default',
                    'controller' 	=> 'error',
                    'action'     	=> 'error500')
                ),
            );
        }

        return $routes;
    }
}

