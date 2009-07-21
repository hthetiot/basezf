<?php
/**
 * Handler class in /BazeZF/Error
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_Error_Handler
{
    private static $_oldErrorhandler;

    private static $_errorReporting;

    static public function registerErrorHandler()
    {
        // prevent stack error
        Zend_Loader::loadClass('BaseZF_Error_Exception');

        // get  current error level
        self::$_errorReporting = ini_get('error_reporting');

        self::$_oldErrorhandler = set_error_handler(array(get_class(), 'handleError'));
    }

    static public function unregisterErrorHandler()
    {
        if (isset(self::$_oldErrorhandler)) {
            set_error_handler(self::$_oldErrorhandler);
        }
    }

    static public function handleError($errno, $errstr, $file, $line, array $errcontext)
    {
           // If error_reporting() == 0 then it was a suppressed error with
        // the @-operator and we don't want to handle that kind of errors !

        // Else it use error_reporting value from func or ini setting on handler start and
        // compare (($errno & $error_reporting) == $errno) to check if it sould display

        if (error_reporting() != 0 && ($errno & self::$_errorReporting) == $errno) {

            $errstr = '(' . self::getErrorType($errno) . ') ' . $errstr;

            // strange Segmentation fault temporarily issue
            if (E_STRICT == $errno) {
                return;
            }

            throw new BaseZF_Error_Exception($errstr, $errno, $file, $line, $errcontext);
            exit();
        }
    }

    static public function getErrorType($errorNo)
    {
        $errortype = array (
            E_ERROR              => 'Error',
            E_WARNING            => 'Warning',
            E_PARSE              => 'Parsing Error',
            E_NOTICE             => 'Notice',
            E_CORE_ERROR         => 'Core Error',
            E_CORE_WARNING       => 'Core Warning',
            E_COMPILE_ERROR      => 'Compile Error',
            E_COMPILE_WARNING    => 'Compile Warning',
            E_USER_ERROR         => 'User Error',
            E_USER_WARNING       => 'User Warning',
            E_USER_NOTICE        => 'User Notice',
            E_STRICT             => 'Runtime Notice',
            E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
        );

        return isset($errortype[$errorNo]) ? $errortype[$errorNo] : false;

    }

    static public function debugException(Exception $e, $debuggerClass = 'BaseZF_Error_Debugger')
    {
        // prevent loop stack error
        Zend_Loader::loadClass($debuggerClass);

        $debugger = new $debuggerClass($e);
        $debugger->render();
    }

    static public function sendExceptionByMail(Exception $e, $from, $to, $subjectPrefix = null)
    {
        // set default prefix as $SERVER['HTTP_HOST'] then $SERVER['SERVER_NAME'] then localhost
        if (is_null($subjectPrefix)) {
            $subjectPrefix = '[' . isset($SERVER['HTTP_HOST']) ? $SERVER['HTTP_HOST'] : (isset($SERVER['SERVER_NAME']) ? $SERVER['SERVER_NAME'] : 'localhost') . '] ';
        }

        // generate mail datas
        $subject = $subjectPrefix . 'Exception Report: ' . wordlimit_bychar($e->getMessage(), 50);
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

