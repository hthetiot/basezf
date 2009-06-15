<?php
/**
 * Abstract.php
 *
 * @category   BazeZF_Framework
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_Framework_View_Helper_Abstract extends Zend_View_Helper_Placeholder_Container_Standalone
{
    public function escape($string)
    {
        return $this->view->escape($string);
    }
}

