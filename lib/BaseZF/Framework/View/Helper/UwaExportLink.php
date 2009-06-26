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
				$url = 'http://www.netvibes.com/subscribe.php?module=UWA&moduleUrl=' . urlencode($widgetUrl) . $optionsParams;
                break;

			case 'google':
				$url = 'http://www.google.com/ig/add?moduleurl=' . urlencode($uwaServerUrl . '/widget/gspec?uwaUrl=' . urlencode($widgetUrl) . $optionsParams);
                break;

            case 'opera':
            case 'dashboard':
            case 'frame':
            case 'screenlets':
            case 'jil':
            case 'vista':
				$url = $uwaServerUrl . '/widget/' . $environmentName . '?uwaUrl=' . urlencode($widgetUrl) . $optionsParams;
                break;

            default:
                throw new Exception(sprintf('Unable to generate link for environment name %s', $environmentName));
		}

        return  preg_replace('/&/', '&amp;', $url);
	}
}
