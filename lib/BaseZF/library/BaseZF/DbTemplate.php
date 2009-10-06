<?php
/**
 * DbTemplate class in /BaseZF
 *
 * @category   BaseZF
 * @package    BaseZF_DbItem
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_DbTemplate
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
    private function _replaceBeginTagInTemplate($template, $data)
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
                    if ($data[$collectionIndex] instanceof BaseZF_DbCollection) {
                        $dataLimit = $data[$collectionIndex]->count();
                    } else {
                        $dataLimit = count($data[$collectionIndex]);
                    }
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
                        throw new BaseZF_DbTemplate_Exception(sprintf('Can\'t parse string: "%s"', $evalString));
                    }

                    $replaceTextArray[] = $caseResult;

                } catch (Exception $e) {
                    throw new BaseZF_DbTemplate_Exception(sprintf('Unable to find template vars values for: "%s"', $evalString));
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
        $dbItemParts = explode(':', $index);

        // data is available ?
        if (!isset($data[$dbItemParts[0]]) ) {
            throw new BaseZF_DbTemplate_Exception(sprintf('There are no template vars values for "%s"', $dbItemParts[0]));
        }

        // manage BaseZF_DbItem
        if ($data[$dbItemParts[0]] instanceof BaseZF_DbItem) {

            $object = $data[$dbItemParts[0]];

            unset($dbItemParts[0]);
            if ( count($dbItemParts) > 1 ) {

                $lastPart = array_pop($dbItemParts);
                $dbItemParts = array_map("ucfirst", $dbItemParts);
                $evalString = '$object = $object->' . implode('->', $dbItemParts) .';';

                try {

                    $evalResult = @eval($evalString);

                    if ( $evalResult === false ) {
                        throw new BaseZF_DbTemplate_Exception(sprintf('Can\'t parse string: "%s"', $evalString));
                    }

                } catch (Exception $e) {
                    throw new BaseZF_DbTemplate_Exception(sprintf('Unable to find template vars values for "%s"', $index));
                }

            } else {
                $lastPart = $dbItemParts[1];
            }

            // check if callable if it is a function
            if( ($pos = strpos($lastPart, '(')) !== false ) {
                $functionName = substr($lastPart, 0, $pos);
                if (!is_callable(array($object, $functionName))) {
                    throw new BaseZF_DbTemplate_Exception(sprintf('No such method "%s" for "%s"', $lastPart, $index));
                }
            }

            $value = '';
            $evalString = '$value = $object->' . $lastPart .';';

            try {

                $evalResult = @eval($evalString);
                if ( $evalResult === false ) {
                    throw new BaseZF_DbTemplate_Exception(sprintf('Can\'t parse string: "%s"', $evalString));
                }

                if (is_bool($value)) {
                    $value = intval($value);
                }

            } catch (Exception $e) {
                throw new BaseZF_DbTemplate_Exception(sprintf('Unable to find template vars values for "%s"', $index));
            }

            return self::_encodeBySpecialChars($value);

        // manage BaseZF_DbCollection
        } elseif ($data[$dbItemParts[0]] instanceof BaseZF_DbCollection) {

            throw new BaseZF_DbTemplate_Exception('Unable to display a DbCollection as vars');

        // manage array assoc
        } else if (is_array($data[$dbItemParts[0]])) {

            if (!isset($dbItemParts[1])) {
                throw new BaseZF_DbTemplate_Exception('Unable to display a Array as vars');
            }

            return self::_encodeBySpecialChars($data[$dbItemParts[0]][$dbItemParts[1]]);
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

            if (strpos($value, '{json}') !== false) {

                $value = Zend_Json::decode(str_replace('{json}', '', $value), true);

                switch ($value['type']) {

                    case 'Item':
                        $value = BaseZF_DbItem::getInstance($value['table'], $value['id']);
                    break;

                    case 'Collection':
                        $value = new BaseZF_DbCollection($value['table'], $value['id']);
                    break;
                }
            }
        }

        return $data;
    }

    /**
     *
     */
    static protected function _encodeDbObject($id, $table, $type)
    {
        $tmp = array (
           'id'     => $id,
           'table'  => $table,
           'type'   => $type,
        );

        return '{json}' . Zend_Json::encode($tmp);
    }

    /**
     *
     */
    static protected function _encodeData($data)
    {
        foreach ($data as $key => &$item) {

            if ( $item instanceof BaseZF_DbItem ) {
                $item = self::_encodeDbObject($item->getId(), $item->getTable(), 'Item');
            } else if ( $item instanceof BaseZF_DbCollection ) {
                $item = self::_encodeDbObject($item->getIds(), $item->getTable(), 'Collection');
            } else {
                throw new BaseZF_DbTemplate_Exception(sprintf('Unable to encode class "%s" with template engine', get_class($item)));
            }
        }

        return Zend_Json::encode($data);
    }
}

