<?php
/**
 * BaseZF_Console class in /BaseZF
 *
 * @category  BaseZF
 * @package   BazeZF_Console
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

/* for signals handling */
declare (ticks = 1);

abstract class BaseZF_Console
{
    /**
     * @const LOCK_FILE_DIR directory where to write lock files
     */
    const LOCK_FILE_DIR = '/var/lock';

    /**
     * Zend Opts child class option
     */
    protected $_optionRules = array();

    /**
     * Zend Opts default class option
     */
    private $_defaultOptionRules = array(
        'help|h'            => 'Display usage',
        'quiet|q'           => 'Enable quiet mode to show error message only',
        'debug|d'           => 'Enable debug mode to show debug message',
        'verbose|v'         => 'Enable verbose mode to show notice message',
        'cleanSemaphore|c'  => 'Reset add semapore of current scripts',
        'logFilePath|l=s'   => 'File output log path',
    );

    /**
     * Path to default logFile
     */
    protected $_logFilePath;

    /**
     * Path to default logFile
     */
    protected $_sctiptPath;

    /**
     * The class can not be instancied directly. See Base_Console::factory instead
     */
    private function __construct()
    {
        // set set time limit and backup old one

        $this->_initOptionRules();
        $this->_init();

        // set script Path
        $this->_sctiptPath = $_SERVER['PWD'] . substr($_SERVER['SCRIPT_FILENAME'], 1);
    }

    public function __destruct()
    {
        // reset set time limit
    }

    /**
     * Get the instance of the singleton
     *
     * @param $className string the name of the derivated class
     * @return Base_Console instance
     */
    static public function factory($className)
    {
        $objectConsole = new $className();

        return $objectConsole;
    }

    /**
     * Run the application and abstract error
     */
    public function run()
    {
        try {

            // reset add semapore of current scripts
            if ($this->_opts->cleanSemaphore) {
                $this->_cleanSemaphore();
            }

            // notify application start
            $this->_log(sprintf('Start "%s"', implode(' ', $_SERVER['argv'])));

            // run application
            $this->_run();

            // notify application end with success
            $this->_log(sprintf('End "%s" scripts with success', implode(' ', $_SERVER['argv'])));

        // handle errors
        } catch (Exception $e) {

            // notify application end with error
            $this->_warning(sprintf('End "%s" scripts with error', implode(' ', $_SERVER['argv'])));

            // log error
            $this->_error(sprintf(
                '%s on file %s at line %d',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
        }
    }

    /**
     * Run the application
     */
    abstract protected function _run();

    /**
     * Init method called after construction. Overload this method to configure your object
     */
    protected function _init()
    {
    }

    /**
     * No cloning autorized !
     */
    protected function __clone()
    {
        throw new Exception(sprintf('Call %s on %s class is forbiden !', __FUNC__, __CLASS__));
    }

    /**
     * No toString autorized !
     */
    protected function __toString()
    {
        throw new Exception(sprintf('Call %s on %s class is forbiden !', __FUNC__, __CLASS__));
    }

    //
    // Options functions
    //

    /**
     * Sets options rules
     *
     * @param $optionRules array see Zend_Console_Getopt for format
     */
    protected function _initOptionRules()
    {
        $optionRules = array_merge($this->_optionRules, $this->_defaultOptionRules);

        $this->_opts = new Zend_Console_Getopt($optionRules);

        if ($this->_opts->help) {
            $this->help();
        }

        return $this;
    }

    /**
     * Display the help message of the application and exit
     * Nothing will be printed if there is no option
     */
    public function help()
    {
        if ($this->_opts) {
            echo $this->_opts->getUsageMessage();
            echo "\n";
        }

        exit(1);
    }

    protected function _isQuietMode()
    {
        return $this->_opts->quiet;
    }

    public function setQuietMode($quietMode = true)
    {
        $this->_opts->quiet = $quietMode;

        return $this;
    }

    protected function _isDebugMode()
    {
        return $this->_opts->debug;
    }

    public function setDebugMode($debugMode = true)
    {
        $this->_opts->debug = $debugMode;

        return $this;
    }

    protected function _isVerboseMode()
    {
        return $this->_opts->verbose;
    }

    public function setVerboseMode($verboseMode = true)
    {
        $this->_opts->verbose = $verboseMode;

        return $this;
    }

    public function setLogFilePath($logFilePath)
    {
        $this->_logFilePath = $logFilePath;

        return $this;
    }

    protected function _getLogFilePath()
    {
        // use defined logFile
        if (!empty($this->_opts->logFilePath)) {
            return $this->_opts->logFilePath;

        // use default logFile
        } else if (isset($this->_logFilePath)) {
            return $this->_logFilePath;
        }

        return false;
    }

    //
    // Messages functions
    //

    /**
     * My prompt func because realine is not default module
     */
    protected function _ask($string, $length = 1024)
    {
        static $tty;

        if (!isset($tty)) {

            if (substr(PHP_OS, 0, 3) == "WIN") {
                $tty = fopen("\con", "rb");
            } else if (!($tty = fopen("/dev/tty", "r"))) {
                $tty = fopen("php://stdin", "r");
            }
        }

        echo $string;
        $result = trim(fgets($tty, $length));
        return $result;
    }

    /**
     * Print a debug message
     *
     * @param $str string message to print
     */
    protected function _debug($str)
    {
        $this->_log($str, Zend_Log::DEBUG);

        return $this;
    }

    /**
     * Print a notice message
     *
     * @param $str string message to display
     */
    protected function _notice($str)
    {
        $this->_log($str, Zend_Log::NOTICE);

        return $this;
    }

    /**
     * Print a warning message
     *
     * @param $str string message to display
     */
    protected function _warning($str)
    {
        $this->_log($str, Zend_Log::WARN);

        return $this;
    }

    /**
     * Print an error message and exit the application
     *
     * @param $str string message to display
     * @param $exitCode int the exit value to use
     */
    protected function _error($str, $exitCode = 1)
    {
        $this->_log($str, Zend_Log::ERR);

        exit($exitCode);
    }

    //
    // Semaphore/Lock functions
    //

    /**
     * Checks for a semaphore presence. If not found create it.
     * If found write an error message and exits the application
     *
     * @param $semaphoreName string the semaphore name to check for

     */
    protected function _addSemaphore($semaphoreName, $retry = 0, $delay = 5)
    {
        $semaphoreFilePath = $this->_getSemaphoreFilePath();

        if (is_file($semaphoreFilePath)) {

            if (!is_writable($semaphoreFilePath)) {
                $this->_error(sprintf('Can not write semaphore file: "%s" to add semaphore "%s"', $semaphoreFilePath, $semaphoreName), 255);
            }

            if (!$data = file_get_contents($semaphoreFilePath)) {
                $this->_error(sprintf('Can not read semaphore file: "%s" to get semaphore "%s"', $semaphoreFilePath, $semaphoreName), 255);
            }

            $data = Zend_Json::decode($data);

            if (isset($data[$semaphoreName])) {

                list($semaphorePid, $semaphoreCreation) = $data[$semaphoreName];

                $this->_error(sprintf('Can not add semaphore "%s" cause it allready locked by "%d" PHP process ID at "%s"', $semaphoreName, $semaphorePid, date('Y-m-d H:i:s', $semaphoreCreation)), 255);

            } else {

                $data[$semaphoreName] = array(
                    getmypid(),
                    time(),
                );
            }

        } else {

            $data = array(
                $semaphoreName => array(
                    getmypid(),
                    time(),
                ),
            );
        }

        if (!file_put_contents($semaphoreFilePath, Zend_Json::encode($data))) {
            $this->_error(sprintf('Can not create semaphore file: "%s" to add semaphore "%s"', $semaphoreFilePath, $semaphoreName), 255);
        }

        return $this;
    }

    /**
     * Delete a semaphore or all semaphores
     *
     * @param $semaphoreName string the semaphore name to delete
     */
    protected function _cleanSemaphore($semaphoreName = null)
    {
        $semaphoreFilePath = $this->_getSemaphoreFilePath();

        // semaphore file exits ?
        if (is_file($semaphoreFilePath)) {

            // remove all semaphore
            if (is_null($semaphoreName)) {

                if (!unlink($semaphoreFilePath)) {
                    $this->_error(sprintf('Can not delete semaphore file: "%s"', $semaphoreFilePath), 255);
                }

            // remove only one semaphore
            } else {

                if (!$data = file_get_contents($semaphoreFilePath)) {
                    $this->_error(sprintf('Can not read semaphore file: "%s" to get semaphore "%s"', $semaphoreFilePath, $semaphoreName), 255);
                }

                $data = Zend_Json::decode($data);

                if (isset($data[$semaphoreName])) {
                    unset($data[$semaphoreName]);
                }

                if (!file_put_contents($semaphoreFilePath, Zend_Json::encode($data))) {
                    $this->_error(sprintf('Can not create semaphore file: "%s" to add semaphore "%s"', $semaphoreFilePath, $semaphoreName), 255);
                }
            }

        // warm missing semaphore file on remove specific semaphore only
        } else if (!is_null($semaphoreName)) {
            $this->_warning(sprintf('Can not unlock semaphore "%s" cause it is not locked', $semaphoreName));
        }

        return $this;
    }

    /**
     * Get current script semaphore file path
     *
     * @return string script semaphore file path
     */
    protected function _getSemaphoreFilePath()
    {
        static $lockFilePath;

        if (!isset($lockFilePath)) {
            $lockFilePath = self::LOCK_FILE_DIR . '/php-console-' . sha1(implode(' ', $_SERVER['argv']));
        }

        return $lockFilePath;
    }

    //
    // Log functions
    //

    /**
     *
     */
    protected function _getLoggerInstance()
    {
        static $logger;

        if (!isset($logger)) {

            $logger = new Zend_Log();
            $formatter = new Zend_Log_Formatter_Simple("%timestamp% [" . getmypid() . "]: %priorityName% - %message%" . PHP_EOL);

            // show errror only on quiet mode
            if ($this->_isQuietMode()) {
                $filter = new Zend_Log_Filter_Priority(Zend_Log::ERR, '=');
                $logger->addFilter($filter);
            }

            // show debug only in debug mode
            if (!$this->_isDebugMode()) {
                $filter = new Zend_Log_Filter_Priority(Zend_Log::DEBUG, '!=');
                $logger->addFilter($filter);
            }

            // show notice only in verbose
            if (!$this->_isVerboseMode()) {
                $filter = new Zend_Log_Filter_Priority(Zend_Log::NOTICE, '!=');
                $logger->addFilter($filter);
            }

            // write on standart output
            $writer = new Zend_Log_Writer_Stream('php://output');
            $writer->setFormatter($formatter);
            $logger->addWriter($writer);

            // write on log file or syslog only if debug is disable
            if (!$this->_isDebugMode()) {

                // isset logFilePath write of specific file
                if ($logFilePath = $this->_getLogFilePath()) {

                    $writer = new Zend_Log_Writer_Stream($logFilePath);
                    $writer->setFormatter($formatter);
                    $logger->addWriter($writer);

                // else write on syslog
                } else {

                    $writer = new Zend_Log_Writer_Syslog(array('application' => $this->_sctiptPath));
                    $writer->setFormatter($formatter);
                    $logger->addWriter($writer);
                }
            }
        }

        return $logger;
    }

    /**
     * Create a log entry using Zend_Log created by bean
     *
     * @param string $msg
     * @param int $level
     *
     * Possible value for $level
     *
     * Zend_Log::EMERG   = 0;  // Emergency: system is unusable
     * Zend_Log::ALERT   = 1;  // Alert: action must be taken immediately
     * Zend_Log::CRIT    = 2;  // Critical: critical conditions
     * Zend_Log::ERR     = 3;  // Error: error conditions
     * Zend_Log::WARN    = 4;  // Warning: warning conditions
     * Zend_Log::NOTICE  = 5;  // Notice: normal but significant condition
     * Zend_Log::INFO    = 6;  // Informational: informational messages
     * Zend_Log::DEBUG   = 7;  // Debug: debug messages
     *
     * @return Zend_Log::log results
     */
    protected function _log($msg, $level = Zend_Log::INFO)
    {
        $logger = $this->_getLoggerInstance();

        if ($logger instanceof Zend_Log) {
            return $logger->log($msg, $level);
        }
    }
}

