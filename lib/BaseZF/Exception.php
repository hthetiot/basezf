<?php
/**
 * Exception class in /BazeZF/
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Exception extends Exception
{
    public function __construct($message = null, $code = 0, $file = null, $line = null, $context = array())
    {
        parent::__construct($message, $code);

        if (!is_null($file)) {
            $this->file = $file;
        }

        if (!is_null($line)) {
            $this->line = $line;
        }

        if (!is_null($context)) {
            $this->context = $context;
        }
    }

    /**
     * Gets the interesting lines in the interesting file
     *
     */
    public function getSource($nbLine = 6)
    {
        $file = fopen($this->file, 'r');
        $beginLine = max(0, $this->line - $nbLine / 2);
        $endLine = $beginLine + $nbLine - 1;
        $code = '';
        $curLine = 0;

        while($line = fgets($file)) {

            $curLine++;
            if ($curLine >= $beginLine && $curLine <= $endLine) {
                $code .= $line;
            }

            if ($curLine > $endLine) {
                break;
            }
        }

        if (!preg_match('/^\<\?php/', ltrim($code))) {
            $code = "<?php\n" . $code;
        }

        return $code;
    }

    public function getContext()
    {
        return $this->context;
    }
}

