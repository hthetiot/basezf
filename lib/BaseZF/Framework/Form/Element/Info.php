<?php
/**
 * Info.php class in /BazeZF/Framework/Form/Element
 *
 * @category   BazeZF_Framework
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

/** Zend_Form_Element_Xhtml */
require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Info form element
 *
 */
class BaseZF_Framework_Form_Element_Info extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formInfo';

    /**
     * Constructor
     *
     * $spec may be:
     * - string: name of element
     * - array: options with which to configure element
     * - Zend_Config: Zend_Config with options for configuring element
     *
     * @param  string|array|Zend_Config $spec
     * @return void
     * @throws Zend_Form_Exception if no element name after initialization
     */
    public function __construct($name, $options = null)
    {
        parent::__construct($name, $options);
    }
}
