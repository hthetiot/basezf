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

		// clear count cache /cache/perpage
		// add collection dependency

		$examples = new MyProject_DbCollection('example');
		$examples->filterWhere('example_id > ?', 1);
		$examples->filterOrderBy('example_id DESC');
		$examples->filterLimit(1000);

		//
		echo '<hr />';
		echo 'filterCount dbColl:';
		echo $examples->filterCount();

		//
		echo '<hr />';
		echo 'filter dbColl:' . "<br />";
		$examples->filterExecute();
        foreach($examples as $example) {
            echo $example->getId() . '/' . $example->login . "<br />";
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
			'creation'		=> 'NOW()',
		);

		$example = MyProject_DbItem::getInstance('example');
		$example->setProperties($data);
		$example->insert();

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
		$example->update();
		echo 'done';

		// delete
		echo '<hr />';
		echo 'delete dbItem:' . "<br />";
		//$example->delete();
		echo 'done';

    }

    public function dbsearchAction()
    {
    }
}
