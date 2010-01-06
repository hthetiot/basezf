<?php
/**
 * Handler class in /BazeZF/Error
 *
 * @category  BazeZF
 * @package   BaseZF_Error
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

abstract class BaseZF_Error_Handler
{
    private static $_oldErrorhandler;

    private static $_oldExceptionhandler;

    private static $_errorReporting;

    //
    // Handle PHP Error
    //

    static public function registerErrorHandler($handlerCallback = null)
    {
        if (is_null($handlerCallback)) {
            $handlerCallback = array(get_class(), 'handleError');
        }

        // prevent stack error
        Zend_Loader::loadClass('BaseZF_Error_Exception');

        // Register new callback and backup old ones
        // To convert normal error has exception
        self::$_oldErrorhandler = set_error_handler($handlerCallback);

        return self::$_oldErrorhandler;
    }

   /**
    *
    */
   static public function unregisterErrorHandler()
   {
        if (isset(self::$_oldErrorhandler)) {
            set_error_handler(self::$_oldErrorhandler);
        }
   }

    static public function handleError($errno, $errstr = null, $file = null, $line = 0, array $errcontext = array())
    {
         // If error_reporting() == 0 then it was a suppressed error with
         // the @-operator and we don't want to handle that kind of errors !

         // Else it use error_reporting value from func or ini setting on handler start and
         // compare (($errno & $error_reporting) == $errno) to check if it sould display
         if (error_reporting() != 0 && ($errno & error_reporting()) == $errno) {

            $errstr = '(' . self::getErrorType($errno) . ') ' . $errstr;

            throw new BaseZF_Error_Exception($errstr, $errno, $file, $line, $errcontext);
         }
    }

    //
    // Handle PHP Exception
    //

    /**
     *
     */
    static public function registerExceptionHandler($handlerCallback)
    {
        if (is_null($handlerCallback)) {
            $handlerCallback = array(get_class(), 'handleException');
        }

        // handle all exception
        self::$_oldExceptionhandler = set_exception_handler($handlerCallback);

        return self::$_oldExceptionhandler;
    }

    /**
     *
     */
    static public function unregisterExceptionHandler()
    {
        if (isset(self::$_oldExceptionhandler)) {
            set_exception_handler(self::$_oldExceptionhandler);
        }
    }

    static public function handleException(Exception $e)
    {
        // @todo
    }

    //
    // Useful functions
    //

    /**
     *
     */
    static public function getErrorType($errorNo)
    {
        static $errortypes;

        // generate list of available level with label.
        if (!isset($errortypes)) {

            $missingErrortypes = array(
                'E_DEPRECATED'      => 8192,
                'E_USER_DEPRECATED' => 16384,
                'E_ALL'             => 30719,
            );

            // add possible missing error level
            foreach ($missingErrortypes as $errortypeConst => $errortypeValue) {
                if (!defined($errortypeConst)) define($errortypeConst, $errortypeValue);
            }

            // add label to error level
            $errortypes = array (
                E_ERROR             => 'Error',
                E_WARNING           => 'Warning',
                E_PARSE             => 'Parsing Error',
                E_NOTICE            => 'Notice',
                E_CORE_ERROR        => 'Core Error',
                E_CORE_WARNING      => 'Core Warning',
                E_COMPILE_ERROR     => 'Compile Error',
                E_COMPILE_WARNING   => 'Compile Warning',
                E_USER_ERROR        => 'User Error',
                E_USER_WARNING      => 'User Warning',
                E_USER_NOTICE       => 'User Notice',
                E_STRICT            => 'Runtime Notice',
                E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
                E_DEPRECATED        => 'Run-time notices',
                E_USER_DEPRECATED   => 'User-generated warning message',
                E_ALL               => 'Fatal Error',
            );
        }

        return isset($errortypes[$errorNo]) ? $errortypes[$errorNo] : false;
    }

    /**
     *
     */
    static public function debugException(Exception $e, $debuggerClass = 'BaseZF_Error_Debugger')
    {
        // prevent loop stack error
        Zend_Loader::loadClass($debuggerClass);

        $debugger = new $debuggerClass($e);
    }

    /**
     *
     */
    static public function sendExceptionByMail(Exception $e, $from, $to, $subjectPrefix = null)
    {
        // set default prefix as $_SERVER['HTTP_HOST'] then $_SERVER['SERVER_NAME'] then localhost
        if (is_null($subjectPrefix)) {
            $subjectPrefix = '[' . isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost') . ']';
        }

        // generate mail datas
        $subject = $subjectPrefix . ' - Exception Report: ' . wordlimit_bychar($e->getMessage(), 50);
        $body = $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine();

        // send mail throw Zend_Mail
        $mail = new Zend_Mail();

        $mail->setSubject($subject)
             ->setFrom($from)
             ->setBodyText($body);

        $emails = explode(',', $to);
        foreach ($emails as $email) {
            $mail->addTo($email);
        }

        $att = $mail->createAttachment(var_export($_GET, true), Zend_Mime::TYPE_TEXT);
        $att->filename = 'GET.txt';
        $att = $mail->createAttachment(var_export($_POST, true), Zend_Mime::TYPE_TEXT);
        $att->filename = 'POST.txt';

        // send session dump only if exists
        if (session_id() != null) {
            $att = $mail->createAttachment(var_export($_SESSION, true), Zend_Mime::TYPE_TEXT);
            $att->filename = 'SESSION.txt';
        }

        $att = $mail->createAttachment(var_export($_SERVER, true), Zend_Mime::TYPE_TEXT);
        $att->filename = 'SERVER.txt';

        $att = $mail->createAttachment($e->getTraceAsString(), Zend_Mime::TYPE_TEXT);
        $att->filename = 'backtraceExeption.txt';

        $mail->send();
    }
}

