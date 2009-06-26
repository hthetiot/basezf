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
    public function formDate($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // force $value to array so we can compare multiple values
        // to multiple options.
        $value = (array) $value;

        // set config value
        $options = array(
            'year_start'    => date('Y')-70,
            'year_end'      => date('Y'),
            'year_empty'    => __('Year'),

            'month_start'   => 1,
            'month_end'     => 12,
            'month_empty'   => __('Month'),

            'day_start'     => 1,
            'day_end'       => 31,
            'day_empty'     => __('Day'),
        );

        // merge options
        foreach($options as $k => $v) {
            if (isset($attribs[$k])) {
                $options[$k] = $attribs[$k];
                unset($attribs[$k]);
            }
        }

        $xhtml = array();
        $xhtml[] = '<div ' . $this->_htmlAttribs($attribs) . '>';

        // build year
        $valueYear = (isset($value['y']) ? $value['y'] : null);
        $attribsYear = array(
            'class' => 'formDateYear',
            'id'    => $name . '_y',
            'name'  => $name . '[y]',
        );

        $xhtml[] = $this->_buildSelect(
            $attribsYear,
            $options['year_start'],
            $options['year_end'],
            $valueYear,
            $options['year_empty']
        );


        // build month
        $valueMonth = (isset($value['m']) ? $value['m'] : null);
        $attribsMonth = array(
            'class' => 'formDateMonth',
            'id'    => $name . '_m',
            'name'  => $name . '[m]',
        );

        $xhtml[] = $this->_buildSelect(
            $attribsMonth,
            $options['month_start'],
            $options['month_end'],
            $valueMonth,
            $options['month_empty']
        );

        // build day
        $valueDay = (isset($value['d']) ? $value['d'] : null);
        $attribsDay = array(
            'class' => 'formDateDay',
            'id'    => $name . '_d',
            'name'  => $name . '[d]',
        );

        $xhtml[] = $this->_buildSelect(
            $attribsDay,
            $options['day_start'],
            $options['day_end'],
            $valueDay,
            $options['day_empty']
        );
        $xhtml[] = '</div>';

        return implode("\n", $xhtml);
    }

    protected function _buildSelect($attribs, $start, $end, $value = 0, $emptyValue = null, $disable = false)
    {
        // is it disabled?
        if (true === $disable) {
            $attribs['disabled'] = 'disabled';
        }

        $xhtml = array();
        $xhtml[] = '<select ' . $this->_htmlAttribs($attribs) . '>';

        if (!is_null($emptyValue)) {

            $xhtml[] = '<option>' . $emptyValue .  '</option>';
        }

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

