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

    static public function registerErrorHandler()
    {
        // prevent stack error
        Zend_Loader::loadClass('BaseZF_Error_Exception');

		self::$_oldErrorhandler = set_error_handler(array(get_class(), 'handleError'));
    }

	static public function unregisterErrorHandler()
    {
		if (isset(self::$_oldErrorhandler)) {
			set_error_handler(self::$_oldErrorhandler);
		}
	}

    static public function handleError($code, $message, $file, $line, array $context)
    {
		// if error_reporting() == 0 then it was a
		// suppressed error with the @-operator!
		//(We don't want to handle that kind of errors!)
        if (error_reporting() != 0) {
			$message = '(' . self::getErrorType($code) . ') ' . $message;
            throw new BaseZF_Error_Exception($message, $code, $file, $line, $context);
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

		return new $debuggerClass($e);
	}

    static public function sendExceptionByMail(Exception $e, $from, $to)
    {
        // generate mail datas
        $subject = '[' . MAIN_URL . ':' . CONFIG_ENV . '] Exception Report: ' . wordlimit_bychar($e->getMessage(), 50);
        $body = $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine();

        // send mail throw Zend_Mail
        $mail = new Zend_Mail();

        $mail->setSubject($subject)
             ->setFrom($from)
             ->setBodyText($body);

        $emails = explode(' ', $to);
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

