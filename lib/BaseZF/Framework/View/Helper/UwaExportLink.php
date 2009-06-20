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
	public function UwaExportLink($environmentName, $widgetUrl, $uwaServerUrl, array $options = array())
	{

        $optionsParams = (!empty($options) ? '&' . http_build_query($options) : null);

		switch ($environmentName)
		{
			case 'netvibes':
				return 'http://www.netvibes.com/subscribe.php?module=UWA&moduleUrl=' . urlencode($widgetUrl) . $optionsParams;

			case 'google':
				return 'http://www.google.com/ig/add?moduleurl=' . urlencode($uwaServerUrl . '/widget/gspec?uwaUrl=' . urlencode($widgetUrl) . $optionsParams);

			case 'opera':
				return $uwaServerUrl . '/widget/opera?uwaUrl=' . urlencode($widgetUrl) . $optionsParams;

			case 'dashboard':
				return $uwaServerUrl . '/widget/dashboard?uwaUrl=' . urlencode($widgetUrl) . $optionsParams;

            case 'screenlets':
				return $uwaServerUrl . '/widget/screenlets?uwaUrl=' . urlencode($widgetUrl) . $optionsParams;

            case 'frame':
				return $uwaServerUrl . '/widget/frame?uwaUrl=' . urlencode($widgetUrl) . $optionsParams;

            default:
                throw new Exception(sprintf('Unable to generate link for environment name %s', $environmentName));
		}
	}
}
