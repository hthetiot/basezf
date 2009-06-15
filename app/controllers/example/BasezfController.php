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

    public function errorAction()
    {
        if ($this->_getParam('error') == 1) {

             $titi = time();

             echo $toto;
        }
    }

    public function imageAction()
    {
    }

    public function notifyAction()
    {
        /*
        $notify = BaseZF_Notify::getInstance();

        if($data = $notify->get('test')) {
            var_dump($data);
        } else {
            $notify->set('test', 'toto');
        }

        die();
        */
    }

    public function stitemAction()
    {
        /*
        $countries = MyProject_StCollection_Country::getInstance();


        foreach ($countries as $country) {

            echo $country->getExtendedId();
        }

        die();
        */
    }

    public function dbitemAction()
	{

        // clear count cache /cache/perpage
		// add collection dependency

		$examples = new MyProject_DbCollection('example');
		$examples->filterWhere('example_id > ? AND country_id = 1', 1);
		$examples->filterOrderBy('example_id DESC');
		$examples->filterLimit(10);
        //$examples->clearCache();

		//
		echo '<hr />';
		echo 'filterCount dbColl:';
		echo $examples->filterCount();

		//
		echo '<hr />';
		echo 'filter dbColl:' . "<br />";
		$examples->filterExecute();
        foreach($examples as $example) {
            echo $example->getId() . '/' . date('Y-m-d', $example->creation) . "<br />";
        }

		// create
		echo '<hr />';
		echo 'insert dbItem:' . "<br />";

		$data = array(
			'country_id'	=> '1',
			'language_id'	=> '1',
			'login'			=> time(),
			'email'			=> 'w',
			'display_name'	=> 'w',
			'creation'		=> '2008-10-10',
		);

		$example = $examples->newItem($data);

		$id = $example->getId();
		echo $id;

		// select
		echo '<hr />';
		echo 'properties dbItem:' . "<br />";
		$example = MyProject_DbItem::getInstance('example', $id);
        $example->getId();
        echo $example->login;

		// update
		echo '<hr />';
		echo 'update dbItem:' . "<br />";
		$example->login = 'titi' . time();
        $example->login = 'titi' . time();
		$example->update();
		echo 'done';

        echo '<hr />';
		echo 'list update dbItem:' . "<br />";
        foreach($examples as $example) {
            echo $example->getId() . '/' . $example->login . "<br />";
        }

		// delete
		echo '<hr />';
		echo 'delete dbItem:' . "<br />";
		$example->delete();
		echo 'done';

        echo '<hr />';
		echo 'list deleted dbItem:' . "<br />";
        foreach($examples as $example) {
            echo $example->getId() . '/' . $example->login . "<br />";
        }


    }

    public function dbsearchAction()
    {
    }
}
