<?php
/**
 * MyProject.php
 *
 * @category   MyProject
 * @package    MyProject
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

final class MyProject
{

	// Registry CallBacks

	private static function _buildRegistryConfig()
	{
        // check config
        if(!file_exists(CONFIG_FILE)) {
            throw new MyProject_Exception('Missing config file on path: "' . CONFIG_FILE . '"');
        }

        $config = new Zend_Config_Ini(CONFIG_FILE, CONFIG_ENV);

        return $config;
	}

	private static function _buildRegistryLogger()
	{
		// get config
        $config = MyProject::registry('config');

		// disabled log
		if (!$config->log->enable) {
			return false;
		}

		// init logger
		$logger = new Zend_Log();

		// add default priority
		$logger->addPriority('TABLE', 8);

		// add priority per components
		$componentsEnables = $config->log->components->toArray();
		$components = array(
			'DBCOLLECTION' 	=> BaseZF_DbCollection::LOG_PRIORITY,
			'DBITEM' 		=> BaseZF_DbItem::LOG_PRIORITY,
			'DBQUERY' 		=> BaseZF_DbQuery::LOG_PRIORITY,
		);

		foreach($components as $name => $priority) {
			$logger->addPriority($name, $priority);

			if (!$componentsEnables[strtolower($name)] && isset($componentsEnables[strtolower($name)])) {
				$filter = new Zend_Log_Filter_Priority($priority, '!=');
				$logger->addFilter($filter);
			}
		}

		// add stream writer
		if ($config->log->writers->stream->enable) {
			$writer = new Zend_Log_Writer_Stream($config->log->writers->stream->path);
			$logger->addWriter($writer);
		}

		// add firebug writer
		if ($config->log->writers->firebug->enable) {
			$writer = new Zend_Log_Writer_Firebug();
			$writer->setPriorityStyle(8, 'TABLE');
			$logger->addWriter($writer);
		}

		// add default writer if no writer was added
		if (!isset($writer)) {
			$writer = new Zend_Log_Writer_Null();
			$logger->addWriter($writer);
		}

		return $logger;
	}

    private static function _buildRegistryDb()
	{
        // get config
        $config = MyProject::registry('config');

        // init db
        $db = Zend_Db::factory($config->db);
        $db->query('SET NAMES utf8');

        return $db;
	}

    private static function _buildRegistryDbCache()
	{
		// get config
        $config = MyProject::registry('config');

        $frontendOptions = array();
        if (isset($config->dbcache->frontend)) {
            $frontendOptions = $config->dbcache->frontend->toArray();
        }

        $backendOptions = array();
        if (isset($config->dbcache->backend)) {
            $backendOptions = $config->dbcache->backend->toArray();
        }

        // créer un objet Zend_Cache_Core
        $cache = Zend_Cache::factory(
			'Core',
			$config->dbcache->adapter,
			$frontendOptions,
			$backendOptions
		);

        return $cache;
	}

    private static function _buildRegistrySession()
	{

	}

    private static function _buildRegistryLocale()
	{
        return $locale = new Zend_Locale('auto');
	}

    private static function _buildRegistryAuth()
	{

	}

    private static function _buildRegistryAcl()
	{

	}

	// Registry Manager

	/**
     * Retrieves a registered shared object, where $registry_key is the
     * registered name of the object to retrieve.
     *
     * If the $registry_key argument is NULL, an array will be returned where
	 * the keys to the array are the names of the objects in the registry
	 * and the values are the class names of those objects.
     *
     * @see Zend_Registry::get()
     * @param string $registry_key The name for the object.
     * @throw Bahu_Exception
     * @return object The registered object.
     */
	public static function registry($registryKey = null, $refresh = false)
	{
		$registryKey = strtolower($registryKey);

		if (is_null($registryKey)) {
			return Zend_Registry::getInstance();
		}

		try {

			if ($refresh) {
				throw new MyProject_Exception('Get fresh "' . $registryKey. '" from registry');
			}

			$object = Zend_Registry::get($registryKey);

			return $object;

		// Just In Time object creation
		} catch (Zend_Exception $e) {

			$callbackFunc = '_buildRegistry' . ucfirst($registryKey);

			if (!is_callable(array('MyProject', $callbackFunc))) {
				throw new MyProject_Exception('Non existing registryCallBack for "' . $registryKey. '" missing function "' . $callbackFunc . '"');
			}

			// call
			$object = self::$callbackFunc();

			Zend_Registry::set($registryKey, $object);

			return $object;

			throw $e;
		}
	}

    //
    // Debug functions
    //

	/**
     * Create a log entry
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
	public static function log($msg, $level = Zend_Log::INFO)
	{
		$logger = MyProject::registry('logger');

		if ($logger->hasWriter()) {
			return $logger->log($msg, $level);
		}
	}

    static public function sendExceptionByMail(Exception $e)
    {
        $config = MyProject::registry('config');

        if (!$config->debug->report->enable) {
            return;
        }

        // generate mail datas
        $from	 = $config->debug->report->from;
        $to		 = $config->debug->report->to;
        $subject = '[' . MAIN_URL . ':' . CONFIG_ENV . '] Exception Report: ' . wordlimit_bychar($e->getMessage(), 50);
        $body = $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine();

        // sned mail throw Zend_Mail
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

    //
    // String functions
    //

    /**
     * @brief Escapes string
     *
     * @param string  $s The string to escape
     * @return The escaped string
     */
	static public function escape($s)
	{
		return htmlspecialchars($s);
	}

	/**
     * @brief Escapes string
     *
     * @param string $s The string to escape
     * @return The escaped string
     */
	static public function escapeJs($s)
	{
		return addslashes($s);
	}

    /**
     * Take a localized string and return an url valid representation of it
     *
     * @param  string $url
     * @return string
     */
	static public function cleanUrl($url)
	{
		/* convert the string to a 7bits representation */
		//
		$url = mb_convert_encoding(($url),
		'HTML-ENTITIES',
		'ISO-8859-15');

		$url = preg_replace(array('/&szlig;/',
		'/&(..)lig;/',
		'/&([aeiouAEIOU])uml;/',
		'/&(.)[^;]*;/'),
		array('ss',
		"$1",
		"$1",
		"$1"),
		$url);

		/* strip non alpha characters */
		$url = preg_replace(array('/[^[:alpha:]\d\.]/', '/-+/'), '-', $url);

		// remove eventual leading/trailing hyphens due to leading/trailing non-alpha chars
		return trim($url, '-');
	}
}
