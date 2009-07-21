<?php
/**
 * Date.php class in /BazeZF/Framework/Form/Element
 *
 * @category   BazeZF_Framework
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

/** Zend_Form_Element_Xhtml */
require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Date form element
 *
 */
class BaseZF_Framework_Form_Element_Date extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formDate';

    /**
     * Array of options
     * @var array options
     */
    public $options = array();

    public function setYears(array $years)
    {
        $this->options['years'] = $years;
    }

    public function setDays(array $days)
    {
        $this->options['days'] = $days;
    }

    public function setMonths(array $months)
    {
        $this->options['months'] = $months;
    }

    public function setFormat($format)
    {
        $this->options['format'] = $format;
    }

    public function setCalendar(array $calendar)
    {
        $this->options['calendar'] = $calendar;
    }
}
