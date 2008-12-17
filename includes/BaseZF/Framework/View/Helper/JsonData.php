<?php
/**
 * JsonData.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_JsonData extends BaseZF_Framework_View_Helper_Abstract
{
    static $_jsonData = array();

    public function jsonData()
    {
        return $this;
    }

    public function addData($data, $key = null)
    {
        if ($key === null) {
            self::$_jsonData[] = $data;
        } else {
            self::$_jsonData[$key] = $data;
        }

        return $this;
    }

    public function setData(array $data)
    {
        self::$_jsonData = $data;

        return $this;
    }

    public function mergeData($data, $key = null)
    {
        if ($key === null) {
            self::$_jsonData = array_merge(self::$_jsonData, $data);
        } else {
            self::$_jsonData[$key] = array_merge(self::$_jsonData, $data);
        }

        return $this;
    }

    public function getData($key = null)
    {
        if ($key === null) {
            $data = self::$_jsonData;
        } else {
            $data = self::$_jsonData[$key];
        }

        return (!is_array($data) ? implode("\n", $data) : $data);
    }
}

