<?php
/**
 * BaseZF_Framework_View_Helper_GoogleAnalytics class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 *
 * See http://www.scribd.com/doc/2261328/InstallingGATrackingCode
 *
 * <code>
 * // in layout or init of controller
 * $trackerId = '123';
 * $googleAnalytics = $this->GoogleAnalytics($trackerId, array(
 *     array('setVar', 'sex:f'),
 *     array('setVar', 'member_id:' . $memberId),
 * ));
 *
 * // from controller or view
 * $googleAnalytics->setVar($trackerId, 'another_var');
 * </code>
 */

class BaseZF_Framework_View_Helper_GoogleAnalytics
{
    /**
     * Tracker options instance
     */
    static protected $_trackerOptionsByIds = array();

    /**
     * Available Trackers options
     */
    static protected $_availableOptions = array
    (
        // Standard Options
        'trackPageview',
        'setVar',

        // ECommerce Options
        'addItem',
        'addTrans',
        'trackTrans',

        // Tracking Options
        'setClientInfo',
        'setAllowHash',
        'setDetectFlash',
        'setDetectTitle',
        'setSessionTimeOut',
        'setCookieTimeOut',
        'setDomainName',
        'setAllowLinker',
        'setAllowAnchor',

        // Campaign Options
        'setCampNameKey',
        'setCampMediumKey',
        'setCampSourceKey',
        'setCampTermKey',
        'setCampContentKey',
        'setCampIdKey',
        'setCampNoKey',

        // Other
        'addOrganic',
        'addIgnoredOrganic',
        'addIgnoredRef',
        'setSampleRate',
    );

    /**
     *
     * @param string $trackerId the google analytics tracker id
     * @param array
     *
     * @return $this for more fluent interface
     */
    public function GoogleAnalytics($trackerId = null, array $options = array())
    {
        if (!is_null($trackerId)) {
            $this->trackPageview($trackerId);

            if (!empty($options)) {
                $this->addTrackerOptions($trackerId, $options);
            }
        }

        return $this;
    }

    /**
     * Alias to _addTrackerOption
     *
     * @param string $optionsName
     * @param array $optionsArgs
     *
     * @return $this for more fluent interface
     */
    public function __call($optionsName, $optionsArgs)
    {
        if (in_array($optionsName, self::$_availableOptions) === false) {
            throw new BaseZF_Exception('Unknown "' . $optionFunc . '" GoogleAnalytics options');
        }

        if (empty($optionsArgs)) {
            throw new BaseZF_Exception('Missing TrackerId has first Argument on "$this->GoogleAnalytics->' . $optionFunc . '()" function call');
        }

        $trackerId = array_shift($optionsArgs);

        $this->_addTrackerOption($trackerId, $optionsName, $optionsArgs);

        return $this;
    }

    /**
     * Add options from array
     *
     * @param string $trackerId the google analytics tracker id
     * @param array of array option with first value has option name
     *
     * @return $this for more fluent interface
     */
    public function addTrackerOptions($trackerId, array $options)
    {
        foreach ($options as $optionsArgs) {

            $optionsName = array_shift($optionsArgs);

            $this->_addTrackerOption($trackerId, $optionsName, $optionsArgs);
        }

        return $this;
    }

    /**
     * Add a tracker option
     *
     * @param string $trackerId the google analytics tracker id
     * @param string $optionsName option name
     * @param array $optionsArgs option arguments
     *
     * @return $this for more fluent interface
     */
    protected function _addTrackerOption($trackerId, $optionsName, array $optionsArgs = array())
    {
        $trackerOptions = &$this->_getTrackerOptions($trackerId);

        array_unshift($optionsArgs, $optionsName);

        $trackerOptions[] = $optionsArgs;

        return $this;
    }

    /**
     * Get tracker's options by tracker id
     *
     * @param string $trackerId the google analytics tracker id
     *
     * @return array an array of options for requested tracker id
     */
    protected function &_getTrackerOptions($trackerId)
    {
        if (!isset(self::$_trackerOptionsByIds[$trackerId])) {
            self::$_trackerOptionsByIds[$trackerId] = array();
        }

        return self::$_trackerOptionsByIds[$trackerId];
    }

    //
    // Render
    //

    /**
     * Cast to string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Rendering Google Anaytics Tracker script
     */
    public function toString()
    {
        $xhtml = array();
        $xhtml[] = '<script type="text/javascript">';
        $xhtml[] = 'var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");';
        $xhtml[] = 'document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));';
        $xhtml[] = '</script>';

        $xhtml[] = '<script type="text/javascript">';
        $xhtml[] = 'try {';

        $i = 0;
        foreach (self::$_trackerOptionsByIds as $trackerId => $options) {

            // build tracker name
            $trackerInstance = 'pageTracker' . ($i > 0 ? $i : null);

            // init tracker
            $xhtml[] = 'var ' . $trackerInstance . ' = _gat._getTracker("' . $trackerId . '");';

            // add options
            foreach ($options as $optionsData) {

                // build tracker func call
                $optionName = '_' . array_shift($optionsData);

                // escape options arg
                $optionArgs = array();
                foreach ($optionsData as $arg) {
                    $optionArgs[] = is_numeric($arg) ? $arg : '"' . addslashes($arg) . '"';
                }

                // add options
                $xhtml[] = $trackerInstance . '.' . $optionName . '(' . implode(',', $optionArgs) . ');';
            }

            $i++;
        }

        $xhtml[] = '} catch(err) {}</script>';
        $xhtml[] = '</script>';

        return implode("\n", $xhtml);
    }
}
