<?php
/**
 * BaseZF_UnitTest_TmpFile class in /tests/BaseZF/UnitTest
 *
 * @category  BaseZF
 * @package   BaseZF_UnitTest
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

final class BaseZF_UnitTest_TemporaryFile
{
    protected static $_nameSpace = null;

    protected static $_tmpPath = null;

    protected static $_files = array();

    protected static $_directories = array();

    /**
     * Set a tmp path for next file added
     *
     * @return string new $tmpPath
     */
    public static function setTmpPath($tmpPath)
    {
        return self::$_tmpPath = $tmpPath;
    }

    /**
     * Get tmp path for next file added
     *
     * @return string new $tmpPath
     */
    public static function getTmpPath()
    {
        return self::$_tmpPath;
    }

    /**
     * Set a namespace inside tmp path for next file added
     *
     * @return string new $nameSpace
     */
    public static function setNameSpace($nameSpace)
    {
        return self::$_nameSpace = $nameSpace;
    }

    /**
     * Create a Tmp file and add it into file should remove at the end
     *
     * @param string $fileName file name
     * @param string $filePath path of file
     * @param string $fileData content of file
     *
     * @return string new tmp file path
     */
    public static function getFile($fileName, $filePath = null, $fileData = null)
    {
        // sanitize $filePath value to use DIRECTORY_SEPARATOR
        if (!is_null($filePath)) {
            $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);
        } else if (!is_null(self::$_nameSpace))  {
            $filePath = DIRECTORY_SEPARATOR;
        }

        // create paths
        $tmpFilePath = self::$_tmpPath . DIRECTORY_SEPARATOR . self::$_nameSpace . $filePath . DIRECTORY_SEPARATOR . $fileName;
        $tmpFileDir = dirname($tmpFilePath);

        if (!is_dir($tmpFileDir) && $tmpFileDir != self::$_tmpPath) {

            $tmpDirs = explode(DIRECTORY_SEPARATOR, $tmpFileDir);
            $nbTmpDirs = count($tmpDirs);
            for ($i = 2; $i <= $nbTmpDirs; $i++) {

                $tmpDir = implode(DIRECTORY_SEPARATOR, array_slice($tmpDirs, 0, $i));

                if (!is_dir($tmpDir)) {
                    mkdir($tmpDir);
                    self::$_directories[] = $tmpDir;
                }
            }
        }

        self::$_files[$tmpFilePath] = $tmpFilePath;

        // add contennt on tmp file
        if (!is_null($fileData)) {
            file_put_contents($tmpFilePath, $fileData);
        }

        return $tmpFilePath;
    }

    public static function clearFiles()
    {
        // clean database or test generated data for example
        foreach (self::$_files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        // remove dir
        $directories = array_reverse(self::$_directories);
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }

        // reset
        self::$_files = array();
        self::$_directories = array();
    }
}

