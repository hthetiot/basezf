<?php
/**
 * BaseZF_ExampleTest class in tests/BaseZF
 *
 * @category  BaseZF
 * @package   BaseZF_UnitTest
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

require_once dirname(__FILE__) . '/../TestHelper.php';

/**
 * Test class for Example
 *
 * @group BaseZF
 * @group BaseZF_Example
 */
class BaseZF_ExampleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Call before all test and on class test loading
     */
    public function setUp()
    {
        // configure test here
    }

    public function testUpdatePropertyValue()
    {
        // use time to have floating value
        $value = time();

        $example = new BaseZF_Example();

        $example->updateProperty($value);

        // compare waiting results with results
        $this->assertEquals($value, $example->getProperty());

        /*
        assertArrayHasKey()
        assertClassHasAttribute()
        assertClassHasStaticAttribute()
        assertContains()
        assertContainsOnly()
        assertEqualXMLStructure()
        assertEquals()
        assertFalse()
        assertFileEquals()
        assertFileExists()
        assertGreaterThan()
        assertGreaterThanOrEqual()
        assertLessThan()
        assertLessThanOrEqual()
        assertNotNull()
        assertObjectHasAttribute()
        assertRegExp()
        assertSame()
        assertSelectCount()
        assertSelectEquals()
        assertSelectRegExp()
        assertStringEqualsFile()
        assertTag()
        assertThat()
        assertTrue()
        assertType()
        assertXmlFileEqualsXmlFile()
        assertXmlStringEqualsXmlFile()
        assertXmlStringEqualsXmlString()
        */
    }

    /**
     * Call after all test and on class test loading
     */
    public function tearDown()
    {
        // clean database or test generated data for example
    }
}

