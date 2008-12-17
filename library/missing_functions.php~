<?php
/**
 * missing_functions.php
 *
 * @category   BaseZF_Library
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

//
// Example Function
//
if (!function_exists('example')) {

    /**
     * This is a example function
     *
     * @param string $string a little string
     * @param string $array a little array
     * @return array $array value
     */
    function example($string, array $array = array())
    {
        return $array;
    }
}

if (!function_exists('define_if_not')) {

	/**
	 * Define a constant only if not defined
	 *
	 * @param string $constant Constant name
	 * @param void $value Constant value
	 *
	 * @return defined with success return true then false
	 */
	function define_if_not($constant, $value) {

		return defined($constant) or define($constant, $value);
	}
}

if (!function_exists('errorHandler')) {

    function exception_error_handler($errno, $errstr, $errfile, $errline ) {

        $ignoredErrNo = array(
            E_WARNING,
        );

        if (in_array($errno, $ignoredErrNo)) {
            return;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    set_error_handler("exception_error_handler");
}


if (!function_exists('__')) {

    /**
     * Handler around _() gettext function
     *
     * @param string $string no transalted string
     * @return string $string transalted string
     */
    function __($string)
    {
        return _($string);
    }

}

if (!function_exists('array_set_current')) {

	/**
	 * Set current position of array
	 *
	 * @param array $array to update position by reference
	 * @param void $key requested position of array
	 *
	 * @return void new array potision
	 */
	function array_set_current(array &$array, $key){
	   reset($array);
	   while (current($array)!==FALSE){
		   if (key($array) == $key) {
			   break;
		   }
		   next($array);
	   }
	   return current($array);
	}
}

if (!function_exists('hash_element_by_deep')) {

	/**
	 * Return a directory where element_id is store
	 *
	 * @param unknown_type $element_id
	 * @param unknown_type $deep_dir
	 * @param unknown_type $deep_dirs
	 * @return unknown
	 */
	function hash_element_by_deep($element_id, $deep_dir, $deep_dirs) {
		if (strlen($element_id) > ($deep_dir * ($deep_dirs + 1))) {
			return false;
		}

		return wordwrap( str_pad( floor( $element_id / pow(10, $deep_dir) ), $deep_dir * $deep_dirs, '0', STR_PAD_LEFT ), $deep_dir, '/', true );
	}
}

if (!function_exists('mb_ucfirst')) {

    /**
     * @todo doc
     */
	function mb_ucfirst($string, $encoding = 'UTF-8') {
    	if ($encoding) {
			return mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding) . mb_substr($string, 1, mb_strlen($string, $encoding) - 1, $encoding);
		} else {
			return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1, mb_strlen($string) - 1);
		}
	}
}

if (!function_exists('wordlimit')) {

    /**
     * @todo doc
     */
    function wordlimit($string, $length = 50, $ellipsis = "...")
    {
       $words = explode(' ', $string);
       if (count($words) > $length)
           return implode(' ', array_slice($words, 0, $length)) . $ellipsis;
       else
           return $string;
    }
}

if (!function_exists('wordlimit_bychar')) {

    /**
     * @todo doc
     */
    function wordlimit_bychar($string, $length = 50, $ellipsis = "...")
    {
		if (mb_strlen($string) < $length) {
			return $string;
		}

		return substr($string, 0, $length) . $ellipsis;
    }
}

if (!function_exists('parseCSS')) {

    /**
     * @todo doc
     */
    function parseCSS($filename)
    {
        $fp = fopen($filename,"r");
        $css = fread($fp, filesize ($filename));
        fclose($fp);

        $css = preg_replace("/[\s,]+/","",$css);
        $css_class = preg_split("/}/", $css);

        while (list($key,$val) = each ($css_class)) {

            $aCSSObj = preg_split("/{/",$val);

            if (!isset($aCSSObj[1])) {
                continue;
            }

            $a = preg_split("/;/",$aCSSObj[1]);
            while(list($key,$val0) = each ($a))
            {
                if($val0 !='') {
                    $aCSSSub=preg_split("/:/",$val0);
                    $aCSSItem[$aCSSSub[0]]=$aCSSSub[1];
                }
            }

            $aCSS[$aCSSObj[0]]=$aCSSItem;
            unset($aCSSItem);
        }

        return $aCSS;
    }
}

if (!function_exists('count_days')) {

    // Will return the number of days between the two dates passed in

    /**
     * @todo doc
     */
    function count_days( $a, $b )
    {
        // First we need to break these dates into their constituent parts:
        $gd_a = getdate( $a );
        $gd_b = getdate( $b );

        // Now recreate these timestamps, based upon noon on each day
        // The specific time doesn't matter but it must be the same each day
        $a_new = mktime( 12, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year'] );
        $b_new = mktime( 12, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year'] );

        // Subtract these two numbers and divide by the number of seconds in a
        // day. Round the result since crossing over a daylight savings time
        // barrier will cause this time to be off by an hour or two.
        return round( abs( $a_new - $b_new ) / 86400 );
    }
}

if (!function_exists('stringToAscii')) {

    /**
     * Convert a string to ascii
     *
     * @param string $string a little string
     * @return array $array value
     */
    function stringToAscii($string)
    {
        $transliteration =  array(
        "À" => "A","Á" => "A","Â" => "A","Ã" => "A","Ä" => "A",
        "Å" => "A","Æ" => "A","Ā" => "A","Ą" => "A","Ă" => "A",
        "Ç" => "C","Ć" => "C","Č" => "C","Ĉ" => "C","Ċ" => "C",
        "Ď" => "D","Đ" => "D","È" => "E","É" => "E","Ê" => "E",
        "Ë" => "E","Ē" => "E","Ę" => "E","Ě" => "E","Ĕ" => "E",
        "Ė" => "E","Ĝ" => "G","Ğ" => "G","Ġ" => "G","Ģ" => "G",
        "Ĥ" => "H","Ħ" => "H","Ì" => "I","Í" => "I","Î" => "I",
        "Ï" => "I","Ī" => "I","Ĩ" => "I","Ĭ" => "I","Į" => "I",
        "İ" => "I","Ĳ" => "IJ","Ĵ" => "J","Ķ" => "K","Ľ" => "K",
        "Ĺ" => "K","Ļ" => "K","Ŀ" => "K","Ł" => "L","Ñ" => "N",
        "Ń" => "N","Ň" => "N","Ņ" => "N","Ŋ" => "N","Ò" => "O",
        "Ó" => "O","Ô" => "O","Õ" => "O","Ö" => "Oe","Ø" => "O",
        "Ō" => "O","Ő" => "O","Ŏ" => "O","Œ" => "OE","Ŕ" => "R",
        "Ř" => "R","Ŗ" => "R","Ś" => "S","Ş" => "S","Ŝ" => "S",
        "Ș" => "S","Š" => "S","Ť" => "T","Ţ" => "T","Ŧ" => "T",
        "Ț" => "T","Ù" => "U","Ú" => "U","Û" => "U","Ü" => "Ue",
        "Ū" => "U","Ů" => "U","Ű" => "U","Ŭ" => "U","Ũ" => "U",
        "Ų" => "U","Ŵ" => "W","Ŷ" => "Y","Ÿ" => "Y","Ý" => "Y",
        "Ź" => "Z","Ż" => "Z","Ž" => "Z","à" => "a","á" => "a",
        "â" => "a","ã" => "a","ä" => "ae","ā" => "a","ą" => "a",
        "ă" => "a","å" => "a","æ" => "ae","ç" => "c","ć" => "c",
        "č" => "c","ĉ" => "c","ċ" => "c","ď" => "d","đ" => "d",
        "è" => "e","é" => "e","ê" => "e","ë" => "e","ē" => "e",
        "ę" => "e","ě" => "e","ĕ" => "e","ė" => "e","ƒ" => "f",
        "ĝ" => "g","ğ" => "g","ġ" => "g","ģ" => "g","ĥ" => "h",
        "ħ" => "h","ì" => "i","í" => "i","î" => "i","ï" => "i",
        "ī" => "i","ĩ" => "i","ĭ" => "i","į" => "i","ı" => "i",
        "ĳ" => "ij","ĵ" => "j","ķ" => "k","ĸ" => "k","ł" => "l",
        "ľ" => "l","ĺ" => "l","ļ" => "l","ŀ" => "l","ñ" => "n",
        "ń" => "n","ň" => "n","ņ" => "n","ŉ" => "n","ŋ" => "n",
        "ò" => "o","ó" => "o","ô" => "o","õ" => "o","ö" => "oe",
        "ø" => "o","ō" => "o","ő" => "o","ŏ" => "o","œ" => "oe",
        "ŕ" => "r","ř" => "r","ŗ" => "r","ś" => "s","š" => "s",
        "ť" => "t","ù" => "u","ú" => "u","û" => "u","ü" => "ue",
        "ū" => "u","ů" => "u","ű" => "u","ŭ" => "u","ũ" => "u",
        "ų" => "u","ŵ" => "w","ÿ" => "y","ý" => "y","ŷ" => "y",
        "ż" => "z","ź" => "z","ž" => "z","ß" => "ss","ſ" => "ss");

       return str_replace( array_keys( $transliteration ), array_values( $transliteration ), $string);
    }
}
