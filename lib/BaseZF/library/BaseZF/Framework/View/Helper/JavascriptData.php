<?php
/**
 * javascriptData.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framework
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_JavascriptData extends BaseZF_Framework_View_Helper_Abstract
{
    static $_jsData = array();

    public function javascriptData()
    {
        return $this;
    }

    public function addData($data, $key = null)
    {
        if ($key === null) {
            self::$_jsData[] = $data;
        } else {
            self::$_jsData[$key] = $data;
        }

        return $this;
    }

    public function mergeData($data, $key = null)
    {
        if ($key === null) {
            self::$_jsData = array_merge(self::$_jsData, $data);
        } else {
            self::$_jsData[$key] = array_merge(self::$_jsData, $data);
        }

        return $this;
    }

    public function getData($key = null)
    {
        if ($key === null) {
            $data = self::$_jsData;
        } else {
            $data = self::$_jsData[$key];
        }

        return (is_array($data) ? implode("\n", $data) : $data);
    }
}

