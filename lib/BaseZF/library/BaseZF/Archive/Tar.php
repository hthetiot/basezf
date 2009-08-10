<?php
/**
 *
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
/**
 * Desktop Compiler.
 */
class BaseZF_Archive_Tar extends BaseZF_Archive_Abstract
{
    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/x-tar';

    /**
     * Build archive for current format
     */
    protected function buildArchive()
    {
        foreach ($this->files as $current)
        {
            if ($current['name'] == $this->options['name']) {
                continue;
            }

            if (strlen($current['path']) > 99) {
                $path = substr($current['path'], 0, strpos($current['path'], "/", strlen($current['path']) - 100) + 1);
                $current['path'] = substr($current['path'], strlen($path));

                if (strlen($path) > 154 || strlen($current['path']) > 99) {
                    throw new Exception(sprintf('Could not add %s%s to archive because the filename is too long.', $path, $current['path']));
                }
            }

            $block = pack("a100a8a8a8a12a12a8a1a100a6a2a32a32a8a8a155a12", $current['path'], sprintf("%07o",
                $current['stat'][2]), sprintf("%07o", $current['stat'][4]), sprintf("%07o", $current['stat'][5]),
                sprintf("%011o", $current['type'] == 2 ? 0 : $current['stat'][7]), sprintf("%011o", $current['stat'][9]),
                "        ", $current['type'], $current['type'] == 2 ? @readlink($current['name']) : "", "ustar ", " ",
                "Unknown", "Unknown", "", "", !empty ($path) ? $path : "", "");

            $checksum = 0;
            for ($i = 0; $i < 512; $i++) {
                $checksum += ord(substr($block, $i, 1));
            }

            $checksum = pack("a8", sprintf("%07o", $checksum));
            $block = substr_replace($block, $checksum, 148, 8);

            if ($current['type'] == 2 || $current['stat'][7] == 0) {
                $this->addArchiveData($block);
            } else {

                if (isset($current['content'])) {
                    $temp = $current['content'];
                } else if ($fp = fopen($current['name'], 'rb')) {
                    $temp = fread($fp, $current['stat'][7]);
                    fclose($fp);
                } else {
                    throw new Exception(sprintf('Could not open file %s for reading. It was not added."', $this->options['name']));
                }

                $this->addArchiveData($block);
                $this->addArchiveData($temp);

                if ($current['stat'][7] % 512 > 0) {
                    $temp = "";
                    for ($i = 0; $i < 512 - $current['stat'][7] % 512; $i++) {
                        $temp .= "\0";
                    }

                    $this->addArchiveData($temp);
                }
            }
        }

        $this->addArchiveData(pack("a1024", ""));
    }

    public function getFileMimeType()
    {
        return $this->_mimeType;
    }

    /**
     * Extract archive for current format
     */
    public function extractArchive($outputDir)
    {
        throw new Exception(sprintf('%s::%s function is not yet implemented', __CLASS__, __FUNCTION__));
    }
}

