<?php
/**
 * BaseZF_Framework_View_Helper_JsonData class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
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

