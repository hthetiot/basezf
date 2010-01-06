<?php
/**
 * BaseZF_Framework_View_Helper_Abstract class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

abstract class BaseZF_Framework_View_Helper_Abstract extends Zend_View_Helper_Placeholder_Container_Standalone
{
    public function escape($string)
    {
        return $this->view->escape($string);
    }
}

