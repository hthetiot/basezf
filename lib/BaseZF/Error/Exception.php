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
    static public $contextWidth = 6;

    public function __construct($message = null, $code = 0, $file = null, $line = null)
    {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
    }
   
    /**
     * Gets the interesting lines in the interesting file
     * 
     */
    protected function getContext()
    {
        $file = fopen($this->file, 'r');
        $beginLine = max(0, $this->line - self::$contextWidth / 2);
        $endLine = $beginLine + self::$contextWidth - 1;
        $context = '';
        $curLine = 0;
        
        while($line = fgets($file)) {
            $curLine++;
            if ($curLine >= $beginLine && $curLine <= $endLine) {
                $context .= $line;
            }
            
            if ($curLine > $endLine) {
                break;
            }
        }
        
        if (!preg_match('/^\<\?php/', ltrim($context))) {
            $context = "<?php\n" . $context;
        }
        
        return $context;
    }
}

