<?php
/**
 * Exception class in /BazeZF/Error
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Error_Exception extends BaseZF_Exception
{
    public function __construct($message = null, $code = 0, $file = null, $line = null)
    {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
    }
}

