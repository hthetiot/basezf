<?php
/**
 * Example.php for MyProject in /app/views/helpers
 *
 * @category   MyProject_Helpers
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
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
            ),

            'Form Elements' => array(
                '/example/form/index'           => 'Showcases',
                '/example/form/multipleselect'  => 'Multiple Select',
                '/example/form/rangeselect'     => 'Range Select',
                '/example/form/dateselect'      => 'Date Select',
                '/example/form/contactselect'   => 'Contact List',
            ),

            'CSS Elements' => array(
               '/example/css/menus'         => 'Menus',
               '/example/css/buttons'       => 'Fancy Buttons',
               '/example/css/box'           => 'Round Box',
            ),

            'JS Elements' => array(
               '/example/javascript/ajaxlink'           => 'Ajax Link',
               '/example/javascript/ajaxformvalidate'   => 'Ajax Form Validation',
               '/example/javascript/autocompleter'      => 'Auto Completer',
               '/example/javascript/lightbox'           => 'LightBox',
               '/example/javascript/uwa'                => 'UWA Widget',
            ),

            'Helpers' => array(
                '/example/helper/geshi'            => 'GeShi',
                '/example/helper/googleanalytics'  => 'Google Analytics',
            ),

            'Classes' => array(
                '/example/BaseZF/error'         => 'Error Handler',
                '/example/BaseZF/controller'    => 'Controller Abstract',
                '/example/BaseZF/image'         => 'Image',
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

            foreach ($links as $link => $label) {
                $xhtml[] = '<div><a href="' . $link . '">' . $this->view->escape($label) . '</a></div>';
            }
        }

        return implode("\n", $xhtml);
    }
}
