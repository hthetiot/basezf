<?php
/**
 * BaseZF_Service_GetText_Translator class in /BazeZF/Service/GetText
 *
 * @category  BazeZF
 * @package   BazeZF_Service_GetText
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Service_GetText_Translator
{
    public function factory($inputPoFile, $outputPoFile, $adapter = 'google', $hasFuzzy = true, $translateAll = false, $adapterNamespace = null)
    {
        if ($inputPoFile instanceof Zend_Config) {
            $inputPoFile = $inputPoFile->toArrray();
        }

        if (is_array($inputPoFile)) {
            list($inputPoFile, $outputPoFile, $adapter, $hasFuzzy, $translateAll, $adapterNamespace) = $inputPoFile;
        }

        // Verify that an adapter name has been specified.
        if (!is_string($adapter) || empty($adapter)) {
            throw new BaseZF_Service_GetText_Exception('Adapter name must be specified in a string');
        }

        // Form full adapter class name
        $defaultAdapterNamespace = __CLASS__;
        if (!is_null($adapterNamespace)) {
            $adapterNamespace = $defaultAdapterNamespace;
        }

        // Adapter no longer normalized- see http://framework.zend.com/issues/browse/ZF-5606
        $adapterName = $adapterNamespace . '_';
        $adapterName .= str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapter))));

        // Load the adapter class.  This throws an exception
        // if the specified class cannot be loaded.
        if (!class_exists($adapterName)) {
            Zend_Loader::loadClass($adapterName);
        }

        // not erase please
        if ($inputPoFile == $outputPoFile) {
            throw new BaseZF_Service_GetText_Exception('@todo');
        }

        $translateAdapter = new $adapterName($inputPoFile);

        // Verify that the object created is a descendent of the abstract adapter type.
        if (! $translateAdapter instanceof BaseZF_Service_GetText_Parsor) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Adapter class "%s" does not extend BaseZF_Service_GetText_Parsor', $adapterName));
        }

        return $translateAdapter;
    }
}
