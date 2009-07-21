<?php
/**
 * Uwa.php
 *
 * @category   BaseZF_Framework
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_Framework_Controller_Action_Uwa extends BaseZF_Framework_Controller_Action
{
    protected $_disableAcl = true;

    protected $_defaultLayout = 'uwa';

    static $availablePrefTypes = array(
        'text',     // renders a classic HTML input field. This is the default type      undefined
        'boolean',     // renders a HTML checkbox (true for checked, false for unchecked)     false
        'hidden',     // renders nothing, used to initalize a value     (empty)
        'range',     // quickly generates a list of integers inside an HTML listbox (select/option)     (empty range)
        'list',     // renders an HTML listbox (select/option)     (empty list)
        'password', // a HTML password field. The password is then saved on our servers, and never sent back to the client. To be used with the authentication method.     undefined
    );

    static $availablePrefOptions = array(
        'options',
        'label',           // indicates the HTML label describing the preference. Since it is actually displayed, the value should be short and descriptive
        'defaultValue',   // lets the user set a default value for the preference
        'onchange',          // targets a JavaScript callback widget method, fired when the setting is changed. Declaring onchange=refresh targets widget.refresh()
        'min',               // defines the minimal integer possible (range type only)
        'max',               // defines the maximal integer possible (range type only)
        'step',           // defines the incremental step between two values (range type only)
    );

    /**
     * $this->view->widget default values
     */
    protected $_defaultWidgetValues = array(
        'title'         => 'BaseZF Widget',
        'preferences' => array(),
        'metas' => array(
            'author'        => 'BaseZF',
            'website'       => 'http://example.com',
            'description'   => 'A Example.com widget.',
            'apiVersion'    => '1.0',
            'debugMode'     => 'false',

            // optional
            'screenshot'    => null,
            'thumbnail'     => null,
            'keywords'      => null,
            'version'       => null,
            'autoRefresh'   => null,
        ),
    );

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
        $this->view->widget = (object) $this->_defaultWidgetValues;

        // get current host
        $this->currentHost = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
        $this->view->currentHost = $this->currentHost;

        parent::init();
    }

    //
    // Manage Widget Preferences
    //

    /**
     * Set default widget title
     *
     * @return $this for more fluent interface
     */
    protected function _setTitle($title)
    {
        $this->view->widget->title = $title;

        return $this;
    }

    /**
     * Enable UWA debug mode
     *
     * @return $this for more fluent interface
     */
    protected function _enableDebug($enable = true)
    {
        $this->view->widget->metas['debugMode'] = $enable ? 'true' : 'false';

        return $this;
    }

    /**
     * Set a preference value
     *
     * @return $this for more fluent interface
     */
    protected function _setPreferenceValue($name, $value)
    {
        if(!isset($this->view->widget->preferences[$name])) {
            throw new BaseZF_Framework_Controller_Action_Uwa_Exception('Unable to find preference "' . $name . '", please add preference before set a new value');
        }

        $this->view->widget->preferences[$name]->value = $value;

        return $this;
    }

    /**
     * Add a preference choice
     *
     * @return $this for more fluent interface
     */
    protected function _addPreference($name, $type, $label = null, $value = null, array $options = array())
    {
        $this->view->widget->preferences[$name] = (object) array(
            'type'      => $type,
            'label'     => $label,
            'value'     => $value,
            'options'   => $options,
        );

        return $this;
    }

    //
    // Manage Widget Metas
    //

    /**
     * Set a UWA meta value
     *
     * @return $this for more fluent interface
     */
    protected function _setWidgetMetaValue($meta, $value)
    {
        if (!isset($this->view->widget->metas[$meta])) {
            throw new BaseZF_Framework_Controller_Action_Uwa_Exception('Illegal widget meta "' . $meta . '"');
        }

        $this->view->widget->metas[$meta] = $value;

        return $this;
    }
}

Class BaseZF_Framework_Controller_Action_Uwa_Exception extends BaseZF_Exception {}


