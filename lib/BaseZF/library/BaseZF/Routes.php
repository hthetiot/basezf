<?php
/**
 * Routes class in /BazeZF/
 *
 * @category   BazeZF
 * @package    BazeZF_Core
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

abstract class BaseZF_Routes
{
    /**
     * Take a localized string and return an url valid representation of it
     *
     * @param  string $url
     * @return string
     */
    static public function cleanUrl($url)
    {
        /* convert the string to a 7bits representation */
        //
        $url = stringToAscii($url);

        $url = preg_replace(array('/&szlig;/',
        '/&(..)lig;/',
        '/&([aeiouAEIOU])uml;/',
        '/&(.)[^;]*;/'),
        array('ss',
        "$1",
        "$1",
        "$1"),
        $url);

        /* strip non alpha characters */
        $url = preg_replace(array('/[^[:alpha:]\d\.]/', '/-+/'), '-', $url);

        // remove eventual leading/trailing hyphens due to leading/trailing non-alpha chars
        return trim($url, '-');
    }

    static public function getCurrentNameSpace()
    {
        // get current route namespace for app with many route
        // to do not have to create all route instance

        return 'default';
    }

    /**
     * Give routes by namespace
     *
     * @return array an array of instance of Zend_Controller_Router_Route
     */
    abstract static public function &fetch($nameSpace = null);
    /*
    static public function &fetch($nameSpace = null)
    {
        static $routes;

        if ($nameSpace === null) {
            $nameSpace = MyProject_Routes::getNameSpace();
        }

        // do not create multiple instance of routes
        if (!isset($routes[$nameSpace])) {

            $routes = array(
                'error-404' => new Zend_Controller_Router_Route('404',
                    array('module'         => 'default',
                    'controller'     => 'error',
                    'action'         => 'error404')
                ),

                'error-500' => new Zend_Controller_Router_Route('500',
                    array('module'         => 'default',
                    'controller'     => 'error',
                    'action'         => 'error500')
                ),
            );
        }

        return $routes;
    }
   */
}

