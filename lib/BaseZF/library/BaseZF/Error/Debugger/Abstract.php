<?php
/**
 * Abstract class in /BazeZF/Error/Debugger
 *
 * @category   BazeZF
 * @package    BazeZF_Core
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

abstract class BaseZF_Error_Debugger_Abstract
{
    protected $_exception;

    public function __construct(Exception $exception)
    {
        $this->_exception = $exception;

        return $this->_render();
    }

    public function getExceptionSourceDetails()
    {
        $source = false;

        if (is_callable(array($this->_exception, 'getSource'))) {
            $source = highlight_string($this->_exception->getSource(), true);
        }

        return $source;
    }

    public function getExceptionContext()
    {
        $context = false;
        if (is_callable(array($this->_exception, 'getContext'))) {
            $context = $this->_exception->getContext();
        }

        return $context;
    }

    abstract protected function _render();
}

