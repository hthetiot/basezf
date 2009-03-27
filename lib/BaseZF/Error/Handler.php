<?php
/**
 * Handler class in /BazeZF/Error
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Error_Handler
{
    static private $_INSTANCE = false;

    private $_oldErrorhandler = null;

    private function __construct()
    {
        // prevent stack error
        Zend_Loader::loadClass('BaseZF_Error_Exception');

        $this->_oldErrorhandler = set_error_handler(array($this, 'newErrorhandler'));
    }

    static public function replaceErrorHandler()
    {
        if (!self::$_INSTANCE instanceof self) {
            self::$_INSTANCE = new self();
        }

        return self::$_INSTANCE;
    }

    public function newErrorhandler($code, $message, $file, $line)
    {
	  // if error_reporting() == 0 then it was a
	  // suppressed error with the @-operator!
	  //(We don't want to handle that kind of errors!)
        if (error_reporting() != 0) {
            $message = '(' . $this->_getErrorType($code) . ') ' . $message;
            throw new BaseZF_Error_Exception($message, $code, $file, $line);
        }
    }

    static public function printException(Exception $e)
    {
        ?>
        <h1>An error occurred</h1>
        <h2><?php echo $e->getMessage() ?></h2>

        <h3>Exception information: </h3>
        <p>
            <b>Name:</b> <?php echo get_class($e) ?>
        </p>

        <h3>Stack trace:</h3>
        <pre><?php echo $e->getTraceAsString() ?></pre>

        <h3>Request Parameters:</h3>
        <?php
        exit();
    }

    static public function sendExceptionByMail(Exception $e, $from, $to)
    {
        // generate mail datas
        $subject = '[' . MAIN_URL . ':' . CONFIG_ENV . '] Exception Report: ' . wordlimit_bychar($e->getMessage(), 50);
        $body = $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine();

        // sned mail throw Zend_Mail
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

    private function _getErrorType($errorNo)
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

		return $errortype[$errorNo];
    }
}

