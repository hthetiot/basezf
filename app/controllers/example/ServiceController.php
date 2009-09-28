<?php
/**
 * UwaController.php
 *
 * @category   MyProject
 * @package    MyProject_App_Controller
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

class Example_ServiceController extends BaseZF_Framework_Controller_Action_Uwa
{
    /**
     * Xml-Rpc controller main action
     */
    public function rpcAction()
    {
        Zend_XmlRpc_Server_Fault::attachFaultException('Exception');

        $server = new Zend_XmlRpc_Server();
        $server->setClass('MyProject_BL_Member', 'member');

        header('Content-Type: text/xml');
        echo $server->handle();
        exit(0);
    }

    public function rpchelpAction()
    {
        $client = new Zend_XmlRpc_Client(MAIN_URL . '/example/service/rpc');
        $system = $client->getProxy('system');
        $member = $client->getProxy('member');

        echo '<hr />';
            echo "Available methods: \n";
            print_r($system->listMethods());
            exit();

    }

    public function rpcconsoleAction()
    {
        $client = new Zend_XmlRpc_Client(MAIN_URL . '/example/service/rpc');
        $member = $client->getProxy('member');

    }
}
