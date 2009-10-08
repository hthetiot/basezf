<?php
/**
 * ServiceController.php
 *
 * @category   MyProject
 * @package    MyProject_App_Controller
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

class Example_ServiceController extends BaseZF_Framework_Controller_Action
{
    /**
     * Set default layout
     */
    protected $_defaultLayout = 'example';

    public function xmlrpcAction()
    {
    }

    public function xmlrpcServerAction()
    {
        Zend_XmlRpc_Server_Fault::attachFaultException('Exception');

        $server = new Zend_XmlRpc_Server();
        $server->setClass('MyProject_BL_Member', 'member');

        header('Content-Type: text/xml');
        echo $server->handle();
        exit(0);
    }

    public function xmlrpcHelpAction()
    {
        $client = new Zend_XmlRpc_Client(MAIN_URL . '/example/service/rpc-server');
        $system = $client->getProxy('system');
        $member = $client->getProxy('member');

        echo '<hr />';
        echo "Available methods: \n";
        print_r($system->listMethods());
        exit();

    }

    public function xmlrpcConsoleAction()
    {
        $client = new Zend_XmlRpc_Client(MAIN_URL . '/example/service/rpc-server');
        $member = $client->getProxy('member');
    }
}
