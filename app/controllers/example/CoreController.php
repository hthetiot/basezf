<?php
/**
 * CoreController.php
 *
 * @category   MyProject
 * @package    MyProject_App_Controller
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
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

    public function dbtemplateAction()
    {
        $examples = new MyProject_DbCollection('example');
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

        $tpl = new BaseZF_DbTemplate();
        $tpl->setTemplate($tplString);
        $tpl->setData($data);

        $tplRendered = $tpl->render();

        echo $tplRendered;
/*
        echo "<hr />";
        $sleepTpl = serialize($tpl);
        var_dump($sleepTpl);

        echo "<hr />";
        $wakeupTpl = unserialize($sleepTpl);
        var_dump($wakeupTpl);

        echo "<hr />";
        echo $wakeupTpl;
*/
        die();
    }

    public function dbitemAction()
    {

        // clear count cache /cache/perpage
        // add collection dependency

        $examples = new MyProject_DbCollection('example');
        $examples->filterWhere('example_id > ? AND example_type_id = 1', 1);
        $examples->filterOrderBy('example_id DESC');
        $examples->filterLimit(10);
        $examples->clearCache();

        //
        echo '<hr />';
        echo 'filterCount dbColl:';
        echo $examples->filterCount();

        //
        echo '<hr />';
        echo 'filter dbColl:' . "<br />";
        $examples->filterExecute();
        foreach ($examples as $example) {
            echo $example->getId() . '/' . date('Y-m-d', $example->creation) . "<br />";
        }

        // create
        echo '<hr />';
        echo 'insert dbItem:' . "<br />";

        $data = array(
            'example_type_id'   => '1',
            'unique_string'     => time(),
            'string'            => 'hello',
            'creation'          => '2008-10-10',
        );

        $example = $examples->newItem($data);

        $id = $example->getId();
        echo $id;

        // select
        echo '<hr />';
        echo 'properties dbItem:' . "<br />";
        $example = MyProject_DbItem::getInstance('example', $id);
        $example->getId();
        echo $example->unique_string;

        // update
        echo '<hr />';
        echo 'update dbItem:' . "<br />";
        $example->unique_string = 'titi' . time();
        $example->unique_string = 'titi' . time();
        $example->update();
        echo 'done';

        echo '<hr />';
        echo 'list update dbItem:' . "<br />";
        foreach ($examples as $example) {
            echo $example->getId() . '/' . $example->unique_string . "<br />";
        }

        // delete
        echo '<hr />';
        echo 'delete dbItem:' . "<br />";
        $example->delete();
        echo 'done';

        echo '<hr />';
        echo 'list deleted dbItem:' . "<br />";
        foreach ($examples as $example) {
            echo $example->getId() . '/' . $example->unique_string . "<br />";
        }


    }

    public function dbsearchAction()
    {
    }
}
