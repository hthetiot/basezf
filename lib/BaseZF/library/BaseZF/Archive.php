<?php
/**
 * DbSearch class in /BazeZF/
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

/**
 * Archive Factory.
 */
class BaseZF_Archive
{
    static public function newArchive($format, $fileName)
    {
        $className = __CLASS__ . '_' . ucfirst(strtolower($format));

        // prevent stack error
        Zend_Loader::loadClass($className);

        return new $className($fileName);
    }
}
