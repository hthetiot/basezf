<?php
/**
 * BaseZF_Archive_Gzip class in /BazeZF/Archive
 *
 * @category  BazeZF
 * @package   BazeZF_Archive
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 *
 * Archive Builder for Gzip Format.
 */

class BaseZF_Archive_Gzip extends BaseZF_Archive_Tar
{
    /**
     * Mime Type.
     *
     * @var string
     */
    protected static $_mimeType = 'application/x-gzip"';

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

        if ($this->_options['inmemory']) {
            $this->_setArchiveData(gzencode($this->_archive, $this->_options['level']));
        } else {
            $this->_setArchiveData(stream_get_contents($this->_archive));
        }
    }

    /**
     * Extract archive for current format
     */
    protected function _extractArchive($outputPath)
    {
        //return @gzopen($this->_options['path'], "rb");
        throw new BaseZF_Archive_Exception(sprintf('%s::%s function is not yet implemented', __CLASS__, __FUNCTION__));
    }
}

