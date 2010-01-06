<?php
/**
 * BaseZF_Framework_View_Helper_UwaExportLink class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
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

            case 'live':
                $url = 'http://my.live.com/?s=1&add=' . urlencode($uwaServerUrl . '/widget/live?'. urlencode($widgetUrl . '?') . $optionsParams);
                //$url = 'http://spaces.live.com/spacesapi.aspx?wx_action=create&wx_url=' . urlencode($uwaServerUrl . '/widget/live?'. urlencode($widgetUrl) . $optionsParams);
                break;


            case 'opera':
            case 'dashboard':
            case 'frame':
            case 'screenlets':
            case 'jil':
            case 'vista':
            case 'blogger':
            case 'chrome':
                $url = $uwaServerUrl . '/widget/' . $environmentName . '?uwaUrl=' . urlencode($widgetUrl) . $optionsParams;
                break;

            default:
                throw new Exception(sprintf('Unable to generate link for environment name %s', $environmentName));
        }

        return  preg_replace('/&/', '&amp;', $url);
    }
}

