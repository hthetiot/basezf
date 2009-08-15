<?php
/**
 * Zip class in /BazeZF/Archive
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
    protected static $_mimeType = 'application/zip';

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
        $files = 0;
        $offset = 0;
        $central = '';

        if (!empty ($this->_options['sfx']))
            if ($fp = fopen($this->_options['sfx'], "rb"))
            {
                $temp = fread($fp, filesize($this->_options['sfx']));
                fclose($fp);
                $this->_addArchiveData($temp);
                $offset += strlen($temp);
                unset ($temp);
            } else {
                throw new BaseZF_Archive_Exception(sprintf('Could not open sfx module from %s."', $this->_options['sfx']));
            }

        $pwd = getcwd();

        foreach ($this->_files as $current) {

            if ($current['name'] == $this->_options['path']) {
                continue;
            }

            $timedate = explode(" ", date("Y n j G i s", $current['stat'][9]));
            $timedate = ($timedate[0] - 1980 << 25) | ($timedate[1] << 21) | ($timedate[2] << 16) |
                ($timedate[3] << 11) | ($timedate[4] << 5) | ($timedate[5]);

            $block = pack("VvvvV", 0x04034b50, 0x000A, 0x0000, (isset($current['method']) || $this->_options['method'] == 0) ? 0x0000 : 0x0008, $timedate);

            // directory
            if ($current['type'] == 5) {

                $block .= pack("VVVvv", 0x00000000, 0x00000000, 0x00000000, strlen($current['path']) + 1, 0x0000);
                $block .= $current['path'] . "/";
                $this->_addArchiveData($block);
                $central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $this->_options['method'] == 0 ? 0x0000 : 0x000A, 0x0000,
                    (isset($current['method']) || $this->_options['method'] == 0) ? 0x0000 : 0x0008, $timedate,
                    0x00000000, 0x00000000, 0x00000000, strlen($current['path']) + 1, 0x0000, 0x0000, 0x0000, 0x0000, $current['type'] == 5 ? 0x00000010 : 0x00000000, $offset);
                $central .= $current['path'] . "/";
                $files++;
                $offset += (31 + strlen($current['path']));

            // empty stuff
            } else if ($current['stat'][7] == 0) {

                $block .= pack("VVVvv", 0x00000000, 0x00000000, 0x00000000, strlen($current['path']), 0x0000);
                $block .= $current['path'];
                $this->_addArchiveData($block);
                $central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $this->_options['method'] == 0 ? 0x0000 : 0x000A, 0x0000,
                    (isset($current['method']) || $this->_options['method'] == 0) ? 0x0000 : 0x0008, $timedate,
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
                    throw new BaseZF_Archive_Exception(sprintf('Could not open file %s for reading. It was not added."', $this->_options['path']));
                }

                $crc32 = crc32($temp);
                if (!isset($current['method']) && $this->_options['method'] == 1) {
                    $temp = gzcompress($temp, $this->_options['level']);
                    $size = strlen($temp) - 6;
                    $temp = substr($temp, 2, $size);
                } else {
                    $size = strlen($temp);
                }

                $block .= pack("VVVvv", $crc32, $size, $current['stat'][7], strlen($current['path']), 0x0000);
                $block .= $current['path'];
                $this->_addArchiveData($block);
                $this->_addArchiveData($temp);
                unset ($temp);
                $central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $this->_options['method'] == 0 ? 0x0000 : 0x000A, 0x0000,
                    (isset($current['method']) || $this->_options['method'] == 0) ? 0x0000 : 0x0008, $timedate,
                    $crc32, $size, $current['stat'][7], strlen($current['path']), 0x0000, 0x0000, 0x0000, 0x0000, 0x00000000, $offset);
                $central .= $current['path'];
                $files++;
                $offset += (30 + strlen($current['path']) + $size);
            }
        }

        $this->_addArchiveData($central);
        $this->_addArchiveData(
            pack("VvvvvVVv", 0x06054b50, 0x0000, 0x0000, $files, $files, strlen($central),
            $offset,
            !empty ($this->_options['comment']) ? strlen($this->_options['comment']) : 0x0000)
        );

        if (!empty ($this->_options['comment'])) {
            $this->_addArchiveData($this->_options['comment']);
        }

        chdir($pwd);

        return true;
    }

    /**
     * Extract archive for current format
     */
    public function _extractArchive($outputDir)
    {
        $zip = zip_open($this->_options['path']);

        while ($zipEntry = zip_read($zip)) {

            $completePath = $outputDir . dirname(zip_entry_name($zipEntry));
            $completeName = $outputDir . zip_entry_name($zipEntry);

            // Walk through path to create non existing directories
            // This won't apply to empty directories ! They are created further below

            $fullPath = '';
            foreach(explode(DIRECTORY_SEPARATOR ,$completePath) as $path) {

                $fullPath .= $path . DIRECTORY_SEPARATOR;

                if(!is_dir($fullPath)) {
                    mkdir($fullPath, 0777);
                }
            }

            if (zip_entry_open($zip, $zipEntry, 'r')) {

                if ($this->_options['inmemory'] == 1) {

                    // @todo

                } else if ($fd = fopen($completeName, 'w+')) {
                    fwrite($fd, zip_entry_read($zipEntry, zip_entry_filesize($zipEntry)));
                    fclose($fd);
                }

                zip_entry_close($zipEntry);
            }
        }

        zip_close($zip);

    }
}

