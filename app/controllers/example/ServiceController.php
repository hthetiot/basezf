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

    //
    // Xml/Rpc
    //

    public function xmlrpcAction()
    {
    }

    public function xmlrpcServerAction()
    {
        // disable layout and view
        $this->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        // set output format
        header('Content-Type: text/xml');

        // handle server request
        Zend_XmlRpc_Server_Fault::attachFaultException('Exception');
        $server = new Zend_XmlRpc_Server();
        $server->setClass('MyProject_Service_XmlRpc_Example', 'example');
        $response = $server->handle();

        // display response
        echo $response;

        exit(0);
    }

    public function xmlrpcHelpAction()
    {
        $serverUrl = str_replace('help', 'server', MAIN_URL . $this->getRequest()->getRequestUri());
        $client = new Zend_XmlRpc_Client($serverUrl);
        $system = $client->getProxy('system');

        echo '<hr />';
        echo '<pre>';
        echo "Available methods: \n";
        print_r($system->listMethods());
        echo '</pre>';
        exit();
    }

    public function xmlrpcConsoleAction()
    {
        $serverUrl = str_replace('console', 'server', MAIN_URL . $this->getRequest()->getRequestUri());
        $client = new Zend_XmlRpc_Client($serverUrl);
        $example = $client->getProxy('example');

        echo $example->getTime();

        exit();
    }

    //
    // Soap
    //

    public function soapAction()
    {

    }

    public function soapServerAction()
    {
        // disable layout and view
        $this->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        // handle wsdl request
        if(isset($_GET['wsdl'])) {

            $autodiscover = new Zend_Soap_AutoDiscover();
            $autodiscover->setClass('MyProject_Service_Soap_Example');
            $autodiscover->handle();

        // handle server request
        } else {

            $serverUrl = str_replace('server', 'wsdl', MAIN_URL . $this->getRequest()->getRequestUri());
            $soap = new Zend_Soap_Server($serverUrl);
            $soap->setClass('MyProject_Service_Soap_Example');
            $soap->handle();
        }
    }

    public function soapWsdlAction()
    {
        // disable layout and view
        $this->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $autodiscover = new Zend_Soap_AutoDiscover();
        $autodiscover->setClass('MyProject_Service_Soap_Example');
        $autodiscover->handle();

        exit();
    }

    public function soapHelpAction()
    {
        $serverUrl = str_replace('help', 'server', MAIN_URL . $this->getRequest()->getRequestUri());
        $client = new Zend_Soap_Client($serverUrl);

        echo '<hr />';
        echo '<pre>';
        echo "Available methods: \n";
        print_r($client->getFunctions());
        echo '</pre>';
        exit();

    }

    public function soapConsoleAction()
    {
        $serverUrl = str_replace('console', 'server', MAIN_URL . $this->getRequest()->getRequestUri() . '?wsdl');
        $client = new Zend_Soap_Client($serverUrl);

        print_r($client->getTime());

        exit();
    }
}
