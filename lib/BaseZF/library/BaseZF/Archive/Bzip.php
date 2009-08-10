<?php
/**
 *
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
    protected function buildArchive()
    {
        throw new Exception(sprintf('%s::%s function is not yet implemented', __CLASS__, __FUNCTION__));
    }

    /**
     * Extract archive for current format
     */
    public function extractArchive($outputDir)
    {
        throw new Exception(sprintf('%s::%s function is not yet implemented', __CLASS__, __FUNCTION__));
    }
}

