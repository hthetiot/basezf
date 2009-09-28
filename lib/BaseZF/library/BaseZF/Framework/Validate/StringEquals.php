<?php
/**
 * StringEquals class in /BazeZF/Framework/Validate
 *
 * @category   BazeZF
 * @package    BazeZF_Framework
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_Validate_StringEquals extends Zend_Validate_Abstract
{
    /**
     * Error codes
     * @const string
     */
    const NOT_MATCH = 'notMatch';
    const UNABLE_TO_MATCH = 'unableToMatch';

    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH         => 'Fields do not match',
        self::UNABLE_TO_MATCH   => 'Unable to compare fields',
    );

    /**
     * Sets validator options
     *
     * @param  string $field
     * @return void
     */
    public function __construct()
    {
        $this->_fields = func_get_args();
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the string length of $value is at least the min option and
     * no greater than the max option (when the max option is not null).
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $contextFields = array();
        foreach ($this->_fields as $fieldName) {

            // prevent partial validation
            if (!isset($context[$fieldName])) {
                $this->_error(self::UNABLE_TO_MATCH);
                return false;
            }

            $contextFields[] = $context[$fieldName];
        }

        if (call_user_func_array('strcmp', $contextFields) != 0) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}

