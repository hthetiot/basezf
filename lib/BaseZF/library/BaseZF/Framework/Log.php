<?php
/**
 * Log class in /BazeZF/Framework
 *
 * @category   BazeZF
 * @package    BazeZF_Framework
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_Log extends Zend_Log
{

    const TABLE   = 8;  // Table: table messages

    /**
     * Factory to construct the Zend_Log and add writers based on the
     * configuration
     *
     * $config can be an array of an instance of Zend_Config
     *
     * @param mixed $config Array or instance of Zend_Config
     * @return Zend_Log
     */
    public static function factory($config = array())
    {
        // check config param
        if($config instanceof Zend_Config) {
            $config = $config->toArray();
        } else if(!is_array($config)) {
            throw new BazeZF_Framework_Log_Exception(sprintf('%s::%s first param must be an array or instance of Zend_Config', __CLASS__, __FUNC__));
        }

        // Do we have one or more writers configured?
        if(!is_array(current($config))) {
            $config = array($config);
        }

        $logger = new Zend_Log();

        // load priority
        foreach($config['priorities'] as $priorityName => $priority) {

            // convert priority const has integer
            if (!is_numeric($priority)) {
                $priority = constant($priority);
            }

            $logger->addPriority($priorityName, (int) $priority);
        }

        // load writers
        foreach($config['writers'] as $writer) {

            // skip disabled writer
            if (isset($writer['enable']) && !$writer['enable']) {
                continue;
            }

            $writerObj = self::_loadWriter($writer['writerName'], ((isset($writer['writerParams'])) ? $writer['writerParams'] : array()));

            // load writer filters
            if(isset($writer['filterName'])) {
                $filterObj = self::_loadFilter($writer['filterName'], ((isset($writer['filterParams'])) ? $writer['filterParams'] : array()));
                $writerObj->addFilter($filterObj);
            }

            $logger->addWriter($writerObj);
        }

        // load writer filters
        foreach($config['filters'] as $filter) {
            $filterObj = self::_loadFilter($filter['filterName'], ((isset($filter['filterParams'])) ? $filter['filterParams'] : array()));
            $logger->addFilter($filterObj);
        }

        // add default writer if no writer was added
        if (!isset($writerObj)) {
            $writer = new Zend_Log_Writer_Null();
            $logger->addWriter($writer);
        }

        return $logger;
    }

    /**
     *
     */
    static private function _loadWriter($writerClassName, array $writerConfig)
    {
        Zend_Loader::loadClass($writerClassName);

        // avoid bad Zend_Log_Writer_ constructor params
        switch ($writerClassName) {

            case 'Zend_Log_Writer_Stream':
                $writerConfig['stream'] = (isset($writerConfig['stream']) ? $writerConfig['stream'] : null);
                $writerConfig['mode'] = (isset($writerConfig['mode']) ? $writerConfig['mode'] : 'a');
                $writerObj = new $writerClassName($writerConfig['stream'], $writerConfig['mode']);
                break;

            case 'Zend_Log_Writer_Mail':
                $mail = new Zend_Mail();
                $mail->setSubject($writerConfig['subject']);
                $mail->addTo($writerConfig['to']);
                $mail->setFrom($writerConfig['from']);
                $writerObj = new $writerClassName($mail);
                break;

            case 'Zend_Log_Writer_FireBug':
                $writerObj = new $writerClassName();

                // set TABLE Priority to TABLE style
                $writerObj->setPriorityStyle(BaseZF_Framework_Log::TABLE, 'TABLE');
                break;

            default:
                $writerObj = new $writerClassName($writerConfig);
        }

        if(!$writerObj instanceof Zend_Log_Writer_Abstract) {
            throw new BazeZF_Framework_Log_Exception("Writer class '$writerClassName' does not extend Zend_Log_Writer_Abstract");
        }

        return $writerObj;
    }

    /**
     *
     */
    static private function _loadFilter($filterClassName, $filterConfig)
    {
        Zend_Loader::loadClass($filterClassName);

        // convert priority const has integer
        if (!is_numeric($filterConfig['priority'])) {
            $filterConfig["priority"] = constant($filterConfig["priority"]);
        }

        $filterObj = new $filterClassName($filterConfig['priority'], $filterConfig['operator']);

        if(!$filterObj instanceof Zend_Log_Filter_Interface) {
            throw new BazeZF_Framework_Log_Exception("Filter class '$filterClassName' does not extend Zend_Log_Filter_Interface");
        }

        return $filterObj;
    }
}

