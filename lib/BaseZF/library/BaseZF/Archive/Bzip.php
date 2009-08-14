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
    protected $_mimeType = 'application/x-bzip2';

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
    public function extractArchive($outputDir)
    {
        return @bzopen($this->_options['name'], "rb");
    }
}

