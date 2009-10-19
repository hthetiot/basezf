<?php
/**
 * Template class in /BaseZF
 *
 * @category   BaseZF
 * @package    BaseZF_Template
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Template
{
    /**
     * Template parts
     */
    private $_template = array();

    /**
     * Template parts rendered
     */
    private $_renderedTemplate = array();

    /**
     * Template vars
     */
    private $_data = array();

    //
    // Public API
    //

    /**
     * Constructor
     *
     */
    public function __construct($template = null, array $data = array()) {

        $this->setTemplate($template);
        $this->setData($data);
    }

    /**
     * Render Template with current var from data and return template
     *
     * @return void string of template or array for multipart template
     */
    public function render($forceProcess = false)
    {
        if (empty($this->_renderedTemplate) || $forceProcess == true) {
            $this->_processTemplate();
        }

        return $this->getRenderedTemplate();
    }

    /**
     * Set used Template
     *
     * @return $this for more fluent interface
     */
    public function setTemplate($template)
    {
        // simple template string became multiple with one part
        if (!is_array($template)) {
            $template = array($template);
        }

        $this->_template = $template;

        return $this;
    }

    /**
     * Get template value
     *
     * @return void string of template or array for multipart template
     */
    public function getTemplate()
    {
        // multiple template with one part became simple string
        if (count($this->_template) == 1) {
            return current($this->_template);
        }

        return $this->_template;
    }

    /**
     * Get rendered template value
     *
     * @return void string of template or array for multipart template
     */
    public function getRenderedTemplate()
    {
        if (count($this->_renderedTemplate) == 1) {
            return current($this->_renderedTemplate);
        }

        return $this->_renderedTemplate;
    }

    /**
     *
     * @return $this for more fluent interface
     */
    public function setData(array $data)
    {
        $this->_cleanRendered();

        $this->_data = $data;

        return $this;
    }

    /**
     * Get Template vars
     *
     * @return array template vars
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Update a template vars value
     *
     * @return $this for more fluent interface
     */
    public function setDataValue($key, $value)
    {
        $this->_cleanRendered();

        $this->_data[$key] = $value;

        return $this;
    }

    /**
     * Render Template if require only with current var from data and return template
     */
    public function __toString()
    {
        return $this->render(false);
    }

    /**
     * Serialize Class for sleeping
     *
     * @return array or preperties should save
     */
    public function __sleep()
    {
        return array(
            '_template',
            '_renderedTemplate',
            '_data',
        );
    }

    /**
     * Unserialize Class for wake up
     */
    public function __wakeup()
    {
    }

    //
    // Private Render API
    //

    /**
     * Clean Render
     */
    private function _cleanRendered()
    {
        // clean template rendered
        $this->_renderedTemplate = array();
    }

    /**
     * Parse template
     */
    private function _processTemplate()
    {
        $this->_cleanRendered();

        foreach ($this->_template as $templatePartName => $templatePartContent) {

            // process Begin/End
            $templatePartContent = self::_replaceBeginTagInTemplate($templatePartContent, &$this->_data);
            // end Begin/End

            // process Request
            //$templatePartContent = self::_replaceRequestTagInTemplate($templatePartContent, &$this->_data);
            // end Request

            // process CONST
            $templatePartContent = self::_replaceConstantTagInTemplate($templatePartContent);
            // end CONST

            // proccess Simple Tags
            $templatePartContent = self::_replaceSimpleTagInTemplate($templatePartContent, &$this->_data);
            // end Simple Tags

            // process IF
            $templatePartContent = self::_replaceCaseTagInTemplate($templatePartContent);
            // end IF

            // savve and escape
            $this->_renderedTemplate[$templatePartName] = self::_decodeBySpecialChars($templatePartContent);
        }

        return $this->_renderedTemplate;
    }

    /**
     *
     */
    private function _replaceBeginTagInTemplate($template, array $data)
    {
        $originalTextArray = array();
        $replacedTextArray = array();
        $tags = array();

        if (preg_match_all('/\[begin:\{(.*?)\}[:]*(.*?)\](.*?)\[end:\{(.*?)\}\]/is', $template, $tags)) {

            foreach ($tags[0] as $index => $tag) {

                $originalTextArray[] = $tag;
                $templateLimit = !empty($tags[2][$index]) ? intval($tags[2][$index]) : null;
                $titleIndex = $tags[1][$index];
                $collectionIndex = substr($titleIndex, 0, strlen($titleIndex)-1);
                $dataLimit = 0;

                if (isset($data[$titleIndex]) ) {

                    $dataLimit = intval($data[$titleIndex]);
                    $collectionIndex = $titleIndex;

                } elseif (isset($data[$collectionIndex])) {

                    if ($data[$collectionIndex] instanceof Iterator && $data[$collectionIndex] instanceof Countable) {
                        $dataLimit = $data[$collectionIndex]->count();
                    } else {
                        $dataLimit = count($data[$collectionIndex]);
                    }
                } else {
                    throw new BaseZF_Template_Exception(sprintf('Unable to find template vars values for: "%s"', $titleIndex));
                }

                $limit = ($templateLimit !== null ? min($templateLimit, $dataLimit) : $dataLimit);
                $iteratorTemplate = trim( $tags[3][$index]);
                $iteratorTemplaterReplacedTextArray = array();
                $iteratorData = $data;
                $i = 0;

                foreach ($data[$collectionIndex] as $item) {

                    if ( $i++ >= $limit ) {
                        break;
                    }

                    $iteratorData[$collectionIndex] = $item;
                    if (!is_array($item)) {
                        $item = array($item);
                    }

                    $iteratorData = array_merge($iteratorData, $item);
                    $iteratorTemplaterReplacedTextArray[] = self::_replaceSimpleTagInTemplate($iteratorTemplate, $iteratorData);
                }

                $replacedTextArray[] = implode("\n", $iteratorTemplaterReplacedTextArray);
            }
        }

        return str_replace($originalTextArray, $replacedTextArray, $template);
    }

    /**
     *
     */
    private function _replaceCaseTagInTemplate($template)
    {
        $replaceTextArray = array();
        $tags = array();

        if (preg_match_all('/\[if:([^\?]+)\?([^\]\[]+):([^\]\[]+)\]/is', $template, $tags)) {

            foreach ($tags[0] as $key => $tag) {

                // if it is not CASE template than escape string
                if ( strpos($tag, '[if:') !== false ) {

                    $if = trim($tags[1][$key]);

                    $operators = array('>', '<', '>=', '<=', '==', '!=');
                    $tmp = null;
                    $replacedIf = false;
                    foreach ($operators as $operator) {
                        $tmp = explode($operator, $if);
                        if ( count($tmp) > 1 ) {
                            $if = self::_quoteString($tmp[0]) . ' ' . $operator . ' ' . self::_quoteString($tmp[1]);
                            $replacedIf = true;
                            break;
                        }
                    }

                    if (!$replacedIf) {
                        $if = self::_quoteString($if);
                    }

                    $then = self::_quoteString($tags[2][$key]);
                    $else = self::_quoteString($tags[3][$key]);

                    $caseString = $if . ' ? ' . $then . ' : ' . $else;
                } else {
                    $caseString = $tag;
                }

                $evalString = '$caseResult = ' . $caseString . ';';
                try {

                    $evalResult = @eval($evalString);
                    if ( $evalResult === false ) {
                        throw new BaseZF_Template_Exception(sprintf('Can\'t parse string: "%s"', $evalString));
                    }

                    $replaceTextArray[] = $caseResult;

                } catch (Exception $e) {
                    throw new BaseZF_Template_Exception(sprintf('Unable to find template vars values for: "%s"', $evalString));
                }
            }

            // update template
            $template = str_replace($tags[0], $replaceTextArray, $template);

            return self::_replaceCaseTagInTemplate($template);

        } else {
            return $template;
        }
    }

    /**
     *
     */
    private function _replaceSimpleTagInTemplate($template, $data)
    {
        $tags = array();
        $replaceTextArray = array();
        if (preg_match_all('/\{([^}]+)\}/', $template, $tags)) {
            foreach ($tags[1] as $tag) {
                $replaceTextArray[] = htmlspecialchars(self::_getDataValue($data, $tag));
            }
        }

        return str_replace( $tags[0], $replaceTextArray, $template );
    }

    /**
     * @todo replace to route support
     */
    private static function _replaceRequestTagInTemplate($template, $data)
    {
        $tags = array();
        $replaceTextArray = array();
        if (preg_match_all('/\[action:(.*?)\]/', $template, $tags)) {
            foreach ($tags[1] as $tag) {
                $replaceTextArray[] = '/profile/request/action?request_id=' . $data['request_id'] . '&state=' . $tag;
            }
        }

        return str_replace( $tags[0], $replaceTextArray, $template );
    }

    /**
     *
     */
    private function _replaceConstantTagInTemplate($template)
    {
        $tags = array();
        $replaceTextArray = array();
        $const = '';
        if (preg_match_all('/\[const:(.*?)\]/', $template, $tags)) {
            foreach ($tags[1] as $tag) {
                $const = constant($tag);
                $replaceTextArray[] = $const !== null ? self::_encodeBySpecialChars($const) : '';
            }
        }
        return str_replace( $tags[0], $replaceTextArray, $template );
    }

    /**
     *
     */
    private static function _getDataValue(&$data, &$index)
    {
        $objectParts = explode(':', $index);

        // data is available ?
        if (!isset($data[$objectParts[0]]) ) {
            throw new BaseZF_Template_Exception(sprintf('There are no template vars values for "%s"', $objectParts[0]));
        }

        // manage ArrayAccess
        if ($data[$objectParts[0]] instanceof ArrayAccess) {

            $object = $data[$objectParts[0]];
            $index = $objectParts[1];

            // check if callable if it is a function
            if( ($pos = strpos($index, '(')) !== false ) {

                $functionName = substr($index, 0, $pos);

                if (!is_callable(array($object, $functionName))) {
                    throw new BaseZF_Template_Exception(sprintf('No such method "%s" on object "%s"', $functionName, get_class($object)));
                }

                $value = call_user_func(array($object, $functionName));

            // check if index exist
            } else {

                if (!isset($object[$index])) {
                    throw new BaseZF_Template_Exception(sprintf('No such property "%s" on object "%s"', $index, get_class($object)));
                }

                $value = $object[$index];
            }

            return self::_encodeBySpecialChars($value);

        // manage Countable Iterator
        } elseif ($data[$objectParts[0]] instanceof Iterator && $data[$objectParts[0]] instanceof Countable) {

            throw new BaseZF_Template_Exception('Unable to display an Iterator as vars');

        // manage array assoc
        } else if (is_array($data[$objectParts[0]])) {

            if (!isset($objectParts[1])) {
                throw new BaseZF_Template_Exception('Unable to display a Array as vars');
            }

            return self::_encodeBySpecialChars($data[$objectParts[0]][$objectParts[1]]);
        }

        // simple var from array key
        return self::_encodeBySpecialChars($data[$index]);
    }

    /**
     *
     */
    private static function _encodeBySpecialChars($string)
    {
        return str_replace(':', '#|#', str_replace('?', '#||#', $string));
    }

    /**
     *
     */
    private static function _decodeBySpecialChars($string)
    {
        return str_replace('#|#', ':', str_replace('#||#', '?', $string));
    }

    /**
     *
     */
    private static function _quoteString( $string )
    {
        $string = trim($string);
        $string = str_replace("$", "\$", $string);
        return is_numeric($string) ? $string : '"' . addslashes($string) . '"' ;
    }

    //
    // Private Template data encode and decode
    //

    /**
     *
     */
    static protected function _decodeData($dataString)
    {
        $data = Zend_Json::decode($dataString, true);

        foreach ($data as $key => &$value) {


        }

        return $data;
    }

    /**
     *
     */
    static protected function _encodeArrayAccess(ArrayAccess $object)
    {

    }

    /**
     *
     */
    static protected function _encodeIterator(Iterator $object)
    {

    }

    /**
     *
     */
    static protected function _encodeData($data)
    {
        foreach ($data as $key => &$object) {

            // class and object support
            if (is_object($object) || gettype($object) === 'object') {

                if ($object instanceof ArrayAccess ) {
                    $object = self::_encodeArrayAccess($object);
                } elseif ($object instanceof Iterator && $object instanceof Countable) {
                    $item = self::_encodeIterator($object);
                } else {
                    throw new BaseZF_Template_Exception(sprintf('Unable to encode object "%s" with BaseZF_Template engine', get_class($item)));
                }

            // encode others types
            } else {

            }
        }

        return Zend_Json::encode($data);
    }
}

