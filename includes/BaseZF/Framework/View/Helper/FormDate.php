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

        // force $value to array so we can compare multiple values
        // to multiple options.
        $value = (array) $value;

        // set config value
        $defaultOptions = array(
            'year_start'    => date('Y')-70,
            'year_end'      => date('Y'),

            'month_start'   => 1,
            'month_end'     => 12,

            'day_start'     => 1,
            'day_end'       => 31,
        );

        $xhtml = array();
        $xhtml[] = '<div ' . $this->_htmlAttribs($attribs) . '>';

        // build year
        $startYear = date('Y')-70;
        $endYear = date('Y');
        $valueYear = (isset($value['y']) ? $value['y'] : null);
        $attribsYear = array(
            'class' => 'formDateYear',
            'id'    => $name . '_y',
            'name'  => $name . '[y]',
        );

        $xhtml[] = $this->_buildSelect($attribsYear, $startYear, $endYear, $valueYear);


        // build month
        $startMonth = 1;
        $endMonth = 12;
        $valueMonth = (isset($value['m']) ? $value['m'] : null);
        $attribsMonth = array(
            'class' => 'formDateMonth',
            'id'    => $name . '_m',
            'name'  => $name . '[m]',
        );

        $xhtml[] = $this->_buildSelect($attribsMonth, $startMonth, $endMonth, $valueMonth);

        // build day
        $startDay = 1;
        $endDay = 31;
        $valueDay = (isset($value['d']) ? $value['d'] : null);
        $attribsDay = array(
            'class' => 'formDateDay',
            'id'    => $name . '_d',
            'name'  => $name . '[d]',
        );

        $xhtml[] = $this->_buildSelect($attribsDay, $startDay, $endDay, $valueDay);
        $xhtml[] = '</div>';

        return implode("\n", $xhtml);
	}

    protected function _buildSelect($attribs, $start, $end, $value = 0, $disable = false)
    {
        // is it disabled?
        if (true === $disable) {
            $attribs['disabled'] = 'disabled';
        }

        $xhtml = array();
        $xhtml[] = '<select ' . $this->_htmlAttribs($attribs) . '>';

        for ($i = $start; $i <= $end; $i++) {

            // is it selected?
            $selected = '';
            if ($i == $value) {
                $selected = ' selected="selected"';
            }

            $xhtml[] = '<option'
                     . $selected
                     . ' value="' . str_pad($i, 2, '0', STR_PAD_LEFT) . '">'
                     . str_pad($i, 2, '0', STR_PAD_LEFT)
                     . '</option>';
        }

        $xhtml[] = '</select>';

        return implode("\n", $xhtml);
    }
}

