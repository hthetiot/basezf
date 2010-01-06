<?php
/**
 * BaseZF_Framework_View_Helper_UwaWidgetPreferences class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Framework_View_Helper_UwaWidgetPreferences extends BaseZF_Framework_View_Helper_Abstract
{
     public function uwaWidgetPreferences(array $widgetPreferences)
     {
        $xhtml = array();
        $xhtml[] = '<widget:preferences>';

         // render Widget preferences
        foreach ($widgetPreferences as $name => $data) {

            $tagAttribs = array(
                'name'          => $name,
                'type'          => $data->type,
                'label'         => $data->label, // label will translated
                'defaultValue'  => $data->value,
            );

            // render by type
            switch ($data->type) {

                case 'list':
                {
                    $xhtml[] = "\t" . '<preference ' . $this->_renderTagAttribs($tagAttribs) . '>';

                    foreach ((array) $data->options as $value => $label) {
                        $xhtml[] = "\t\t" .'<option ' . $this->_renderTagAttribs(array('label' => $label, 'value' => $value)) . '/>';
                    }

                    $xhtml[] = "\t" . '</preference>';

                    break;
                }

                case 'range':
                {
                    $tagAttribs['step'] =(isset($data->options['step']) ? $data->options['step'] : 1);
                    $tagAttribs['min'] = (isset($data->options['min']) ? $data->options['min'] : 1);
                    $tagAttribs['max'] = (isset($data->options['max']) ? $data->options['max'] : 99);
                }

                default:
                    $xhtml[] = "\t" . '<preference ' . $this->_renderTagAttribs($tagAttribs) . ' />';
            }
        }

        $xhtml[] = '</widget:preferences>';

        return implode("\n", $xhtml) . "\n";
     }

     /**
      *
      */
     protected function _renderTagAttribs(array $tagAttribs)
     {
        $xhtml = array();
        foreach ($tagAttribs as $key => $value) {
            $xhtml[] = $key . '="' . (is_array($value) ? Zend_Json::encode($value) : $this->escape($value)) . '"';
        }

        return implode(' ', $xhtml);
     }
}

