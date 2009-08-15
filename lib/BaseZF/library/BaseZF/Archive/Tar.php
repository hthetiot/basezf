<?php
/**
 * Tar class in /BazeZF/Archive
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)

/**
 * Archive Builder for Tar Format.
 */
class BaseZF_Archive_Tar extends BaseZF_Archive_Abstract
{
    /**
     * Mime Type.
     *
     * @var string
     */
    protected static $_mimeType = 'application/x-tar';

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
        foreach ($this->_files as $current)
        {
            // useless ?
            if ($current['name'] == $this->_options['path']) {
                continue;
            }

            // check file name
            if (strlen($current['path']) > 99) {
                $path = substr($current['path'], 0, strpos($current['path'], "/", strlen($current['path']) - 100) + 1);
                $current['path'] = substr($current['path'], strlen($path));

                if (strlen($path) > 154 || strlen($current['path']) > 99) {
                    throw new BaseZF_Archive_Exception(sprintf('Could not add %s%s to archive because the filename is too long.', $path, $current['path']));
                }
            }

            // create file index
            $block = pack("a100a8a8a8a12a12a8a1a100a6a2a32a32a8a8a155a12", $current['path'], sprintf("%07o",
                $current['stat'][2]), sprintf("%07o", $current['stat'][4]), sprintf("%07o", $current['stat'][5]),
                sprintf("%011o", $current['type'] == 2 ? 0 : $current['stat'][7]), sprintf("%011o", $current['stat'][9]),
                "        ", $current['type'], $current['type'] == 2 ? @readlink($current['name']) : "", "ustar ", " ",
                "Unknown", "Unknown", "", "", !empty ($path) ? $path : "", "");

            // build checksum
            $checksum = 0;
            for ($i = 0; $i < 512; $i++) {
                $checksum += ord(substr($block, $i, 1));
            }

            $checksum = pack("a8", sprintf("%07o", $checksum));

            // add checksum to index
            $block = substr_replace($block, $checksum, 148, 8);

            // add dir or empty file
            if ($current['type'] == 2 || $current['stat'][7] == 0) {
                $this->_addArchiveData($block);

            // add file with content from file or string
            } else {

                // add content from string
                if (isset($current['content'])) {
                    $temp = $current['content'];

                // add content from file
                } else if ($fp = fopen($current['name'], 'rb')) {
                    $temp = fread($fp, $current['stat'][7]);
                    fclose($fp);
                } else {
                    throw new BaseZF_Archive_Exception(sprintf('Could not open file "%s" for reading."', $this->_options['path']));
                }

                // add index
                $this->_addArchiveData($block);

                // add file content
                $this->_addArchiveData($temp);

                // add file end token
                if ($current['stat'][7] % 512 > 0) {
                    $temp = "";
                    for ($i = 0; $i < 512 - $current['stat'][7] % 512; $i++) {
                        $temp .= "\0";
                    }

                    $this->_addArchiveData($temp);
                }
            }
        }

        // add archive end token
        $this->_addArchiveData(pack("a1024", ""));

    }

    /**
     * Extract archive for current format
     */
    protected function _extractArchive($outputPath)
    {
        $fp = fopen($this->_options['path'], 'rb');
        while ($block = fread($fp, 512)) {

            $temp = unpack('a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100symlink/a6magic/a2temp/a32temp/a32temp/a8temp/a8temp/a155prefix/a12temp', $block);
            $file = array (
                'checksum'  => octdec($temp['checksum']),
                'type'      => $temp['type'],
                'magic'     => $temp['magic'],
                'name'      => $outputPath . $temp['prefix'] . $temp['name'],
                'stat'      => array(
                    2   => $temp['mode'],
                    4   => octdec($temp['uid']),
                    5   => octdec($temp['gid']),
                    7   => octdec($temp['size']),
                    9   => octdec($temp['mtime']),
                ),
            );

            if ($file['checksum'] == 0x00000000) {
                break;
            } else if (substr($file['magic'], 0, 5) != 'ustar') {
                throw new BaseZF_Archive_Exception(sprintf('This script does not support extracting this type of tar file.'));
            }

            $block = substr_replace($block, '        ', 148, 8);
            $checksum = 0;
            for ($i = 0; $i < 512; $i++) {
                $checksum += ord(substr($block, $i, 1));
            }

            if ($file['checksum'] != $checksum) {
                throw new BaseZF_Archive_Exception(sprintf('Could not extract from "%s", this file is corrupted.', $this->_options['path']));
            }

            if ($this->_options['inmemory'] == 1) {

                $file['data'] = fread($fp, $file['stat'][7]);
                fread($fp, (512 - $file['stat'][7] % 512) == 512 ? 0 : (512 - $file['stat'][7] % 512));
                unset ($file['checksum'], $file['magic']);
                $this->files[] = $file;

            } else if ($file['type'] == 5) {

                if (!is_dir($file['name'])) {
                    mkdir($file['name'], $file['stat'][2]);
                }

            } else if ($this->_options['overwrite'] == 0 && file_exists($file['name'])) {

                throw new BaseZF_Archive_Exception(sprintf('%s already exists', $file['name']));

            } else if ($file['type'] == 2) {

                symlink($temp['symlink'], $file['name']);
                //chmod($file['name'], $file['stat'][2]);

            } else {

                if (!is_dir(dirname($file['name']))) {
                    mkdir(dirname($file['name']), 0777);
                }

                if ($new = @fopen($file['name'], 'wb')) {

                    fwrite($new, fread($fp, $file['stat'][7]));
                    fread($fp, (512 - $file['stat'][7] % 512) == 512 ? 0 : (512 - $file['stat'][7] % 512));
                    fclose($new);
                    //chmod($file['name'], $file['stat'][2]);

                } else {
                    throw new BaseZF_Archive_Exception(sprintf('Could not open "%s" for writing.', $file['name']));
                }
            }

            //chown($file['name'], $file['stat'][4]);
            //chgrp($file['name'], $file['stat'][5]);
            //touch($file['name'], $file['stat'][9]);
            unset($file);
        }
    }
}
