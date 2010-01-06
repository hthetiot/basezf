<?php
/**
 * MyProject_Routes class in /MyProject
 *
 * @category  MyProject
 * @package   MyProject_Core
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/MyProject/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
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

