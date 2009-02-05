<?php
/**
 * FormDate.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_FormDate extends Zend_View_Helper_FormElement
{
	public function formDate($name, $value = null, $attribs = null, $options = null)
    {
        $info = $this->_getInfo($name, $value, $attribs, $options);
        extract($info); // name, value, attribs, options, listsep, disable

        $xhtml = array();

        $xhtml[] = '<div ' . $this->_htmlAttribs($attribs) . '>';

        // build year
        $xhtml[] = '<select class="formDateYear" id="' . $name . '_y" name="' . $name . '_y">';

        $startYear = date('Y')-70;
        $endYear = date('Y');
        for ($year = $startYear; $year < $endYear; $year++) {
            $xhtml[] = '<option value="' . $year . '">' . $year . '</option>';
        }
        $xhtml[] = '</select>  ';

        // build month
        $startMonth = 1;
        $endMonth = 12;

        $xhtml[] = '<select class="formDateMonth" id="' . $name . '_m" name="' . $name . '_m">';
        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $xhtml[] = '<option value="' . str_pad($month, 2, '0', STR_PAD_LEFT) . '">' . str_pad($month, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        $xhtml[] = '</select>  ';

        // build day
        $startDay = 1;
        $endDay = 31;

        $xhtml[] = '<select class="formDateDay" id="' . $name . '_d" name="' . $name . '_d">';
        for ($day = $startDay; $day <= $endDay; $day++) {
            $xhtml[] = '<option value="' . $day . '">' . str_pad($day, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        $xhtml[] = '</select>  ';

        $xhtml[] = '</div>';

        return implode("\n", $xhtml);
	}
}

