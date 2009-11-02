<?php
/**
 * FormDate.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framework
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_FormDate extends Zend_View_Helper_FormElement
{

    public function formDate($name, $value = null, $attribs = null, $options = array())
    {
        $info = $this->_getInfo($name, $value, $attribs, $options);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // force $value to array so we can compare multiple values
        // to multiple options.
        $value = (array) $value;
        $options = (array) $options;

        // set config value
        $defaultOptions = array(
            'years'      => range(date('Y')-70, date('Y')),
            'days'       => range(1, 31),
            'months'     => array(
                1   => __('January', 'time'),
                2   => __('February', 'time'),
                3   => __('March', 'time'),
                4   => __('April', 'time'),
                5   => __('May', 'time'),
                6   => __('June', 'time'),
                7   => __('July', 'time'),
                8   => __('August', 'time'),
                9   => __('September', 'time'),
                10  => __('October', 'time'),
                11  => __('November', 'time'),
                12  => __('December', 'time'),
            ),
            'format'    => array('Y', 'm', 'd'),
            'calendar'  => array(
                'direction' => 7,
                'tweak'     => array(
                    'x' => 6,
                    'y' => 0,
                ),
            ),
        );

        // populate options cause i whont overload any _Form_Element_Date
        $optionsPossibleKeys = array_keys($defaultOptions);
        $options = (is_array($options) ? $options : array());
        foreach ($attribs as $k => $v) {
            if (in_array($k, $optionsPossibleKeys)) {
                $options[$k] = $v;
                unset($attribs[$k]);
            }
        }

        if (!empty($options) || !is_array($options)) {
            $options = array_merge($defaultOptions, $options);
        } else {
            $options = $defaultOptions;
        }

        // build Xhtml
        $xhtml = array();
        $xhtml[] = '<div id="' . $id . '" ' . $this->_htmlAttribs($attribs) . '>';

        // set format
        $elementsFormatByName = array();
        foreach ($options['format'] as $formatName) {
            $xhtml[] = $this->_buildElementDateFormat($formatName, $value, $name, $options);
            $elementsFormatByName[$name . '_' . $formatName] = $formatName;
        }

        $xhtml[] = '</div>';

        $xhtml[] = '<script type="text/javascript">';
        $xhtml[] = 'if (typeof CalendarInstance == "undefined") var CalendarInstance = {};';
        $xhtml[] = "CalendarInstance." . $name . " = new Calendar({ " . $name . "_" . $formatName . " : " . json_encode($elementsFormatByName) . "}, " . json_encode($options['calendar']) . ");";
        $xhtml[] = '</script>';

        return implode("\n", $xhtml);
    }

    protected function _buildElementDateFormat($formatName, $value, $name, $options)
    {
        switch ($formatName) {

            // build year: Y
            case 'Y':
            {
                $yearValue = (isset($value[$formatName]) ? $value[$formatName] : null);
                $yearOptions = $options['years'];

                return $this->_buildSelect(
                    $yearValue,
                    $yearOptions,
                    array(
                        'class' => 'formDateYear',
                        'id'    => $name . '_' . $formatName,
                        'name'  => $name . '[' . $formatName . ']',
                    )
                );
            }

            // build year: y
            case 'y':
            {
                $yearValue = (isset($value[$formatName]) ? $value[$formatName] : null);
                $yearOptions = array();
                foreach ($options['years'] as $key => $value) {
                    $yearOptions[$key] = substr($value, 2 , 2);
                }

                return $this->_buildSelect(
                    $yearValue,
                    $yearOptions,
                    array(
                        'class' => 'formDateYear',
                        'id'    => $name . '_' . $formatName,
                        'name'  => $name . '[' . $formatName . ']',
                    )
                );
            }

            // build day
            case 'd':
            {
                $dayValue= (isset($value[$formatName]) ? $value[$formatName] : null);
                $dayOptions = $options['days'];

                return $this->_buildSelect(
                    $dayValue,
                    $dayOptions,
                    array(
                        'class' => 'formDateDay',
                        'id'    => $name . '_' . $formatName,
                        'name'  => $name . '[' . $formatName . ']',
                    )
                );
            }

            // build month
            case 'm':
            {
                $monthValue = (isset($value[$formatName]) ? $value[$formatName] : null);
                $monthOptions = $options['months'];

                return $this->_buildSelect(
                    $monthValue,
                    $monthOptions,
                    array(
                        'class' => 'formDateMonth',
                        'id'    => $name . '_' . $formatName,
                        'name'  => $name . '[' . $formatName . ']',
                    )
                );
            }

            default:
                throw new Exception(sprintf('Bad element format value "%s" for helper class %s', $formatName, __CLASS__));
        }
    }

    protected function _buildSelect($currentValue = 0, $options, $attribs)
    {
        $xhtml = array();
        $xhtml[] = '<select ' . $this->_htmlAttribs($attribs) . '>';

        $isAssoc = !(current(array_keys($options)) == '0');

        foreach ($options as $value => $label) {

            if (!$isAssoc) {
                $label = $value = str_pad($label, 2, '0', STR_PAD_LEFT);
            }

            // is it selected?
            $selected = '';
            if ($value == $currentValue) {
                $selected = ' selected="selected"';
            }

            $xhtml[] = '<option' . $selected . ' value="' . $value . '">' . $this->view->escape($label) . '</option>';

        }

        $xhtml[] = '</select>';

        return implode("\n", $xhtml);
    }
}

