<?php
/**
 * Example_CoreController class in /app/controllers/example
 *
 * @category  MyProject
 * @package   MyProject_App_Controller
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class Example_CoreController extends BaseZF_Framework_Controller_Action
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

    public function archiveAction()
    {

    }

    public function notifyAction()
    {
        /*
        $notify = BaseZF_Notify::getInstance();

        if ($data = $notify->get('test')) {
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

    public function templateAction()
    {
        /*
        $examples = new MyProject_Collection_Db('example');
        $examples->filterWhere('example_id > ? AND example_type_id = 1', 1);
        $examples->filterOrderBy('example_id DESC');
        $examples->filterLimit(10);
        $examples->filterExecute();

        $data = array(
            'titi' => rand(1, 3),
            'tata' => array(
                'tutu' => time(),
                'tete' => time(),
            ),
            'data' => $examples,

            'data2' => array(
                'a',
                'b',
            ),
        );

        $tplString = '
        <pre>
            test string with var name   : titi
            test var                    : titi={titi}
            test if titi == 1           : [if: {titi} == 1 ? titi=true : titi=false]
            test var array assoc        : {tata:tutu}
            test const                  : [const:BASE_PATH]

            [begin:{datas}]
            test begin/end with limit   : {data:unique_string}
            [end:{datas}]
        </pre>
        ';

        $tpl = new BaseZF_Template();
        $tpl->setTemplate($tplString);
        $tpl->setData($data);

        $tplRendered = $tpl->render();

        echo $tplRendered;
        die();
        */

    }

    public function dbitemAction()
    {
        $this->_helper->layout->disableLayout();

        /*
        // select
        echo '<hr />';
        echo 'properties dbItem:' . "<br />";
        $myExample = MyProject_Item_Db::getInstance('example', 4);
        $myExample->getId();
        echo $myExample->unique_string;
        */

        // select
        echo '<hr />';
        echo 'create collection:';
        $examples = new MyProject_Collection_Db('example', array(3, 4));
        foreach ($examples as $example) {
            echo $example->getId() . '/' . $example->creation . "<br />";
        }

/*
        // clear count cache /cache/perpage
        // add collection dependency

        echo '<hr />';
        echo 'create collection:';
        $examples = new MyProject_Collection_Db('example');
        $examples->filterWhere('example_id > ? AND example_type_id = 1', 1);
        $examples->filterOrderBy('example_id DESC');
        $examples->filterLimit(10);
        $examples->clearCache();

        //
        echo '<hr />';
        echo 'filterCount Coll db:';
        echo $examples->filterCount();

        //
        echo '<hr />';
        echo 'see Coll db item:' . "<br />";
        $examples->filterExecute();
        foreach ($examples as $example) {
            echo $example->getId() . '/' . $example->creation . "<br />";
        }
        */
/*
        // create
        echo '<hr />';
        echo 'insert dbItem:' . "<br />";

        $data = array(
            'example_type_id'   => '1',
            'unique_string'     => time(),
            'string'            => 'hello',
            'creation'          => '2008-10-10',
        );

        $myExample = $examples->newItem($data);
        $myId = $myExample->getId();
        echo $myId;

        // select
        echo '<hr />';
        echo 'properties dbItem:' . "<br />";
        $myExample = MyProject_Item_Db::getInstance('example', $myId);
        $myExample->getId();
        echo $myExample->unique_string;

        // update
        echo '<hr />';
        echo 'update dbItem:' . "<br />";
        $myExample->unique_string = 'titi' . time();
        $myExample->unique_string = 'titi' . time();
        $myExample->update();
        echo 'done';

        echo '<hr />';
        echo 'list update dbItem:' . "<br />";
        foreach ($examples as $example) {
            echo $example->getId() . '/' . $example->unique_string . "<br />";
        }

        // delete
        echo '<hr />';
        echo 'delete dbItem:' . "<br />";
        $myExample->delete();
        unset($myExample);
        echo 'done';

        echo '<hr />';
        echo 'list deleted dbItem:' . "<br />";
        foreach ($examples as $example) {
            echo $example->getId() . '/' . $example->unique_string . "<br />";
        }
        */
    }

    public function dbsearchAction()
    {
    }
}

