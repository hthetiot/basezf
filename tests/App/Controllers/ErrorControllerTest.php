<?php
// Call ErrorControllerTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "ErrorControllerTest::main");
}

require_once dirname(__FILE__) . '/../../TestHelper.php';

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

    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {

    }

    public function testErrorControllerTrapsMissingActionsAs404s()
    {
        $this->dispatch('/paste/bogus');
        $this->assertModule('default');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(404);
    }

    public function testErrorControllerTrapsMissingControllersAs404s()
    {
        $this->dispatch('/bogus');
        $this->assertModule('default');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(404);
    }

    public function testErrorControllerTrapsExceptionsAs500s()
    {
        $this->markTestSkipped('Still trying to determine a scenario to test this');
    }
}

