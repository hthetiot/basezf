<?php
/**
 * Abstract class in /BazeZF/Archive
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)

/**
 * Abstract Archive Builder for format.
 */
abstract class BaseZF_Archive_Abstract
{
    protected $_options = array(
        'name'          => null,
        'inmemory'      => true,
        'overwrite'     => false,
        'recurse'       => true,
        'followlinks'   => false,
        'level'         => 3,
        'method'        => true,
        'sfx'           => null,
        'comment'       => null
    );

    protected $_files = array();
    protected $_exclude = array();
    protected $_storeonly = array();
    protected $_archive;

    /**
     * Create a new archive instance
     *
     * @param string $name
     * @param array $options
     */
    public function __construct($name, array $options = array())
    {
        // set name option
        $options['name'] = $name;

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
        if (!empty($this->_options['name'])) {
            $this->_options['name'] = str_replace("\\", "/", $this->_options['name']);
            $this->_options['name'] = preg_replace("/\/+/", "/", $this->_options['name']);
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
    abstract public function extractArchive($outputDir);

    /**
     * Get archive file mine type
     *
     * @return string header for file mine type
     */
    abstract public function getFileMimeType();

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
            if (!$this->_options['overwrite'] && file_exists($this->_options['name'])) {
                chdir($pwd);
                throw new BaseZF_Archive_Exception(sprintf('File %s already exists.', $this->_options['name']));
            } else if ($this->_archive = fopen($this->_options['name'], "wb+")) {
                chdir($pwd);
            } else {
                chdir($pwd);
                throw new BaseZF_Archive_Exception(sprintf('Could not open %s for writing.', $this->_options['name']));
            }

        } else {
            $this->_archive = null;
        }

        $this->_buildArchive();

        if (!$this->_options['inmemory']) {
            fclose($this->_archive);
        }

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
            throw new BaseZF_Archive_Exception(sprintf('Could not open file %s for reading."', $filePath));
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
     *
     */
    public function downloadFile()
    {
        if (!$this->_options['inmemory']) {
            throw new BaseZF_Archive_Exception('Can only use downloadFile() if archive is in memory. Redirect to file otherwise, it is faster.');
        }

        header("Content-Type: " . $this->getFileMimeType());

        // attachment
        $header = "Content-Disposition: attachment; filename=\"";
        $header .= strstr($this->_options['name'], "/") ? substr($this->_options['name'], strrpos($this->_options['name'], "/") + 1) : $this->_options['name'];
        $header .= "\"";
        header($header);

        header("Content-Length: " . mb_strlen($this->_archive));
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: no-cache, must-revalidate, max-age=60");
        header("Expires: Sat, 01 Jan 2000 12:00:00 GMT");
        print($this->_archive);
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
    protected function _makeList()
    {
        if (!empty ($this->_exclude))
            foreach ($this->_files as $key => $value)
                foreach ($this->_exclude as $current)
                    if ($value['name'] == $current['name'])
                        unset ($this->_files[$key]);
        if (!empty ($this->_storeonly))
            foreach ($this->_files as $key => $value)
                foreach ($this->_storeonly as $current)
                    if ($value['name'] == $current['name'])
                        $this->_files[$key]['method'] = 0;
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
            'ext'   => substr($filePath, strrpos($filePath, ".")),
        );

        if (!is_null($fileContents)) {

            $tempHandle = fopen('php://temp', 'r+');
            fwrite($tempHandle, $fileContents);

            $fileEntry['stat'] = fstat($tempHandle);
            $fileEntry['content'] = $fileContents;

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

