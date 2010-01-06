<?php
/**
 * MyProject_Console_Example class in /MyProject/Console
 *
 * @category  MyProject
 * @package   MyProject_Console
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/MyProject/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class MyProject_Console_Example extends BaseZF_Console
{
    protected function _run()
    {
        // log message
        $this->_log('hello info');
        $this->_notice('hello notice');
        $this->_debug('hello debug');
        $this->_warning('hello warning');

        $this->_addSemaphore('import');
        $this->_cleanSemaphore('import');

        $choice = $this->_ask('toto ?');
        $this->_log(sprintf('You enter <%s>', $choice));
    }
}

