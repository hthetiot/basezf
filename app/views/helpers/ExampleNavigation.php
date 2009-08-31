<?php
/**
 * Example.php for MyProject in /app/views/helpers
 *
 * @category   MyProject
 * @package    MyProject_App_Helper
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

class App_View_Helper_ExampleNavigation extends BaseZF_Framework_View_Helper_Abstract
{
    /**
     * This is the main helper methods
     *
     */
    public function exampleNavigation()
    {
        $menus = array(

            '' => array(
                '/example'                      => 'Home',
                '/example/index/guideslines'    => 'Coding Guidelines',
            ),

            'Tools' => array(
               '/example/index/blueprint'       => 'Blueprint',
               '/example/index/mootools'        => 'Mootools',
               '/example/index/debug'           => 'Debug',
               '/example/index/makefile'        => 'MakeFile',
            ),

            'Form Elements' => array(
                '/example/form/index'           => 'Showcases',
                '/example/form/fancyselect'     => 'Fancy Select Element',
                '/example/form/dateselect'      => 'Date Element',
                //'/example/form/rangeselect'   => 'Range Select Element',
                //'/example/form/contactselect' => 'Contact List Element',
            ),

            'CSS Elements' => array(
               '/example/css/menus'             => 'Menus',
               '/example/css/buttons'           => 'Fancy Buttons',
               '/example/css/box'               => 'Round Box',
            ),

            'JS Tools' => array(
               '/example/form/autocompleter'    => 'Auto Completer',
               '/example/javascript/ajaxlink'   => 'Ajax Link',
               '/example/javascript/ajaxform'   => 'Ajax Form',
               '/example/javascript/lightbox'   => 'LightBox',
               '/example/javascript/uwa'        => 'UWA Widget',
            ),

            'Helpers' => array(
                '/example/helper/geshi'            => 'GeShi',
                '/example/helper/googleanalytics'  => 'Google Analytics',
            ),

            'Classes' => array(
                '/example/BaseZF/error'         => 'Error Handler',
                '/example/BaseZF/controller'    => 'Controller Abstract',
                '/example/BaseZF/archive'       => 'Archive',
                '/example/BaseZF/notify'        => 'Notify',
                '/example/BaseZF/stitem'        => 'StItem/StCollection',
                '/example/BaseZF/dbitem'        => 'DbItem/DbCollection',
                '/example/BaseZF/dbsearch'      => 'DbSearch',
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
