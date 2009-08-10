<?php
/**
 *
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)

/**
 * Archive Builder for Zip Format.
 */
class BaseZF_Archive_Zip extends BaseZF_Archive_Abstract
{
    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/zip';

    /**
     * Build archive for current format
     */
    protected function buildArchive()
    {
        $files = 0;
        $offset = 0;
        $central = '';

        if (!empty ($this->options['sfx']))
            if ($fp = fopen($this->options['sfx'], "rb"))
            {
                $temp = fread($fp, filesize($this->options['sfx']));
                fclose($fp);
                $this->addArchiveData($temp);
                $offset += strlen($temp);
                unset ($temp);
            } else {
                throw new Exception(sprintf('Could not open sfx module from %s."', $this->options['sfx']));
            }

        $pwd = getcwd();

        foreach ($this->files as $current) {

            if ($current['name'] == $this->options['name']) {
                continue;
            }

            $timedate = explode(" ", date("Y n j G i s", $current['stat'][9]));
            $timedate = ($timedate[0] - 1980 << 25) | ($timedate[1] << 21) | ($timedate[2] << 16) |
                ($timedate[3] << 11) | ($timedate[4] << 5) | ($timedate[5]);

            $block = pack("VvvvV", 0x04034b50, 0x000A, 0x0000, (isset($current['method']) || $this->options['method'] == 0) ? 0x0000 : 0x0008, $timedate);

            // directory
            if ($current['type'] == 5) {

                $block .= pack("VVVvv", 0x00000000, 0x00000000, 0x00000000, strlen($current['path']) + 1, 0x0000);
                $block .= $current['path'] . "/";
                $this->addArchiveData($block);
                $central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $this->options['method'] == 0 ? 0x0000 : 0x000A, 0x0000,
                    (isset($current['method']) || $this->options['method'] == 0) ? 0x0000 : 0x0008, $timedate,
                    0x00000000, 0x00000000, 0x00000000, strlen($current['path']) + 1, 0x0000, 0x0000, 0x0000, 0x0000, $current['type'] == 5 ? 0x00000010 : 0x00000000, $offset);
                $central .= $current['path'] . "/";
                $files++;
                $offset += (31 + strlen($current['path']));

            // empty stuff
            } else if ($current['stat'][7] == 0) {

                $block .= pack("VVVvv", 0x00000000, 0x00000000, 0x00000000, strlen($current['path']), 0x0000);
                $block .= $current['path'];
                $this->addArchiveData($block);
                $central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $this->options['method'] == 0 ? 0x0000 : 0x000A, 0x0000,
                    (isset($current['method']) || $this->options['method'] == 0) ? 0x0000 : 0x0008, $timedate,
                    0x00000000, 0x00000000, 0x00000000, strlen($current['path']), 0x0000, 0x0000, 0x0000, 0x0000, $current['type'] == 5 ? 0x00000010 : 0x00000000, $offset);
                $central .= $current['path'];
                $files++;
                $offset += (30 + strlen($current['path']));

            // files
            } else {

                if (isset($current['content'])) {
                    $temp = $current['content'];
                } else if ($fp = fopen($current['name'], 'rb')) {
                    $temp = fread($fp, $current['stat'][7]);
                    fclose($fp);
                } else {
                    throw new Exception(sprintf('Could not open file %s for reading. It was not added."', $this->options['name']));
                }

                $crc32 = crc32($temp);
                if (!isset($current['method']) && $this->options['method'] == 1) {
                    $temp = gzcompress($temp, $this->options['level']);
                    $size = strlen($temp) - 6;
                    $temp = substr($temp, 2, $size);
                } else {
                    $size = strlen($temp);
                }

                $block .= pack("VVVvv", $crc32, $size, $current['stat'][7], strlen($current['path']), 0x0000);
                $block .= $current['path'];
                $this->addArchiveData($block);
                $this->addArchiveData($temp);
                unset ($temp);
                $central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $this->options['method'] == 0 ? 0x0000 : 0x000A, 0x0000,
                    (isset($current['method']) || $this->options['method'] == 0) ? 0x0000 : 0x0008, $timedate,
                    $crc32, $size, $current['stat'][7], strlen($current['path']), 0x0000, 0x0000, 0x0000, 0x0000, 0x00000000, $offset);
                $central .= $current['path'];
                $files++;
                $offset += (30 + strlen($current['path']) + $size);
            }
        }

        $this->addArchiveData($central);
        $this->addArchiveData(
            pack("VvvvvVVv", 0x06054b50, 0x0000, 0x0000, $files, $files, strlen($central),
            $offset,
            !empty ($this->options['comment']) ? strlen($this->options['comment']) : 0x0000)
        );

        if (!empty ($this->options['comment'])) {
            $this->addArchiveData($this->options['comment']);
        }

        chdir($pwd);

        return true;
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

