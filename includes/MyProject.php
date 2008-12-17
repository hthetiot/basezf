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
    //
    // Debug functions
    //

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
