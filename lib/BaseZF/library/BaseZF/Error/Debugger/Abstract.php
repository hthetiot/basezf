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

    abstract protected function _render();

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

    public function getServerParams()
    {
        return $_SERVER;
    }

    public function getPostParams()
    {
        return $_POST;
    }

    public function getGetParams()
    {
        return $_GET;
    }

    public function getCookiesParams()
    {
        return isset($_COOKIES) ? $_COOKIES : false;
    }

    public function getSessionParams()
    {
        return isset($_SESSION) ? $_SESSION : false;
    }
}

