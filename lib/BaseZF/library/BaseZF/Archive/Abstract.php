<?php
/**
 *
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)

/**
 * Zbstract Archive Builder for format.
 */
abstract class BaseZF_Archive_Abstract
{
    protected $options = array(
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

    protected $files = array();
    protected $exclude = array();
    protected $storeonly = array();
    protected $archive;

    public function __construct($name, array $options = array())
    {
        // set name option
        $options['name'] = $name;

        $this->setOptions($options);
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;

        $this->cleanOptions();

        return $this;
    }

    public function setOptions($options)
    {
        // set options
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }

        $this->cleanOptions();

        return $this;
    }

    protected function cleanOptions()
    {
        if (!empty($this->options['name'])) {
            $this->options['name'] = str_replace("\\", "/", $this->options['name']);
            $this->options['name'] = preg_replace("/\/+/", "/", $this->options['name']);
        }

        return $this;
    }

    public function createArchive()
    {
        $this->makeList();

        if (!$this->options['inmemory']) {

            $pwd = getcwd();
            if (!$this->options['overwrite'] && file_exists($this->options['name'])) {
                chdir($pwd);
                throw new Exception(sprintf('File %s already exists.', $this->options['name']));
            } else if ($this->archive = fopen($this->options['name'], "wb+")) {
                chdir($pwd);
            } else {
                chdir($pwd);
                throw new Exception(sprintf('Could not open %s for writing.', $this->options['name']));
            }

        } else {
            $this->archive = null;
        }

        $this->buildArchive();

        if (!$this->options['inmemory']) {
            fclose($this->archive);
        }

        return true;
    }

    public function addArchiveData($data)
    {
        if ($this->options['inmemory']) {
            $this->archive .= $data;
        } else {
            fwrite($this->archive, $data);
        }

        return $this;
    }

    public function makeList()
    {
        if (!empty ($this->exclude))
            foreach ($this->files as $key => $value)
                foreach ($this->exclude as $current)
                    if ($value['name'] == $current['name'])
                        unset ($this->files[$key]);
        if (!empty ($this->storeonly))
            foreach ($this->files as $key => $value)
                foreach ($this->storeonly as $current)
                    if ($value['name'] == $current['name'])
                        $this->files[$key]['method'] = 0;
        unset ($this->exclude, $this->storeonly);

        return $this;
    }

    public function addFiles($list, $root = '')
    {
        $temp = $this->listFiles($list);

        foreach ($temp as $current) {

            // update root
            $current['path'] = $root . $current['path'];

            $this->files[] = $current;
        }

        return $this;
    }

    public function excludeFiles($list)
    {
        $temp = $this->listFiles($list);

        foreach ($temp as $current) {
            $this->exclude[] = $current;
        }

        return $this;
    }

    public function storeFiles($list)
    {
        $temp = $this->listFiles($list);

        foreach ($temp as $current) {
            $this->storeonly[] = $current;
        }

        return $this;
    }

    public function listFiles($list)
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
                $temp = $this->parseDir($dir);

                foreach ($temp as $current2) {
                    if (preg_match("/^{$regex}$/i", $current2['name'])) {
                        $files[] = $current2;
                    }
                }

                unset($regex, $dir, $temp, $current);

            // include a dir
            } else if (is_dir($current)) {

                $temp = $this->parseDir($current);
                foreach ($temp as $file) {
                    $files[] = $file;
                }

                unset ($temp, $file);

            // include simple file
            } else if (file_exists($current)) {

                $files[] = $this->buildFileEntry($current);
            }
        }

        chdir($pwd);

        unset ($current, $pwd);

        usort($files, array ($this, "sortFiles"));

        return $files;
    }

    public function addFileFromString($fileName, $fileContents)
    {
        $this->files[] = $this->buildFileEntry($fileName, $fileName, $fileContents);

        return $this;
    }

    public function addFile($filePath, $archiveFilePath = null)
    {
        if (file_exists($filePath)) {
            $this->files[] = $this->buildFileEntry($filePath, $archiveFilePath);
        } else {
            throw new Exception(sprintf('Could not open file %s for reading."', $filePath));
        }

        return $this;
    }

    protected function buildFileEntry($filePath, $archiveFilePath = null, $fileContents = null)
    {
        $fileEntry = array(
            'name'  => $filePath,
            'path'  => (is_null($archiveFilePath) ? $filePath : $archiveFilePath),
            'type'  => (is_link($filePath) && !$this->options['followlinks'] ? 2 : 0),
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

    public function parseDir($dirname, $archivePathRoot = null)
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

                if (!$this->options['recurse']) {
                    continue;
                }

                $temp = $this->parseDir($fullname, $archivePath);

                foreach ($temp as $file2) {
                    $files[] = $file2;
                }

            } else if (file_exists($fullname)) {
                $files[] = $this->buildFileEntry($fullname, $archivePath . basename($fullname));
            }
        }

        closedir($dir);

        return $files;
    }

    public function sortFiles($a, $b)
    {
        if ($a['type'] != $b['type'])
            if ($a['type'] == 5 || $b['type'] == 2)
                return -1;
            else if ($a['type'] == 2 || $b['type'] == 5)
                return 1;
        else if ($a['type'] == 5)
            return strcmp(strtolower($a['name']), strtolower($b['name']));
        else if ($a['ext'] != $b['ext'])
            return strcmp($a['ext'], $b['ext']);
        else if ($a['stat'][7] != $b['stat'][7])
            return $a['stat'][7] > $b['stat'][7] ? -1 : 1;
        else
            return strcmp(strtolower($a['name']), strtolower($b['name']));
        return 0;
    }

    public function getArchiveContent()
    {
        if (!$this->options['inmemory']) {
            throw new Exception('Can only use getArchiveContent() if archive is in memory. Redirect to file otherwise, it is faster.');
        }

        return $this->archive;
    }

    public function downloadFile()
    {
        if (!$this->options['inmemory']) {
            throw new Exception('Can only use downloadFile() if archive is in memory. Redirect to file otherwise, it is faster.');
        }

        header("Content-Type: " . $this->getFileMimeType());

        // attachment
        $header = "Content-Disposition: attachment; filename=\"";
        $header .= strstr($this->options['name'], "/") ? substr($this->options['name'], strrpos($this->options['name'], "/") + 1) : $this->options['name'];
        $header .= "\"";
        header($header);

        header("Content-Length: " . mb_strlen($this->archive));
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: no-cache, must-revalidate, max-age=60");
        header("Expires: Sat, 01 Jan 2000 12:00:00 GMT");
        print($this->archive);
    }

    /*** ABSTRACT FUNCTIONS ***/

    abstract protected function buildArchive();

    abstract public function extractArchive($outputDir);

    abstract public function getFileMimeType();
}

