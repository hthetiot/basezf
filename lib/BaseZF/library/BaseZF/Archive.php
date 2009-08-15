<?php
/**
 * Archive class in /BazeZF/
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

/**
 * Archive Factory.
 */
class BaseZF_Archive
{
    protected static $_availableFormats = array(
        'bzip',
        'zip',
        'tar',
        'gzip',
    );

    /**
     *
     */
    static public function newArchive($format, $filePath, array $options = array())
    {
        $className = self::_getClassNameByFormat($format);

        return new $className($filePath, $options);
    }

    static private function _getClassNameByFormat($format) {

        $className = __CLASS__ . '_' . ucfirst(strtolower($format));

        // prevent stack error
        Zend_Loader::loadClass($className);

        return $className;
    }

    /**
     *
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
     *
     */
    static public function getArchiveFormat($filePath)
    {
        if (!is_readable($filePath)) {
            throw new BaseZF_Archive_Exception(sprintf('Could not open file "%s"', $filePath));
        }

        $mimeType = trim(mime_content_type($filePath));

        // compare mine types
        foreach(self::$_availableFormats as $format) {

            $className = self::_getClassNameByFormat($format);
            $formatMimeType = call_user_func($className .'::getFileMimeType');

            if ($formatMimeType == $mimeType) {
                return $format;
            }
        }

        // no mine types match
        throw new BaseZF_Archive_Exception(sprintf('Could not detect archive format with mine type "%s"', $mimeType));
    }
}
