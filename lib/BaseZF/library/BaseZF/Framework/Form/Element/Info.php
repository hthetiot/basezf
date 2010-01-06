<?php
/**
 * Info.php class in /BazeZF/Framework/Form/Element
 *
 * @category  BazeZF
 * @package   BazeZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

/** Zend_Form_Element_Xhtml */
require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Info form element
 */
class BaseZF_Framework_Form_Element_Info extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formInfo';

    /**
     * Array of options
     * @var array options
     */
    public $options = array();

    /**
     * Set message lines for Info element
     *
     * @param array $messages messages values
     *
     * @return $this for more fluent interface
     */
    public function setMessages(array $messages)
    {
        $this->options['messages'] = $messages;

        return $this;
    }
}

