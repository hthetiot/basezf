<?php
/**
 * BaseZF_Registry class in /BaseZF
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 *
 * This class should containt only registry builder
 *
 *
 * You can add some callback for BaseZF_Registry::registry('YourRegistryEntryName');
 * for that you just have to add a function like following.
 * <code>
 * protected function _create<YourRegistryEntryName>()
 * {
 *      return new SingletonClass();
 * }
 * </code>
 *
 */

abstract class BaseZF_Framework_Registry
{
    /**
     * @var string
     */
    protected $_registryNameSpace;

    /**
     * @var array
     */
    protected static $_instances = array();

    /**
     * Constructor
     *
     * @return void
     */
    protected function __construct()
    {
        // set registry nameSpace as registry class name
        $this->_registryNameSpace = get_class($this);
    }

    /**
     * Return Existing instance
     *
     * @param string $class Late Static Bindings issue
     *
     * @return object ready to use instance of BaseZF_Bean child class
     */
    public static function getInstance($class = __CLASS__)
    {
        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }

        return self::$_instances[$class];
    }

    /**
     * Set config of current bean instance
     *
     * @param void $config can be an array, an Zend_Config instance of a filename
     * @param void $environment config environment value (e.g production, staging, development,...)
     *
     * @return object Zend_Config instance
     */
    public function setConfig($config, $environment = null)
    {
        if (is_string($config)) {
            $config = $this->_loadConfigFromFile($config, $environment);
        } elseif (is_array($config)) {
            $config = new Zend_Config($config, $environment);
        } elseif ($config instanceof Zend_Config) {
           // nothing to do..
        } else {
            throw new BaseZF_Exception('Invalid config provided; must be location of config file, a config object, or an array');
        }

        return $this->register('config', $config, true);
    }

    /**
     * Load config from file
     *
     * @param void $file config file path
     * @param void $environment config environment value (e.g production, staging, development,...)
     *
     * @return object Zend_Config instance
     */
    protected function _loadConfigFromFile($file, $environment)
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
                    throw new BaseZF_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
                break;

            default:
                throw new BaseZF_Exception('Invalid configuration file provided; unknown config type');
        }

        return $config;
    }

    /**
     * Create a Zend_Log instance from config
     *
     * @return object Zend_Log instance
     */
    protected function _createLog()
    {
        // get config
        $config = $this->registry('config');

        // init logger
        $logger = BaseZF_Framework_Log::factory($config->log);

        return $logger;
    }

    /**
     * Create a Zend_Db instance from config
     *
     * @return object Zend_Db instance
     */
    protected function _createDb()
    {
        // get config
        $config = $this->registry('config');

        // init db
        $db = Zend_Db::factory($config->db);

        return $db;
    }

    /**
     * Create a Zend_Cache instance from config
     *
     * @return object Zend_Cache instance
     */
    protected function _createCache()
    {
        // get config
        $config = $this->registry('config');

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
     * Create a Zend_Session_Namespace instance from config using bean namespace
     *
     * @return object Zend_Session_Namespace instance
     */
    protected function _createSession()
    {
        // get config
        $config = $this->registry('config');

        // set session config if available
        if (isset($config->session)) {
            Zend_Session::setOptions($config->session->toArray());
        }

        return new Zend_Session_Namespace($this->_registryNameSpace);
    }

    /**
     * Initilize Locale registry
     *
     * @param string $locale iso code of locale, fr_FR for example
     *
     * @return object Zend_Locale instance of current locale
     */
    protected function _createLocale($locale = null)
    {
        try {

            // get from cookie if not forced
            if (is_null($locale) && isset($_COOKIE['lang'])) {
                $locale = $_COOKIE['lang'];
            }

            if (is_null($locale)) {
                throw new BaseZF_Exception('No locale found in cookies or param');
            }

            $locale = new Zend_Locale($locale);

            // only long locale name
            if (strpos($locale, '_') === false) {
                throw new BaseZF_Exception(sprintf('Invalide locale found with value %s', $locale));
            }

        } catch (BaseZF_Exception $e) {

            $auth = $this->registry('auth');

            // get from current member, if logged in
            if ($auth->hasIdentity()) {

                $identity = $auth->getIdentity();

                // @todo get from member
                if (is_array($identity) && isset($identity['locale'])) {
                    $locale = new Zend_Locale('auto');
                }

            // get from browser->env->ip
            } else {

                $locale = new Zend_Locale('auto');

                // only long locale name
                if (strpos($locale, '_') === false) {
                    $locale = new Zend_Locale($locale . '_' . strtoupper($locale));
                }
            }

            // set in cookies, if not equal or empty
            if (!isset($_COOKIE['lang']) || $_COOKIE['lang'] != $locale->toString()) {
                setcookie('lang', $locale->toString(), 0, '/');
            }
        }

        // init translation setting
        $this->_initLocaleTranslation($locale);

        return $locale;
    }

    /**
     * Set current used locale
     *
     * @param string $locale iso code of locale, fr_FR for example
     *
     * @return object Zend_Locale instance of current locale
     */
    public function setCurrentLocale($locale = null)
    {
        $locale = $this->_createLocale($locale);

        return $this->register('locale', $locale, true);
    }

    /**
     * Init Translation system using gettext
     *
     * @param object $locale instance of Zend_Locale
     *
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

        /*
        $gettextService = new BaseZF_Service_GetText();
        $gettextService->iniTranslation($locale, array('message'));
        */
    }

    /**
     * Create a Zend_Auth instance from config
     *
     * @return object Zend_Auth instance
     */
    protected function _createAuth()
    {
        return Zend_Auth::getInstance();
    }

    /**
     * Create a Zend_Acl instance from config
     *
     * @todo read CONFIG_ACL_ROLES and CONFIG_ACL_ROUTES using BaseZF ACL adapter
     *
     * @return object Zend_Acl instance
     */
    protected function _createAcl()
    {
        $acl = new Zend_Acl();

        return $acl;
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
     * @throw BazeZF_Exception
     * @return object The registered object.
     */
    public function registry($registryKey = null, $refresh = false)
    {
        $registryKey = strtolower($registryKey);

        if (is_null($registryKey)) {
            return Zend_Registry::getInstance();
        }

        try {

            if ($refresh) {
                throw new BaseZF_Exception('Get fresh "' . $registryKey. '" from registry');
            }

            $object = Zend_Registry::get($this->_registryNameSpace . ':' . $registryKey);

            return $object;

        // Just In Time object creation
        } catch (Zend_Exception $e) {

            $callbackFunc = '_create' . implode(array_map('ucfirst', explode('_', $registryKey)));

            if (!is_callable(array($this, $callbackFunc))) {
                throw new BaseZF_Exception('Non existing registryCallBack for "' . $registryKey. '" missing function "' . $callbackFunc . '"');
            }

            // call
            $object = $this->$callbackFunc();

            $this->register($registryKey, $object);

            return $object;
        }
    }

    /**
     * Save object in registry using bean namespace
     *
     * @param string $registryKey registry key value
     * @param void $object registry value
     * @param string $force erase existing registry entry with same name if exist
     *
     * @return void $object value
     */
    public function register($registryKey, $object, $force = false)
    {
        $fullRegistryKey = $this->_registryNameSpace . ':' . $registryKey;

        if (!$force && Zend_Registry::isRegistered($fullRegistryKey)) {
            throw new BaseZF_Exception('All ready registered key "' . $registryKey. '", use force param to overwrite all ready registered key in registry');
        }

        return Zend_Registry::set($fullRegistryKey, $object);
    }

    //
    // Debug functions
    //

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
    public function log($msg, $level = Zend_Log::INFO)
    {
        $logger = $this->registry('log');

        if ($logger && $logger instanceof Zend_Log) {
            return $logger->log($msg, $level);
        }
    }
}

