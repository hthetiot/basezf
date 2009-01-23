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

	}

	private static function _buildRegistryLogger()
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
     * Zend_Log::EMERG   = 0;  // Urgence : le système est inutilisable
     * Zend_Log::ALERT   = 1;  // Alerte: une mesure corrective doit être prise immédiatement
     * Zend_Log::CRIT    = 2;  // Critique : états critiques
     * Zend_Log::ERR     = 3;  // Erreur: états d'erreur
     * Zend_Log::WARN    = 4;  // Avertissement: états d'avertissement
     * Zend_Log::NOTICE  = 5;  // Notice: normal mais état significatif
     * Zend_Log::INFO    = 6;  // Information: messages d'informations
     * Zend_Log::DEBUG   = 7;  // Debug: messages de déboguages
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
        throw $e;
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
