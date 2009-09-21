<?php
/**
 * ErrorControllerTest.php for App in Controllers
 *
 * @category   App
 * @package    App_UnitTest
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

if (!defined('APPLICATION_PATH')) {
    require_once dirname(__FILE__) . '/../../TestHelper.php';
}

/**
 * Test class for Error.
 *
 * @group App_Controllers
 */
class ErrorControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $application = new Zend_Application(APPLICATION_ENV, APPLICATION_CONFIG);
        $application->bootstrap();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    public function testErrorControllerTrapsMissingActionsAs404s()
    {
        $this->dispatch('/paste/bogus');
        $this->assertModule('default');
        $this->assertController('error');
        $this->assertAction('notfound');
        $this->assertResponseCode(404);
    }

    public function testErrorControllerTrapsMissingControllersAs404s()
    {
        $this->dispatch('/bogus');
        $this->assertModule('default');
        $this->assertController('error');
        $this->assertAction('notfound');
        $this->assertResponseCode(404);
    }

    public function testErrorControllerTrapsExceptionsAs500s()
    {
        $this->markTestSkipped('Still trying to determine a scenario to test this');
    }
}

