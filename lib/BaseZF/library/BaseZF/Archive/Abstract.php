<?php
/**
 * Abstract class in /BazeZF/Archive
 *
 * @category   BazeZF
 * @package    BazeZF_Archive
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *
 * Abstract Archive Builder for format.
 */

abstract class BaseZF_Archive_Abstract
{
    /**
     * Archive options
     *
     * @var array
     */
    protected $_options = array(
        'path'          => null,
        'inmemory'      => false,
        'overwrite'     => true,
        'recurse'       => true,
        'followlinks'   => false,
        'level'         => 3,
        'method'        => true,
        'sfx'           => null,
        'comment'       => null
    );

    /**
     * Archive files infos
     *
     * @var array
     */
    protected $_files       = array();

    /**
     * Archive excluded files paths
     *
     * @var array
     */
    protected $_exclude     = array();

    /**
     * Archive files stored
     *
     * @var array
     */
    protected $_storeonly   = array();

    /**
     * Archive content or archive file ressource
     *
     * @var void
     */
    protected $_archive     = null;

    /**
     * Create a new archive instance
     *
     * @param string $filePath archive file path
     * @param array $options archive option
     */
    public function __construct($filePath = null, array $options = array())
    {
        // set path option
        if (!is_null($filePath)) {
            $options['path'] = $filePath;
        } else {
            $options['inmemory'] = true;
        }

        $this->setOptions($options);
    }

    /**
     * Set option value
     *
     * @param string $option
     * @param string $value
     *
     * @return return $this for more fluent interface
     */
    public function setOption($option, $value)
    {
        $this->_options[$option] = $value;

        $this->cleanOptions();

        return $this;
    }

    /**
     * Set options values
     *
     * @param array $options
     *
     * @return return $this for more fluent interface
     */
    public function setOptions($options)
    {
        // set options
        foreach ($options as $key => $value) {
            $this->_options[$key] = $value;
        }

        $this->cleanOptions();

        return $this;
    }

    /**
     * Sanitize options values
     *
     * @return return $this for more fluent interface
     */
    protected function cleanOptions()
    {
        if (!empty($this->_options['path'])) {
            $this->_options['path'] = str_replace("\\", "/", $this->_options['path']);
            $this->_options['path'] = preg_replace("/\/+/", "/", $this->_options['path']);
        }

        return $this;
    }

    //
    // Abstract functions
    //

    /**
     *
     */
    abstract protected function _buildArchive();

    /**
     *
     */
    abstract protected function _extractArchive($outputDir);

    /**
     * Get archive file mine type abstract (static could not be static)
     *
     * @return string header for file mine type
     */
    public static function getFileMimeType()
    {
         throw new BaseZF_Archive_Exception(sprintf('Function "%s" should overwrited by class %s', __FUNC__, __CLASS__));
    }

    //
    // Public API functions
    //

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    public function createArchive()
    {
        $this->_makeList();

        if (!$this->_options['inmemory']) {

            $pwd = getcwd();
            if (!$this->_options['overwrite'] && file_exists($this->_options['path'])) {
                chdir($pwd);
                throw new BaseZF_Archive_Exception(sprintf('File %s already exists.', $this->_options['path']));
            } else if ($this->_archive = fopen($this->_options['path'], 'wb+')) {
                chdir($pwd);
            } else {
                chdir($pwd);
                throw new BaseZF_Archive_Exception(sprintf('Could not open %s for writing.', $this->_options['path']));
            }

        } else {
            $this->_archive = null;
        }

        $this->_buildArchive();

        if (!$this->_options['inmemory'] && is_resource($this->_archive)) {
            fclose($this->_archive);
        }

        return $this;
    }

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    public function extractArchive($outputDir = null)
    {
        if (is_null($outputDir)) {
            $this->_options['inmemory'] = true;
            $outputDir = DIRECTORY_SEPARATOR;

        // add possible missing DIRECTORY_SEPARATOR at the end of string
        } else {
            $outputDirLength = mb_strlen($outputDir) - 1;
            $outputDir = strpos($outputDir, DIRECTORY_SEPARATOR, $outputDirLength) == $outputDirLength ? $outputDir : $outputDir . DIRECTORY_SEPARATOR;
        }

        // open file for reading
        if (!is_readable($this->_options['path'])) {
            throw new BaseZF_Archive_Exception(sprintf('Could not open file "%s"', $this->_options['path']));
        }

        //
        if (!$this->_options['inmemory']) {
            $this->_extractArchiveToPath($outputDir);
        } else {
            $this->_extractArchive($outputDir);
        }

        return $this;
    }

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    protected function _extractArchiveToPath($outputDir)
    {
        if (!is_dir($outputDir)) {
            mkdir($outputDir);
        }

        if (!is_writable($outputDir)) {
            throw new BaseZF_Archive_Exception(sprintf('Could not write on path "%s"', $outputDir));
        }

        // set current path to output dir
        $cwd = getcwd();
        chdir($outputDir);

        $this->_extractArchive($outputDir);

        // set previous path to current
        chdir($cwd);

        return $this;
    }

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    public function addFiles($list, $root = '')
    {
        $temp = $this->_listFiles($list);

        foreach ($temp as $current) {

            // update root
            $current['path'] = $root . $current['path'];

            $this->_files[] = $current;
        }

        return $this;
    }

    /**
     * Get archive files description
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    public function excludeFiles($list)
    {
        $temp = $this->_listFiles($list);

        foreach ($temp as $current) {
            $this->_exclude[] = $current;
        }

        return $this;
    }

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    public function addFileFromString($fileName, $fileContents)
    {
        $this->_files[] = $this->_buildFileEntry($fileName, $fileName, $fileContents);

        return $this;
    }

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    public function addFile($filePath, $archiveFilePath = null)
    {
        if (file_exists($filePath)) {
            $this->_files[] = $this->_buildFileEntry($filePath, $archiveFilePath);
        } else {
            throw new BaseZF_Archive_Exception(sprintf('Could not open file %s for reading.', $filePath));
        }

        return $this;
    }

    /**
     *
     */
    public function getArchiveContent()
    {
        if (!$this->_options['inmemory']) {
            throw new BaseZF_Archive_Exception('Can only use getArchiveContent() if archive is in memory. Redirect to file otherwise, it is faster.');
        }

        return $this->_archive;
    }

    /**
     * Download archive
     */
    public function downloadFile()
    {
        // attachment
        header('Content-Disposition: attachment; filename="' . basename($this->_options['path']) . '"');

        // archive format
        header('Content-Type: ' . $this->getFileMimeType());

        // standart header
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        // archive length
        if ($this->_options['inmemory']) {
            header('Content-Length: ' . mb_strlen($this->_archive));
        } else {
            header('Content-Length: ' . filesize($this->_options['path']));
        }

        ob_clean();
        flush();

        // display archive content on standart output
        if ($this->_options['inmemory']) {
            print($this->_archive);
        } else {
            readfile($this->_options['path']);
        }
    }

    //
    // Core Private Functions
    //

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    protected function _addArchiveData($data)
    {
        if ($this->_options['inmemory']) {
            $this->_archive .= $data;
        } else {
            fwrite($this->_archive, $data);
        }

        return $this;
    }

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    protected function _setArchiveData($data)
    {
        if ($this->_options['inmemory']) {
            $this->_archive .= $data;
        } else {

            $this->_archive = fopen($this->_options['path'], 'wb+');
            fwrite($this->_archive, $data);
            fclose($this->_archive);
        }

        return $this;
    }

    /**
     *
     *
     */
    protected function _getArchiveData($display = false)
    {
        if ($this->_options['inmemory']) {

            return $this->_archive;

        } else if ($display) {

            ob_clean();
            flush();

            readfile($this->_options['path']);

        } else {

            $this->_archive = fopen($this->_options['path'], 'wb+');
            $data = stream_get_contents($this->_archive, $data);
            fclose($this->_archive);

            return $data;
        }
    }

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    protected function _makeList()
    {
        if (!empty ($this->_exclude)) {
            foreach ($this->_files as $key => $value) {
                foreach ($this->_exclude as $current) {
                    if ($value['name'] == $current['name']) {
                        unset ($this->_files[$key]);
                    }
                }
            }
        }

        if (!empty ($this->_storeonly)) {
            foreach ($this->_files as $key => $value) {
                foreach ($this->_storeonly as $current) {
                    if ($value['name'] == $current['name']) {
                        $this->_files[$key]['method'] = 0;
                    }
                }
            }
        }

        unset ($this->_exclude, $this->_storeonly);

        return $this;
    }

    /**
     *
     *
     * @return return $this for more fluent interface
     */
    protected function _storeFiles($list)
    {
        $temp = $this->_listFiles($list);

        foreach ($temp as $current) {
            $this->_storeonly[] = $current;
        }

        return $this;
    }

    /**
     *
     */
    protected function _listFiles($list)
    {
        // require array
        if (!is_array($list)) {
            $list = array($list);
        }

        $files = array();
        $pwd = getcwd();

        foreach ($list as $current) {

            $current = str_replace("\\", "/", $current);
            $current = preg_replace("/\/+/", "/", $current);
            $current = preg_replace("/\/$/", "", $current);

            // include dir content
            if (strstr($current, "*")) {

                $regex = preg_replace("/([\\\^\$\.\[\]\|\(\)\?\+\{\}\/])/", "\\\\\\1", $current);
                $regex = str_replace("*", ".*", $regex);
                $dir = strstr($current, "/") ? substr($current, 0, strrpos($current, "/")) : ".";
                $temp = $this->_parseDir($dir);

                foreach ($temp as $current2) {
                    if (preg_match("/^{$regex}$/i", $current2['name'])) {
                        $files[] = $current2;
                    }
                }

                unset($regex, $dir, $temp, $current);

            // include a dir
            } else if (is_dir($current)) {

                $temp = $this->_parseDir($current);
                foreach ($temp as $file) {
                    $files[] = $file;
                }

                unset ($temp, $file);

            // include simple file
            } else if (file_exists($current)) {

                $files[] = $this->_buildFileEntry($current);
            }
        }

        chdir($pwd);

        unset ($current, $pwd);

        usort($files, array ($this, "sortFiles"));

        return $files;
    }

    /**
     *
     */
    protected function _buildFileEntry($filePath, $archiveFilePath = null, $fileContents = null)
    {
        $fileEntry = array(
            'name'  => $filePath,
            'path'  => (is_null($archiveFilePath) ? $filePath : $archiveFilePath),
            'type'  => (is_link($filePath) && !$this->_options['followlinks'] ? 2 : 0),
            'ext'   => pathinfo($filePath, PATHINFO_EXTENSION),
        );

        // clean fileEntry path to remove first char if is a DIRECTORY_SEPARATOR
        if (substr($fileEntry['path'], 0, 1) == DIRECTORY_SEPARATOR) {
            $fileEntry['path'] = substr($fileEntry['path'], 1, mb_strlen($fileEntry['path']));
        }

        if (!is_null($fileContents)) {

            $tempHandle = fopen('php://temp', 'r+');
            fwrite($tempHandle, $fileContents);

            $fileEntry['stat'] = fstat($tempHandle);
            $fileEntry['stat'][9] = time(); // overwrite file creation to now
            $fileEntry['data'] = $fileContents;

            fclose($tempHandle);

        } else {
            $fileEntry['stat'] = stat($filePath);
        }

        return $fileEntry;
    }

    /**
     *
     */
    protected function _parseDir($dirname, $archivePathRoot = null)
    {
        if (!preg_match("/^(\.+\/*)+$/", $dirname) && !is_null($archivePathRoot)) {

            $archivePath = $archivePathRoot . str_replace(dirname($dirname) . '/', '', $dirname) . '/';
            $files = array(
                array(
                    'name' => $dirname,
                    'path' => $archivePath,
                    'type' => 5,
                    'stat' => stat($dirname),
                )
            );

        } else {
            $archivePath = '';
            $files = array();
        }

        $dir = opendir($dirname);

        while ($file = readdir($dir)) {

            $fullname = $dirname . '/' . $file;
            if ($file == '.' || $file == '..') {
                continue;

            } else if (is_dir($fullname)) {

                if (!$this->_options['recurse']) {
                    continue;
                }

                $temp = $this->_parseDir($fullname, $archivePath);

                foreach ($temp as $file2) {
                    $files[] = $file2;
                }

            } else if (file_exists($fullname)) {
                $files[] = $this->_buildFileEntry($fullname, $archivePath . basename($fullname));
            }
        }

        closedir($dir);

        return $files;
    }

    /**
     *
     */
    protected function _sortFiles($a, $b)
    {
        if ($a['type'] != $b['type']) {

            if ($a['type'] == 5 || $b['type'] == 2) {
                return -1;
            } else if ($a['type'] == 2 || $b['type'] == 5) {
                return 1;
            }

        } else if ($a['type'] == 5) {
            return strcmp(strtolower($a['name']), strtolower($b['name']));
        } else if ($a['ext'] != $b['ext']) {
            return strcmp($a['ext'], $b['ext']);
        } else if ($a['stat'][7] != $b['stat'][7]) {
            return $a['stat'][7] > $b['stat'][7] ? -1 : 1;
        } else {
            return strcmp(strtolower($a['name']), strtolower($b['name']));
        }

        return 0;
    }
}

