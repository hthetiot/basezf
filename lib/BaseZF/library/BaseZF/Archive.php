<?php
/**
 * Archive class in /BazeZF/
 *
 * @category   BazeZF
 * @package    BazeZF_Archive
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *
 * Archive Factory.
 */

class BaseZF_Archive
{
    /**
     * Available archive formats indexed by default extentions
     *
     * @var array
     */
    protected static $_availableFormats = array(
        'bz'    => 'bzip',
        'zip'   => 'zip',
        'tar'   => 'tar',
        'gz'    => 'gzip',
    );

    /**
     * Create a new archive
     *
     * @param string $format archive format see $_availableFormats property values
     * @param string $filePath archive file path, if null it create archive in memory
     * @param array $options archive builder options
     *
     * @return object instance of BaseZF_Archive_Abstract extended class
     */
    static public function newArchive($format, $filePath = null, array $options = array())
    {
        $className = self::_getClassNameByFormat($format);

        return new $className($filePath, $options);
    }

    /**
     * Extract an archive
     *
     * @param string $filePath archive file path
     * @param string $outputPath output archive path, if null it extract archive in memory
     * @param string $format archive format see $_availableFormats property values
     * @param array $options archive builder options
     *
     * @return object instance of BaseZF_Archive_Abstract extended class
     */
    static public function extractArchive($filePath, $outputPath = null, $format = null, array $options = array())
    {
        // detect format
        if (is_null($format)) {
            $format = self::getArchiveFormat($filePath);
        }

        $archive = self::newArchive($format, $filePath, $options);
        $archive->extractArchive($outputPath);

        return $archive;
    }

    /**
     * Detect archive format
     *
     * @param string $filePath archive file path
     *
     * @return string archive format see $_availableFormats property values
     */
    static public function getArchiveFormat($filePath)
    {
        if (!is_readable($filePath)) {
            throw new BaseZF_Archive_Exception(sprintf('Could not open file "%s"', $filePath));
        }

        $extention = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = null;

        // use $minType only if mime_content_type is available
        if (function_exists('mime_content_type')) {
            $mimeType = trim(mime_content_type($filePath));
        }

        // compare mine types
        foreach(self::$_availableFormats as $formatExtention => $format) {

            $className = self::_getClassNameByFormat($format);
            $formatMimeType = call_user_func($className .'::getFileMimeType');

            if (!is_null($mimeType) && $formatMimeType == $mimeType) {
                return $format;
            } else if ($extention == $formatExtention) {
                return $format;
            }
        }

        // no mine types match
        if (!is_null($mimeType)) {
            throw new BaseZF_Archive_Exception(sprintf('Could not detect archive format for file "%s", with mine type "%s"', $filePath, $mimeType));
        } else {
            throw new BaseZF_Archive_Exception(sprintf('Could not detect archive format for file "%s"', $filePath));
        }
    }

    /**
     *
     */
     static public function getAvailableFormats()
     {
         return self::$_availableFormats;
     }

    /**
     * Get class name and load class from archive format name
     *
     * @param string $format archive format see $_availableFormats property values
     *
     * @return string class name of BaseZF_Archive_Abstract extended class
     */
    static private function _getClassNameByFormat($format) {

        if (in_array($format, self::$_availableFormats) === false) {
            throw new BaseZF_Archive_Exception(sprintf('Invalid archive format "%s", see %s::$_availableFormats value for available form', $format, __CLASS__));
        }

        $className = __CLASS__ . '_' . ucfirst(strtolower($format));

        // prevent stack error
        Zend_Loader::loadClass($className);

        return $className;
    }
}

