<?php
/**
 * BaseZF_Notify class in /BazeZF
 *
 * Used to displaying a message and destuct it after it displayed, just a little notify system
 *
 * @category  BazeZF
 * @package   BazeZF_Core
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Notify
{
    /**
     * Singleton instance
     */
    private static $_INSTANCES = array();

    /**
     * Notifier namespace for storage
     */
    const DEFAULT_NAMESPACE = 'Notify';

    /**
     * Link to Zend_Session_Namespace instance
     */
    private $_storage;

    /**
     * Data Storage buffer
     */
    protected $_data = array();

    /**
     * Constructor
     */
    protected function __construct($nameSpace = self::DEFAULT_NAMESPACE)
    {
        $this->_retrieveStorage($nameSpace);

        return $this;
    }

    /**
     * Retreive instance of BazeZF_Notify, or create if not exist
     *
     * @return object instance of BazeZF_Notify
     */
     static public function getInstance($nameSpace = self::DEFAULT_NAMESPACE)
     {
        if (!isset(self::$_INSTANCES[$nameSpace])) {
            self::$_INSTANCES[$nameSpace] = new BaseZF_Notify($nameSpace);
        }

        return self::$_INSTANCES[$nameSpace];
     }

    /**
     * Set a new notifier with data
     *
     * @param string $notifierId nofifier id
     * @param array $data nofifier data
     */
    public function set($notifierId, $data = array())
    {
        if (!$this->isExist($notifierId)) {
            $this->_data[$notifierId] = array();
        }

        $this->_data[$notifierId][] = $data;

        $this->_updateStorage();

        return $this;
    }

    /**
     * Get notifier data
     *
     * @param string/array $notifierIds notifier ids
     * @param boolean $destruct destruct nofifier after read it
     * @return array notifier data
     */
    public function get($notifierIds, $destruct = true)
    {
        if (is_array($notifierIds)) {

            $return = array();
            foreach ($notifierIds as $notifierId) {
                $return[$notifierId] = $this->get($notifierId, $destruct);
            }

            return $return;

        } else {

            $notifierId = $notifierIds;

            if ($this->isExist($notifierId)) {

                $data = $this->_data[$notifierId];

                if ($destruct) {
                    $this->delete($notifierId);
                }

                return $data;
            }
        }

        return false;
    }

    /**
     * Get all notifiers data
     *
     * @param boolean $destruct destruct nofifier after read it
     * @return array of notifier data
     */
    public function getAll($destruct = true)
    {
        $data = array();

        foreach ($this->_data as $notifierId => $value) {
            if ($notifierData  = $this->get($notifierId, $destruct)) {
                $data[] = $notifierData;
            }
        }

        return $data;
    }

    /**
     * Delete a notifier record
     *
     * @param string $notifierId nofifier id
     * @return object instance of BazeZF_Notify
     */
    public function delete($notifierId)
    {
        if (isset($this->_data[$notifierId])) {
            unset($this->_data[$notifierId]);
        }

        $this->_updateStorage();

        return $this;
    }

    /**
     * Check if notifier exist
     *
     * @param string $notifierId
     * @return boolean true if exist else false
     */
    public function isExist($notifierId)
    {
        return isset($this->_data[$notifierId]);
    }

    /**
     * Get data from storage
     *
     * @return object instance of BazeZF_Notify
     */
    protected function _retrieveStorage($nameSpace)
    {
        // init session namespace
        $this->_storage = &new Zend_Session_Namespace($nameSpace);

        if (isset($this->_storage->data)) {
           $this->_data = $this->_storage->data;
        }
    }

    /**
     * Update data to storage
     *
     * @return object instance of BazeZF_Notify
     */
    protected function _updateStorage()
    {
        $this->_storage->data = $this->_data;

        return $this;
    }
}

