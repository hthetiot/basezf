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
    /**
	 *
	 */
    protected static $_registryNameSpace = __CLASS__;

	/**
	 *
	 */
    public static function setConfig($config)
	{
        if (is_string($config)) {
            $config = self::_loadConfig($config);
        } elseif (is_array($config)) {
            $config = new Zend_Config($config);
        } elseif ($config instanceof Zend_Config) {
           // nothing to do..
        } else {
            throw new MyProject_Exception('Invalid config provided; must be location of config file, a config object, or an array');
        }

		return self::register('config', $config);
	}

    /**
	 * @todo add cache
	 */
    protected static function _loadConfig($file, $environment)
	{
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'ini':
                $config = new Zend_Config_Ini($file, $environment);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($file, $environment);
                break;

            case 'php':
            case 'inc':
                $config = include $file;
                if (!is_array($config)) {
                    throw new MyProject_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
                break;

            default:
                throw new MyProject_Exception('Invalid configuration file provided; unknown config type');
        }

        return self::setConfig($config);
	}

	/**
	 *
	 */
    private static function _createLogger()
	{
		// get config
        $config = MyProject::registry('config');

		// disabled log
		if (!$config->log->enable) {
			return false;
		}

		// init logger
		$logger = new Zend_Log();

		// add default priority for db profiler
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

    /**
	 *
	 */
    private static function _createDb()
	{
        // get config
        $config = MyProject::registry('config');

        // init db
        $db = Zend_Db::factory($config->db);
        $db->query('SET NAMES utf8;');

        return $db;
	}

    /**
	 *
	 */
    private static function _createDbCache()
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

        // create cache instance
        $cache = Zend_Cache::factory(
			'Core',
			$config->dbcache->adapter,
			$frontendOptions,
			$backendOptions
		);

        return $cache;
	}

	/**
	 *
	 */
    private static function _createSession()
	{
        // get config
        $config = MyProject::registry('config');

        // set session config if available
        if (isset($config->session)) {
            Zend_Session::setOptions($config->session->toArray());
        }

        return new Zend_Session_Namespace('Default');
	}

    /**
     * Initilize Locale registry
     *
     * @param string $locale iso code of locale, fr_FR for example
     * @return object Zend_Locale instance of current locale
     */
    private static function _createLocale($locale = null)
	{
		try {

            // get from cookie if not forced
            if (is_null($locale) && isset($_COOKIE['lang'])) {
                $locale = $_COOKIE['lang'];
            }

            if (is_null($locale)) {
                throw new Exception('No locale found in cookies or param');
            }

            $locale = new Zend_Locale($locale);

        } catch (Exception $e) {

            $auth = MyProject::registry('auth');

            // get from current member, if logged in
            if ($auth->hasIdentity()) {

                // @todo get from member
                $locale = new Zend_Locale('auto');

            // get from browser->env->ip
            } else {
                $locale = new Zend_Locale('auto');
            }

            // set in cookies, if not equal or empty
            if (!isset($_COOKIE['lang']) || $_COOKIE['lang'] != $locale->toString()) {
                setcookie('lang', $locale->toString(), 0, '/');
            }
        }

        // init translation setting
        self::_initLocaleTranslation($locale);

        return $locale;
	}

    /**
     * Set current used locale
     *
     * @param string $locale iso code of locale, fr_FR for example
     * @return object Zend_Locale instance of current locale
     */
    public static function setCurrentLocale($locale = null)
	{
        $locale = self::_createLocale($locale);

        Zend_Registry::set('locale', $locale);

        return $locale;
    }

    /**
     * Init Translation system using gettext
     *
     * @param object $locale instance of Zend_Locale
     * @return void
     */
    protected static function _initLocaleTranslation(Zend_Locale $locale)
    {
        $defaultDomain = 'message';
        $availableDomains = array(
            'message',
            'time',
            'validate',
        );

        // init available gettext domains
        foreach ($availableDomains as $domain) {
            bindtextdomain($domain, LOCALES_PATH);
            bind_textdomain_codeset($domain, 'UTF-8');
        }

        // set default domain
        textdomain($defaultDomain);

		// mandatory for gettext
		putenv('LANGUAGE=' . $locale);

		setlocale(LC_MESSAGES, $locale . '.utf8');
		setlocale(LC_TIME, $locale . '.utf8');
    }

    /**
	 *
	 */
    private static function _createAuth()
	{
        return Zend_Auth::getInstance();
	}

    /**
	 *
	 */
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

			$object = Zend_Registry::get(self::$_registryNameSpace . ':' . $registryKey);

			return $object;

		// Just In Time object creation
		} catch (Zend_Exception $e) {

			$callbackFunc = '_create' . implode(array_map('ucfirst', explode('_', $registryKey)));

			if (!is_callable(array(__CLASS__, $callbackFunc))) {
				throw new MyProject_Exception('Non existing registryCallBack for "' . $registryKey. '" missing function "' . $callbackFunc . '"');
			}

			// call
			$object = MyProject::$callbackFunc();

            self::register($registryKey, $object);

			return $object;
		}
	}

    public static function register($registryKey, $object, $force = false)
	{
        $fullRegistryKey = self::$_registryNameSpace . ':' . $registryKey;

        if (!$force && Zend_Registry::isRegistered($fullRegistryKey)) {
            throw new MyProject_Exception('All ready registered key "' . $registryKey. '", use force param to overwrite all ready registered key in registry');
        }

        return Zend_Registry::set($fullRegistryKey, $object);
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
}
