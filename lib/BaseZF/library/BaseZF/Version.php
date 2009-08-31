<?php
/**
 * Version class in /BazeZF/
 *
 * @category   BazeZF
 * @package    BazeZF_Version
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */
final class BazeZF_Version
{
    /**
     * Zend Framework version identification - see compareVersion()
     */
    const VERSION = '0.7';

    /**
     * Compare the specified Zend Framework version string $version
     * with the current Zend_Version::VERSION of Zend Framework.
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
}
