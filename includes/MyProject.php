<?php
/**
 * MyProject.php
 *
 * @category   MyProject
 * @package    MyProject
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 *
 * This class should containt only debug error repporting and registry features
 *
 *
 * You can add some callback for MyProject::registry('YourRegistryEntryName');
 * for that you just have to add a function like following.
 * <code>
 * private static function _create<YourRegistryEntryName>()
 * {
 *      return new SingletonClass();
 * }
 * </code>
 *
 */

final class MyProject
{
    // add your registry callback here...

    // Default Registry CallBacks

	private static function _createConfig()
	{
        // check config
        if(!file_exists(CONFIG_FILE)) {
            throw new MyProject_Exception('Missing config file on path: "' . CONFIG_FILE . '"');
        }

        $config = new Zend_Config_Ini(CONFIG_FILE, CONFIG_ENV);

        return $config;
	}

	private static function _createLogger()
	{
		// get config
        $config = Self::registry('config');

		// disabled log
		if (!$config->log->enable) {
			return false;
		}

		// init logger
		$logger = new Zend_Log();

		// add default priority for
        $dbProfilerLogPriority = 8;
		$logger->addPriority('TABLE', $dbProfilerLogPriority);

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
			$writer->setPriorityStyle($dbProfilerLogPriority, 'TABLE');
			$logger->addWriter($writer);
		}

		// add default writer if no writer was added
		if (!isset($writer)) {
			$writer = new Zend_Log_Writer_Null();
			$logger->addWriter($writer);
		}

		return $logger;
	}

    private static function _createDb()
	{
        // get config
        $config = Self::registry('config');

        // init db
        $db = Zend_Db::factory($config->db);
        $db->query('SET NAMES utf8;');

        return $db;
	}

    private static function _createDbCache()
	{
		// get config
        $config = Self::registry('config');

        $frontendOptions = array();
        if (isset($config->dbcache->frontend)) {
            $frontendOptions = $config->dbcache->frontend->toArray();
        }

        $backendOptions = array();
        if (isset($config->dbcache->backend)) {
            $backendOptions = $config->dbcache->backend->toArray();
        }

        // create cache instance
        $cache = Zend_Cache::factory(
			'Core',
			$config->dbcache->adapter,
			$frontendOptions,
			$backendOptions
		);

        return $cache;
	}

    private static function _createSession()
	{
        // get config
        $config = Self::registry('config');

        // set session config if available
        if (isset($config->session)) {
            Zend_Session::setOptions($config->session->toArray());
        }

        return new Zend_Session_Namespace('Default');
	}

    private static function _createLocale()
	{
        // @todo set current localte in cookies for better perfomance of Zend_Locale

        return $locale = new Zend_Locale('auto');
	}

    private static function _createAuth()
	{
        return Zend_Auth::getInstance();
	}

    private static function _createAcl()
	{
        // @todo read CONFIG_ACL_ROLES and CONFIG_ACL_ROUTES
        // with a BaseZF ACL adapter
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

			$callbackFunc = '_create' . implode(array_map('ucfirst', explode('_', $registryKey)));

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
		$logger = Self::registry('logger');

		if ($logger->hasWriter()) {
			return $logger->log($msg, $level);
		}
	}
}
