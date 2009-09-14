<?php
/**
 * Version class in /BaseZF/
 *
 * @category   BaseZF
 * @package    BaseZF_Version
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */
final class BaseZF_Version
{
    /**
     * BaseZF Framework version identification - see compareVersion()
     */
    const VERSION = '0.7';

    /**
     * Zend Framework required version identification - see checkZendVersion()
     */
    const ZF_VERSION = '1.8.4';

    /**
     * Compare the specified BaseZF Framework version string $version
     * with the current BaseZF_Version::VERSION of BaseZF Framework.
     *
     * @param  string  $version  A version string (e.g. "0.7.1").
     * @return boolean           -1 if the $version is older,
     *                           0 if they are the same,
     *                           and +1 if $version is newer.
     *
     */
    public static function compareVersion($version)
    {
        return version_compare($version, self::VERSION);
    }

    /**
     * Compare the specified Zend Framework version string $version
     */
    public static function checkZendVersion($version = null)
    {
        // check required version of Zend Framwork
        if (!is_null($version) && Zend_Version::compareVersion($version) > 0) {
            throw new BaseZF_Exception(sprintf('Please upgrade to a newer version of Zend Framework (%s require by Application)', $version));
        }

        // check basezf required version of Zend Framwork
        if (Zend_Version::compareVersion(self::ZF_VERSION) > 0) {
            throw new BaseZF_Exception(sprintf('Please upgrade to a newer version of Zend Framework (%s require by BaseZF Framework)', $version));
        }
    }
}
