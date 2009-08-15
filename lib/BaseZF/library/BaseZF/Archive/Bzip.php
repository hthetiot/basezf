<?php
/**
 * Bzip class in /BazeZF/Archive
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)

/**
 * Archive Builder for Bzip Format.
 */
class BaseZF_Archive_Bzip extends BaseZF_Archive_Tar
{
    /**
     * Mime Type.
     *
     * @var string
     */
    protected static $_mimeType = 'application/x-bzip2';

    /**
     * Get archive format mime type
     *
     * @return string archive mime type
     */
    public static function getFileMimeType()
    {
        return self::$_mimeType;
    }

    /**
     * Build archive for current format
     */
    protected function _buildArchive()
    {
        // compress as Tar archive
        parent::_buildArchive();

        $this->_archive = bzcompress($this->_archive, $this->_options['level']);
    }

    /**
     * Extract archive for current format
     */
    protected function _extractArchive($outputPath)
    {
        //return @bzopen($this->_options['path'], "rb");
        throw new BaseZF_Archive_Exception(sprintf('%s::%s function is not yet implemented', __CLASS__, __FUNCTION__));
    }
}

