<?php
/**
 * MyProject_Routes class in /MyProject/
 *
 * @category   MyProject
 * @package    MyProject_Core
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

class MyProject_Routes extends BaseZF_Routes
{
    static public function &fetch($nameSpace = null)
    {
        static $routes;

        // do not create multiple instance of routes
        if (!isset($routes)) {

            $routes = array(

                'user' => new Zend_Controller_Router_Route(
                    'user/:username',
                    array(
                        'module'         => 'default',
                        'controller'     => 'index',
                        'action'         => 'index'
                    )
                ),

                'error404' => new Zend_Controller_Router_Route(
                    'error-404',
                    array(
                        'module'         => 'default',
                        'controller'     => 'error',
                        'action'         => 'notfound'
                    )
                ),

                'error500' => new Zend_Controller_Router_Route(
                    'error-500',
                    array(
                        'module'         => 'default',
                        'controller'     => 'error',
                        'action'         => 'applicationerror'
                    )
                ),
            );
        }

        return $routes;
    }
}

