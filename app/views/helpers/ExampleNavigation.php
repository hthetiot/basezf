<?php
/**
 * Example.php for MyProject in /app/views/helpers
 *
 * @category   MyProject
 * @package    MyProject_App_Helper
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

class App_View_Helper_ExampleNavigation extends Zend_View_Helper_Navigation
{
    /**
     * This is the main helper methods
     *
     */
    public function exampleNavigation()
    {
        /*
       $config = array(
            array(
                'label'      => 'Home',
                'module'     => 'example',
                'controller' => 'index',
                'action'     => 'index',
            ),

            array(
                'label'      => 'Coding Guidelines',
                'module'     => 'example',
                'controller' => 'index',
                'action'     => 'guideslines',
            ),

            array(
                'label'      => 'Libraries',
                'module'     => 'example',
                'controller' => 'index',
                'action'     => 'index',
                'pages'      => array(
                    array(
                        'label'      => 'Blueprint',
                        'module'     => 'example',
                        'controller' => 'library',
                        'action'     => 'blueprint',
                    ),
                ),
            ),
        );

        $container = new Zend_Navigation($config);
		$this->setContainer($container);

        return $this;

        */
        $menus = array(

            '' => array(
                '/example'                      => 'Home',
                '/example/index/guideslines'    => 'Coding Guidelines',
            ),

            'Libraries' => array(
               //'/example/library/zend'            => 'Zend Framework',
               '/example/library/blueprint'       => 'Blueprint',
               '/example/library/mootools'        => 'Mootools',
            ),

            'Tools' => array(
               '/example/tools/debug'           => 'Debug',
               '/example/tools/makefile'        => 'MakeFile',
            ),

            'CSS Elements' => array(
               '/example/css/menus'             => 'Menus',
               '/example/css/buttons'           => 'Fancy Buttons',
               '/example/css/box'               => 'Round Box',
            ),

            'JS Elements' => array(
               '/example/javascript/ajaxlink'       => 'Ajax Link',
               '/example/javascript/ajaxform'       => 'Ajax Form',
               '/example/javascript/lightbox'       => 'LightBox',
               '/example/javascript/uwa'            => 'UWA Widget',
            ),

            'Form Elements' => array(
                '/example/form/index'           => 'Showcases',
                '/example/form/fancyselect'     => 'Fancy Select',
                '/example/form/dateselect'      => 'Date Select',
                '/example/form/autocompleter'   => 'Auto Completer',
                //'/example/form/rangeselect'   => 'Range Select Element',
                //'/example/form/contactselect' => 'Contact List Element',
            ),

            'View Helpers' => array(
                '/example/helper/geshi'            => 'GeShi',
                '/example/helper/googleanalytics'  => 'Google Analytics',
            ),

            'Services' => array(
                '/example/service/xmlrpc'           => 'Simple XMLRPC',
            ),

            'Core Classes' => array(
                '/example/core/error'         => 'Error Handler',
                '/example/core/controller'    => 'Controller Abstract',
                '/example/core/archive'       => 'Archive',
                '/example/core/notify'        => 'Notify',
                '/example/core/stitem'        => 'StItem/StCollection',
                '/example/core/dbitem'        => 'DbItem/DbCollection',
                '/example/core/dbsearch'      => 'DbSearch',
            ),
        );

        $xhtml = array();
        foreach ($menus as $menu => $links) {

            if (strlen($menu) > 0) {
                $xhtml[] = '<h4>' . $this->view->escape($menu) . '</h4>';
            }

            $xhtml[] = '<ul>';
            foreach ($links as $link => $label) {
                $xhtml[] = '<li><a href="' . $link . '">' . $this->view->escape($label) . '</a></li>';
            }
            $xhtml[] = '</ul>';
        }

        return implode("\n", $xhtml);
    }
}
