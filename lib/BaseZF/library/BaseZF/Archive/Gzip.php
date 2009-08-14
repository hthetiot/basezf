<?php
/**
 * Gzip class in /BazeZF/Archive
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)

/**
 * Archive Builder for Gzip Format.
 */
class BaseZF_Archive_Gzip extends BaseZF_Archive_Tar
{
    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/x-gzip"';

    /**
     * Build archive for current format
     */
    protected function _buildArchive()
    {
        // compress as Tar archive
        parent::_buildArchive();

        $this->_archive = gzencode($this->_archive, $this->_options['level']);
    }

    /**
     * Extract archive for current format
     */
    public function extractArchive($outputDir)
    {
        return @gzopen($this->_options['name'], "rb");
    }
}

