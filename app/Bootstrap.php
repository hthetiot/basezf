<?php
/**
 * Bootstrap.php
 *
 * Main Bootstrap
 *
 * @category   MyProject
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

/**
 * Main BootStrap
 */
final class Bootstrap extends BaseZF_Bootstrap
{
	protected function _initMyProject()
	{
		MyProject::setEnvironment(CONFIG_ENV);
		MyProject::setConfigFilePath(CONFIG_FILE);

        MyProject::registry('locale');

	}

	/**
     * Get available routes
     */
    protected function _getRoutes()
    {
        return MyProject_Routes::fetch();
    }
}
