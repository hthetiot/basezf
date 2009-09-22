<?php
/**
 * Yaml class in /BazeZF/Framework/Config
 *
 * @category   BazeZF
 * @package    BazeZF_Framework
 * @author     Sean P. O. MacCath-Moran
 *
 * Sean P. O. MacCath-Moran
 * zendcode@emanaton.com
 * http://www.emanaton.com
 */

/**
 * @see Zend_Config
 */
require_once 'Zend/Config.php';

/**
 * @see Syck extension fallback to Spyc lib class
 */
if (!function_exists('syck_load')) {
    Zend_Loader::loadClass('Spyc');
}

class BaseZF_FrameWork_Config_Yaml extends Zend_Config {

    /**
    * Holds the options values as passed in.
    *
    * @var boolean
    */
    protected $_options;

    /**
    * This class mirrors Zend_Config_Ini with a few exceptions. Notably,
    * there is no need to parse dots in property names, and a bug has been
    * corrected where in values being set as properties on the object cause
    * an error when access is then attempted (since the __get function is
    * overwritten in the parent object), the fix for this being to store the
    * passed in options locally.
    *
    * Loads the section $section from the config file $filename for
    * access facilitated by nested object properties.
    *
    * If the section name contains a < then the section name to the right
    * is loaded and included into the properties. Note that the keys in
    * this $section will override any keys of the same
    * name in the sections that have been included via <.
    *
    * If the $section is null, then all sections in the yaml file are loaded.
    *
    * example yaml file:
    *      production:
    *        debug: false
    *        db:
    *          adapter: PDO_MYSQL
    *          params:
    *            host: someserver
    *            username: wiz_user
    *            password: "wiz"
    *            dbname: system_wiz
    *            nonesense: value
    *
    *      staging < production:
    *        debug: true
    *        db:
    *          params:
    *              host: localhost
    *              username: wiz
    *              password: "$ecret1"
    *              dbname: wiz
    *
    * after calling $data = new ZExt_Config_Yaml($file, 'staging'); then
    *      $data->debug === true
    *      $data->db->params->host === "localhost"
    *      $data->db->params->nonesense === "value"
    *
    * The $options parameter may be provided as either a boolean or an array.
    * If provided as a boolean, this sets the $allowModifications option of
    * Zend_Config. If provided as an array, there are two configuration
    * directives that may be set. For example:
    *
    * $options = array(
    *   'allowModifications' => false,
    *   'skipExtends'      => false
    *  );
    *
    * @param  string        $filename
    * @param  string|null   $section
    * @param  boolean|array $options
    * @throws Zend_Config_Exception
    * @return void
    */
    public function __construct($filename, $section = null, $options = false) {

        // If filename is empy, we cannot proceed.
        if (empty($filename)) {

            /**
            * @see Zend_Config_Exception
            */
            require_once 'Zend/Config/Exception.php';

            throw new Zend_Config_Exception('Filename is not set');
        }

        // if syck has not been loaded, we cannot proceeed.
        if (!function_exists('syck_load')) {

            require_once 'Zend/Config/Exception.php';

            throw new Zend_Config_Exception('Syck extension is not loaded');
        }

        // check to see what options have been passed and store them
        $allowModifications = false;
        $this->_options = array();

        if (is_bool($options)) {

            // boolean passed in for optins, so assume this is meant as the
            // allowModifications settings
            $allowModifications = $options;

        } elseif (is_array($options)) {

            // if options is an array, then collect several settings
            if (isset($options['allowModifications'])) {
                $allowModifications = (bool) $options['allowModifications'];
            }

            if (isset($options['skipExtends'])) {
                $this->_options['skipExtends'] = (bool) $options['skipExtends'];
            }
        }

        // If the yaml file cannot be read without errors, then we cannot proceed.
        // use error handler from the parent config object
        //set_error_handler(array($this, '_loadFileErrorHandler'));
        // Warnings and errors are suppressed
        $ymlArray = syck_load(file_get_contents($filename));

        //restore_error_handler();
        // Check if there was a error while loading file
        if ($this->_loadFileErrorStr !== null) {
            /**
            * @see Zend_Config_Exception
            */
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception($this->_loadFileErrorStr);
        }

        // load and process the section requested by the "Section" variable (or return
        // all sections if no section specified)
        $preProcessedArray = array();
        foreach ($ymlArray as $key => $data) {
            $bits = explode('<', $key);
            $thisSection = trim($bits[0]);
            switch (count($bits)) {
                // no parent section specified, so make no modification
            case 1:
                $preProcessedArray[$thisSection] = $data;
                break;
                // store the name of the parent section in a special array key AT THE TOP
                // of the array.
            case 2:
                $extendedSection = trim($bits[1]);
                $preProcessedArray[$thisSection] =
                array_merge(array(';extends'=>$extendedSection), $data);
                break;

            default: // this cannot be!

                /**
                * @see Zend_Config_Exception
                */
                require_once 'Zend/Config/Exception.php';
                throw new Zend_Config_Exception(
                    'Section "'.$thisSection.'" may not extend multiple sections in $filename'
                    );
            }
        }

        if (null === $section) {

            // if no section specified, then process and return all sections
            $dataArray = array();
            foreach ($preProcessedArray as $sectionName => $sectionData) {

                if(!is_array($sectionData)) {
                    $dataArray = array_merge_recursive($dataArray, array($sectionName=>$sectionData));
                } else {
                    $dataArray[$sectionName] = $this->_processExtends($preProcessedArray, $sectionName);
                }
            }

            parent::__construct($dataArray, $allowModifications);

        } elseif (is_array($section)) {

            // if multiple sections specified, then return them
            $dataArray = array();
            foreach ($section as $sectionName) {
                if (!isset($preProcessedArray[$sectionName])) {

                    /**
                    * @see Zend_Config_Exception
                    */
                    require_once 'Zend/Config/Exception.php';

                    throw new Zend_Config_Exception("Section '$sectionName' cannot be found in $filename");
                }
                $processedArray = $this->_processExtends($preProcessedArray, $sectionName);
                $dataArray = $this->array_merge_recursive_distinct($processedArray, $dataArray);

            }
            parent::__construct($dataArray, $allowModifications);

        } else {

            if (!isset($preProcessedArray[$section])) {

               /**
                * @see Zend_Config_Exception
                */
                require_once 'Zend/Config/Exception.php';

                throw new Zend_Config_Exception("Section '$section' cannot be found in $filename");
            }
            parent::__construct($this->_processExtends($preProcessedArray, $section), $allowModifications);
        }

        $this->_loadedSection = $section;
    }

    /**
    * Helper function to process each element in the section and handle
    * the "extends" inheritance keyword.
    *
    * @param  array  $ymlArray
    * @param  string $section
    * @param  array  $config
    * @throws Zend_Config_Exception
    * @return array
    */
    protected function _processExtends($ymlArray, $section, $config = array())
    {
        $thisSection = $ymlArray[$section];

        foreach ($thisSection as $key => $value) {
            if (strtolower($key) == ';extends') {
                if (isset($ymlArray[$value])) {
                    $this->_assertValidExtend($section, $value);
                    $skipExtends =
                    array_key_exists('skipExtends', $this->_options) ?
                    $this->_options['skipExtends'] :
                    false;
                    if (!$skipExtends) {
                        $config = $this->_processExtends($ymlArray, $value, $config);
                    }
                } else {

                    /**
                    * @see Zend_Config_Exception
                    */
                    require_once 'Zend/Config/Exception.php';

                    throw new Zend_Config_Exception("Section '$section' cannot be found");
                }
            } else {
                if (array_key_exists($key, $config)) {
                    if (is_array($value) && is_array($config[$key])) {
                        $config[$key] = $this->array_merge_recursive_distinct($config[$key], $value);
                    } elseif (is_array($value) || is_array($config[$key])) {
                        // throw error
                    } else {
                        $config[$key] = $value;
                    }
                } else {
                    $config[$key] = $value;
                }
            }
        }
        return $config;
    }

    /**
    * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
    * keys to arrays rather than overwriting the value in the first array with the duplicate
    * value in the second array, as array_merge does. I.e., with array_merge_recursive,
    * this happens (documented behavior):
    *
    * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
    *     => array('key' => array('org value', 'new value'));
    *
    * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
    * Matching keys' values in the second array overwrite those in the first array, as is the
    * case with array_merge, i.e.:
    *
    * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
    *     => array('key' => array('new value'));
    *
    * Parameters are passed by reference, though only for performance reasons. They're not
    * altered by this function.
    *
    * @param array $array1
    * @param mixed $array2
    * @return array
    * @author daniel@danielsmedegaardbuus.dk
    * @see http://us.php.net/manual/en/function.array-merge-recursive.php#89684
    */
    function &array_merge_recursive_distinct(array &$array1, &$array2 = null)
    {
        $merged = $array1;

        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {

                if (is_array($array2[$key])) {

                    if (array_key_exists($key, $merged)) {

                        $merged[$key] = is_array($merged[$key]) ?
                        $this->array_merge_recursive_distinct($merged[$key], $array2[$key]) :
                        $array2[$key];

                    } else {
                        $merged[$key] = $array2[$key];
                    }

                } else {
                    $merged[$key] = $val;
                }
            }
        }

        return $merged;
    }
}

