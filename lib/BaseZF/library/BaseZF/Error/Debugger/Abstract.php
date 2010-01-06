<?php
/**
 * Abstract class in /BazeZF/Error/Debugger
 *
 * @category  BazeZF
 * @package   BaseZF_Error
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
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

    /**
     * Gets the interesting lines in the interesting file
     */
    public function getExceptionSourceDetails($nbLine = 6)
    {
        if (!is_file($this->_exception->getFile())) {
            return;
        }

        $file = fopen($this->_exception->getFile(), 'r');
        $beginLine = max(0, $this->_exception->getLine() - $nbLine / 2);
        $endLine = $beginLine + $nbLine - 1;
        $code = '';
        $curLine = 0;

        while ($line = fgets($file)) {

            $curLine++;

            if ($this->_exception->getLine() == $curLine) {
                $lineLabel = 'ERR:';
            } else {
                $lineLabel = str_pad($curLine, 3, '0', STR_PAD_LEFT) . ':';
            }

            if ($curLine >= $beginLine && $curLine <= $endLine) {
                $code .= $lineLabel . $line;
            }

            if ($curLine > $endLine) {
                break;
            }
        }


        return ($code);
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

