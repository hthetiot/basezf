<?php
/**
 * Copyright (c) 2009  Radu Gasler <miezuit@gmail.com>
 *
 *  This file is free software: you may copy, redistribute and/or modify it
 *  under the terms of the GNU General Public License as published by the
 *  Free Software Foundation, either version 2 of the License, or (at your
 *  option) any later version.
 *
 *  This file is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 *
 * Parser class for tests folders.
 *
 * $Id: FolderParser.php 2 2009-03-06 11:11:06Z miezuit $
 *
 * $Rev: 2 $
 *
 * $LastChangedBy: miezuit $
 *
 * $LastChangedDate: 2009-03-06 13:11:06 +0200 (V, 06 mar. 2009) $
 *
 * @author Radu Gasler <miezuit@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html     GPL License
 * @version 0.1
 */

class FolderParser
{
    /**
     * folder to parse
     *
     * @var string
     */
    private $_rootFolder = null;

    /**
     * root folder id
     *
     * @var string
     */
    private $_rootFolderId = null;

    /**
     * folder id prefix
     *
     * @var string
     */
     private $_folderIdPrefix = null;

    /**
     * suite id prefix
     *
     * @var string
     */
     private $_suiteIdPrefix = null;

    /**
     * test file suffix
     *
     * @var string
     */
    private $_testSuiteSuffix = null;

    /**
     * excluded folders patterns for parsing
     *
     * @var array
     */
    private $_exclude = array();

    /**
     * Folders tree.
     * Format:
     * array(string folderId => array ('path'       => string FolderPath
     *                                 'subfolders' => array(string folderId)
     *                                 'suites'     => array(string suiteId)
     *                                )
     *      )
     *
     * @var array
     */
    private $_folders = array();

    /**
     * Suites and tests array.
     * Format:
     * array(string suiteId => array('path'  => string filePath,
     *                               'tests' => array(string testName)
     *                              )
     *      )
     *
     * @var array
     */
     private $_suites = array();

    /**
     * Array of links from paths to suites ids.
     * Format:
     * array(string suitePath => string suiteId)
     *
     * @var array
     */
     private $_suitePathToId = array();

    /**
     * Constructor for the FolderParser class.
     *
     * @param string $folder folder to parse for tests
     * @param array  $exclude OPTIONAL excluded folders patterns for parsing
     * @param string $testFileSuffix OPTIONAL test file suffix
     * @param string $folderIdPrefix OPTIONAL folder id prefix
     * @param string $suiteIdPrefix OPTIONAL suite id prefix
     */
    public function __construct($folder, $exclude = array(), $testSuiteSuffix = 'Test.php',
				$folderIdPrefix = 'dir', $suiteIdPrefix = 'suite')
    {
	$this->_rootFolder      = $folder;
	$this->_exclude         = $exclude;
	$this->_testSuiteSuffix = $testSuiteSuffix;
	$this->_folderIdPrefix  = $folderIdPrefix;
	$this->_suiteIdPrefix   = $suiteIdPrefix;

	$this->_rootFolderId = $this->_parseFolder($this->_rootFolder);
    }

    /**
     * Returns root folder id.
     * If there are no tests found it returns 0.
     *
     * @return string root folder id
     */
    public function getRootFolderId()
    {
	return $this->_rootFolderId;
    }

    /**
     * Returns information about a folder id.
     *
     * @param string folder id
     * @return array suite information
     */
    public function getFolder($folderId)
    {
	return $this->_folders[$folderId];
    }

    /**
     * Returns the folder path.
     *
     * @param int folder id
     * @return string folder path
     */
    public function getFolderPath($folderId)
    {
	return $this->_folders[$folderId]['path'];
    }

    /**
     * Returns information about a suite id.
     *
     * @param int suite id
     * @return  array suite information
     */
    public function getSuite($suiteId)
    {
	return $this->_suites[$suiteId];
    }

    /**
     * Returns the suite id for a given suite path.
     *
     * @param string suite path
     * @return string suite id or 0 if there is no suite with that path
     */
    public function getSuiteId($suitePath)
    {
	return $this->_suitePathToId[$suitePath];
    }

    /**
     * Returns the suite tests array.
     *
     * @param int suite id
     * @return  array suite tests
     */
    public function getSuiteTests($suiteId)
    {
	return $this->_suites[$suiteId]['tests'];
    }

    /**
     * Returns the suite path.
     *
     * @param int suite id
     * @return string suite path
     */
    public function getSuitePath($suiteId)
    {
	return $this->_suites[$suiteId]['path'];
    }

    /**
     * Returns next available id for folders or suites.
     *
     * @param boolean $folders folders or suites (0 for suites and 1 for folders)
     * @return string folder or suite id
     */
    private function _getNextId($folders)
    {
	// set array and prefix
	$array  = $folders ? $this->_folders : $this->_suites;
	$prefix = $folders ? $this->_folderIdPrefix : $this->_suiteIdPrefix;
	// get last key
	end($array);
	$id = key($array);

	if(is_null($id)) {
	    $newId = $prefix . '1';
	} else {
	    // extract number component of id
	    $n = substr($id, strlen($prefix));
	    // add folder prefix
	    $newId = $prefix . ++$n;
	}
	return $newId;
    }

    /**
     * Returns next available folder id.
     *
     * @return string folder id
     */
    private function _getNextFolderId()
    {
	return $this->_getNextId(true);
    }

    /**
     * Returns next available suite id.
     *
     * @return string suite id
     */
    private function _getNextSuiteId()
    {
	return $this->_getNextId(false);
    }

    /**
     * Parses recursively a folder to search for tests.
     * On the way it creates $this->_suites and $this->_folders.
     *
     * @param string $dir folder path
     * @return int folder id or 0 if there are no subfolders with suites
     */
    private function _parseFolder($path)
    {
	$items = scandir($path);

	$folderId = $this->_getNextFolderId();
	$folder = array('path' => $path);
	// use reference because we might update suites and subfolders components later on
	$this->_folders[$folderId] = &$folder;
	$suites = array();
	$subfolders = array();

	// parse the folders contentes
	foreach ($items as $item) {
	    $itemPath = $path . '/' . $item;
	    // if is dir and is not hidden nor . or ..
	    if ((is_dir($itemPath)) && (substr($item, 0, 1) !== '.')) {
		// filter the folder through exludes
		$descend = true;
		foreach($this->_exclude as $pattern) {
		    if(preg_match($pattern, $item)) {
			$descend = false;
			break;
		    }
		}
		// descend recursively into folder if not excluded
		if($descend) {
		    $subfolderId = $this->_parseFolder($itemPath, $this->_exclude);
		    if($subfolderId) {
			$subfolders[] = $subfolderId;
		    }
		}
	    }

	    //echo $itemPath;
	    //die();

	    // if is file and ends with test suffix
	    if (is_file($itemPath)) { //&& (substr($itemPath, -(strlen($this->_testSuiteSuffix))) == $this->_testSuiteSuffix)) {
		$tests = getTests($itemPath);
		if ($tests) {
		    $suiteId = $this->_getNextSuiteId();
		    $suite = array('path'  => $itemPath,
				   'tests' => $tests
			     );
		    $this->_suites[$suiteId] = $suite;
		    $this->_suitePathToId[$itemPath] = $suiteId;
		    $suites[] = $suiteId;
		}
	    }
	}

	// update the suites and subfolders components
	if(count($suites)) {
	    $folder['suites'] = $suites;
	}
	if(count($subfolders)) {
	    $folder['subfolders'] = $subfolders;
	}

	if(isset($folder['suites']) || isset($folder['subfolders'])) {
	    return $folderId;
	} else {
	    unset($this->_folders[$folderId]);
	    return 0;
	}
    }
}

/*EOF*/