<?php
/**
 * Exception class in /BazeZF/
 *
 * @category   BazeZF
 * @package    BazeZF_Core
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
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

