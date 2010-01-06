<?php
/**
 * Exception class in /BazeZF/
 *
 * @category  BazeZF
 * @package   BazeZF_Core
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Exception extends Exception
{
    public $file;

    public $line;

    public $context;

    public function __construct($message = null, $code = 0, $file = null, $line = null, $context = array())
    {
        parent::__construct($message, $code);

        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }
}

