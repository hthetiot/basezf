<?php
/**
 * HeadWidgetPreferences.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_UwaExportLink extends BaseZF_Framework_View_Helper_Abstract
{
	public function UwaExportLink($environment, $widgetUrl, $uwaServerUrl)
	{
		switch ($environment)
		{

			case 'netvibes':
				return 'http://www.netvibes.com/subscribe.php?module=UWA&moduleUrl=' . urlencode($widgetUrl);

			case 'google':
				return 'http://www.google.com/ig/add?moduleurl=' . urlencode($uwaServerUrl) . '%2Fwidget%2Fgspec%3FuwaUrl%3D' . urlencode(urlencode($widgetUrl));

			case 'opera':
				return $uwaServerUrl . '/widget/opera?uwaUrl=' . urlencode($widgetUrl);

			case 'dashboard':
				return $uwaServerUrl . '/widget/dashboard?uwaUrl=' . urlencode($widgetUrl);
		}
	}
}
