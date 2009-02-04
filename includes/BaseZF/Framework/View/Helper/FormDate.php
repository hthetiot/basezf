<?php
/**
 * FormDate.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_FormDate extends BaseZF_Framework_View_Helper_Abstract
{
	public function formDate($name, $value = null, $attribs = null, $options = null)
    {
        return '

            <select id="" name="' . $name . '_d">
                <option>1</option>
                <option>2</option>
            </select>

            <select id="" name="' . $name . '_y">
                <option>1</option>
                <option>2</option>
            </select>

            <select id="" name="' . $name . '_m">
                <option>1</option>
                <option>2</option>
            </select>
        ';
	}
}

