<?php
/**
 * HeadWidgetPreferences.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_HeadWidgetPreferences extends BaseZF_Framework_View_Helper_Abstract
{
     public function headWidgetPreferences(array $widgetPreferences)
     {
        $xhtml = array();
        $xhtml[] = '<widget:preferences>';

         // render Widget preferences
        foreach($widgetPreferences as $name => $data) {

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
                        $xhtml[] = "\t\t" .'<option ' . $this->_renderTagAttribs(array('label' => $label, 'value' => $value)) . ' />' ;
                    }

                    $xhtml[] = "\t" . '</preference>';

                    break;
                }

                case 'range':
                {
                    $tagAttribs['step'] =(isset($data->options['step']) ? $data->options['step'] : 1);
                    $tagAttribs['min'] = (isset($data->options['min']) ? $data->options['step'] : 1);
                    $tagAttribs['max'] = (isset($data->options['max']) ? $data->options['step'] : 99);
                }

                default:
                    $xhtml[] = "\t" . '<preference ' . $this->_renderTagAttribs($tagAttribs) . ' />';
            }
        }

        $xhtml[] = '</widget:preferences>';

        return implode("\n", $xhtml);
     }

     /**
      *
      */
     protected function _renderTagAttribs(array $tagAttribs)
     {
        $xhtml = array();
        foreach ($tagAttribs as $key => $value) {
            $xhtml[] = $key . '="' . (is_array($value) ? json_encode($value) : $this->escape($value)) . '"';
        }

        return implode(' ', $xhtml);
     }
}

