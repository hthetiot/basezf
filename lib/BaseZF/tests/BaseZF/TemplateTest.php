<?php
/**
 * TemplateTest.php for BaseZF in tests/
 *
 * @category   BaseZF
 * @package    BaseZF_UnitTest
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

require_once dirname(__FILE__) . '/../TestHelper.php';

/**
 * Test class for BaseZF_Template
 *
 * @group BaseZF
 */
class BaseZF_TemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * Call before all test and on class test loading
     */
    public function setUp()
    {

    }

    public function testRenderTemplateWithSimpleTag()
    {
        $tplData = array(
          'login'   => 'Toto',
          'gender'  => 'Mr',
        );

        $tplString = 'hello {gender} {login}, did you like parsor !';
        $tplStringTest = 'hello ' . $tplData['gender'] . ' ' . $tplData['login'] . ', did you like parsor !';

        $tpl = new BaseZF_Template();
        $tpl->setTemplate($tplString);
        $tpl->setData($tplData);

        $this->assertEquals($tplStringTest, $tpl->render());
    }

    public function testRenderTemplateWithArrayTag()
    {
        $tplData = array(
            'member' => array(
                'login'   => 'Toto' . time(),
                'gender'  => 'Mr',
            ),
        );

        $tplString = 'hello {member:gender} {member:login}, did you like parsor !';
        $tplStringTest = 'hello ' . $tplData['member']['gender'] . ' ' . $tplData['member']['login'] . ', did you like parsor !';

        $tpl = new BaseZF_Template();
        $tpl->setTemplate($tplString);
        $tpl->setData($tplData);

        $this->assertEquals($tplStringTest, $tpl->render());
    }

    public function testRenderTemplateWithConstantTag()
    {
        $constantName = 'BaseZF_TemplateTest_' . rand(1, 100);
        define($constantName, time());

        $tplString = 'hello [const:' . $constantName . '], did you like parsor !';
        $tplStringTest = 'hello ' . constant($constantName) . ', did you like parsor !';
        $tplData = array();

        $tpl = new BaseZF_Template();
        $tpl->setTemplate($tplString);
        $tpl->setData($tplData);

        $this->assertEquals($tplStringTest, $tpl->render());
    }

    public function testRenderTemplateWithCaseTag()
    {
        $tplData = array(
          'login'   => 'Toto',
          'gender'  => rand(1, 0),
        );

        $tplString = 'hello [if: {gender} == 1 ? Mr : Miss ] {login}, did you like parsor !';
        $tplStringTest = 'hello ' . ($tplData['gender'] == 1 ? 'Mr' : 'Miss') . ' Toto, did you like parsor !';

        $tpl = new BaseZF_Template();
        $tpl->setTemplate($tplString);
        $tpl->setData($tplData);

        $this->assertEquals($tplStringTest, $tpl->render());
    }

    public function testRenderTemplateWithBeginTag()
    {
        $this->markTestSkipped('Still trying to determine a scenario to test this');
    }

    public function testRenderTemplateWithAllTags()
    {
        $this->markTestSkipped('Still trying to determine a scenario to test this');
    }

    public function testRenderTemplateWithArrayAccess()
    {
        $this->markTestSkipped('Still trying to determine a scenario to test this');
    }

    public function testRenderTemplateWithIterator()
    {
        $this->markTestSkipped('Still trying to determine a scenario to test this');
    }

    public function testSleepAndWakeUpTemplateWithAllTags()
    {
        $this->markTestSkipped('Still trying to determine a scenario to test this');
    }

    /**
     * Call after all test and on class test loading
     */
    public function tearDown()
    {
    }
}
