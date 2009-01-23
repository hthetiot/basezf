<?php
/**
 * BaseZfController.php
 *
 * @category   MyProject_Controller
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class Example_BaseZfController extends BaseZF_Framework_Controller_Action
{
    /**
     * Set default layout
     */
    protected $_defaultLayout = 'example';

    public function controllerAction()
    {
    }

    public function imageAction()
    {
    }

    public function notifyAction()
    {
    }

    public function stitemAction()
    {
    }

    public function dbitemAction()
    {
        $examples = new MyProject_DbCollection('example');
        $examples->filterExecute();
        
        foreach($examples as $example) {
            echo $example->login;
        }
        
        /*
        $example = MyProject_DbItem::getInstance('example', 1);
        echo $example->getId();
        echo $example->login;
        */
        
        exit();
    }

    public function dbsearchAction()
    {
    }
}
