<?php
/**
 * Routes class in /BazeZF/
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Routes
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
		$url = mb_convert_encoding(($url),
		'HTML-ENTITIES',
		'ISO-8859-15');

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

    public function getNameSpace()
    {
        // get current route namespace for app with many route
        // to do not have to create all route instance
    }

    /**
     * Give routes
     *
     * @return array an array of instance of Zend_Controller_Router_Route
     */
    abstract static public function &fetch();
    /*

    new Zend_Controller_Router_Route(
    'author/:username',
    array(
        'controller' => 'profile',
        'action'     => 'userinfo'
    )
    );

    $route = new Zend_Controller_Router_Route(
        'archive/:year',
        array('year' => 2006)
    );

    $route = new Zend_Controller_Router_Route(
        'archive/:year',
        array(
            'year'       => 2006,
            'controller' => 'archive',
            'action'     => 'show'
        ),
        array('year' => '\d+')
    );

    $hostnameRoute = new Zend_Controller_Router_Route_Hostname(
        ':username.users.example.com',
        array(
            'controller' => 'profile',
            'action'     => 'userinfo'
        )
    );

    'error-404' => new Zend_Controller_Router_Route_Regex('^site/page-non-trouvee$',
                array('module' 		=> 'default',
                      'controller' 	=> 'error',
                      'action'     	=> 'error404')
            ),

    'error-500' => new Zend_Controller_Router_Route_Regex('^site/erreur-interne$',
        array('module' 		=> 'default',
              'controller' 	=> 'error',
              'action'     	=> 'error500')
    */
}

