<?php
/**
 * BaseZF_Service_GetText class in /BazeZF/Service
 *
 * @category  BazeZF
 * @package   BazeZF_Service_GetText
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Service_GetText
{
    /**
     * Default config values
     */
    protected static $_defaultConfig = array(

        // @todo clean/optimize key names

        // domain src paths
        'domainsPaths' => array(
            'message'   => array(),
        ),

        // locales gettext env
        'localeDirPath' => '/home/hthetiot/projects/basezf/locale',
        'potDirPath'    => '/home/hthetiot/projects/basezf/locale/dist',
        'poDir'         => 'LC_MESSAGES',

        // bin path
        'xgettextPath'  => 'xgettext',
        'msguniqPath'   => 'msguniq',
        'msgmergePath'  => 'msgmerge',
        'msginitPath'   => 'msginit',
        'msguniqPath'   => 'msguniq',
        'msgfmtPath'    => 'msgfmt',

        // other options
        'bugsAddress'   => 'nobody@example.com',
        'srcKeyword'    => '__', //@todo array('__', '_'),
        'srcEncoding'   => 'utf-8',
    );


    /**
     * Instance config value
     */
    protected $_config = array();

    /**
     * Constructor
     *
     * @param array $config the config of gettext service
     */
    public function __construct($config = array())
    {
        $this->setConfig($config);
    }

    //
    // Public Config API
    //

    /**
     * Init Translation system using gettext
     *
     * @param object $locale instance of Zend_Locale
     *
     * @return void
     */
    public function iniTranslation($locale, array $availableDomains = array())
    {
        if (!$locale instanceOf Zend_Locale) {
            $locale = new Zend_Locale($locale);
        }

        $localeDirPath = $this->getConfig('localeDirPath');

        // init available gettext domains
        foreach ($availableDomains as $domain) {
            bindtextdomain($domain, $localeDirPath);
            bind_textdomain_codeset($domain, 'UTF-8');
        }

        // set first domain has default domain
        $defaultDomain = array_shift($availableDomains);
        textdomain($defaultDomain);

        $localeWithEncoding = $locale . '.utf8';

        // mandatory for gettext
        if (putenv('LANGUAGE') != $locale->getLanguage()) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Could not set the ENV variable LANGUAGE = %s', $locale));
        }

        if (setlocale(LC_MESSAGES, $localeWithEncoding) !== $localeWithEncoding) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to set locale "%s" to value "%s", please check installed locales on system', 'LC_MESSAGES', $localeWithEncoding));
        }

        if (setlocale(LC_TIME, $localeWithEncoding) !== $localeWithEncoding) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to set locale "%s" to value "%s", please check installed locales on system', 'LC_TIME', $localeWithEncoding));
        }

        return $this;
    }

    /**
     * Set config of current bean instance
     *
     * @param void $config can be an array, an Zend_Config instance of a filename
     * @param void $environment config environment value (e.g production, staging, development,...)
     *
     * @return object Zend_Config instance
     */
    public function setConfig($config = array(), $environment = null)
    {
        if (is_string($config)) {
            $config = $this->_loadConfigFromFile($config, $environment);
        } elseif ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } else if (!is_array($config)) {
            throw new BaseZF_Service_GetText_Exception('Invalid config provided; must be location of config file, a config object, or an array');
        }

        // merge with default and set has current
        $this->_config = array_merge(self::$_defaultConfig, $config);

        return $this->_config;
    }

    /**
     * Load config from file
     *
     * @param void $file config file path
     * @param void $environment config environment value (e.g production, staging, development,...)
     *
     * @return object Zend_Config instance
     */
    protected function _loadConfigFromFile($file, $environment)
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'ini':
                $config = new Zend_Config_Ini($file, $environment);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($file, $environment);
                break;

            case 'php':
            case 'inc':
                $config = include $file;
                if (!is_array($config)) {
                    throw new BaseZF_Service_GetText_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
                break;

            default:
                throw new BaseZF_Service_GetText_Exception('Invalid configuration file provided; unknown config type');
        }

        return $config->toArray();;
    }

    /**
     * Get config
     *
     * @param void $key
     * @return void $key config value
     */
    public function getConfig()
    {
        $args = func_get_args();

        $config = $this->_config;
        foreach ($args as $arg) {

            if (!isset($config[$arg])) {
                throw new BaseZF_Service_GetText_Exception(sprintf('Unable to load config value for key "%s" in BaseZF_GetText class', $arg));
            }

            $config = $config[$arg];
        }

        return $config;
    }

    //
    // Public Export API
    //

    public function exportPotFile(array $domains = null)
    {
        $domainsPaths = $this->getDomainsPaths($domains);

        //@todo
    }

    public function exportPoFiles(array $locales, array $domains = null, $archiveFormat = 'zip')
    {
        //@todo

        $domainsPaths = $this->getDomainsPaths($domains);

        // new archive
        $archive = BaseZF_Archive::newArchive($archiveFormat);

        $poDomainFiles = array();
        foreach ($locales as $locale) {
            foreach ($domainsPaths as $domain => $paths) {
                $poDomainFile = $this->getPoFileForLocaleDomain($locale, $domain);
                $archive->addFile($poDomainFile, $locale . '/' . $domain . '.po');
            }
        }

        // create and download
        $archive->createArchive();
        $archive->setOption('path', 'export_language-' . date('dmY') . '-(' . implode('_', $locales) . ').zip');
        $archive->downloadFile();

        return $archive;
    }

    //
    // Public Merge/Deploy API
    //

    /**
     * Build GetText POT files for available domains
     *
     */
    public function updatePotFiles(array $domains = null)
    {
        $domainsPaths = $this->getDomainsPaths($domains);
        $bugsAddress = $this->getConfig('bugsAddress');
        $srcEncoding = $this->getConfig('srcEncoding');
        $srcKeyword = $this->getConfig('srcKeyword');


        $potFilePaths = array();
        foreach ($domainsPaths as $domain => $paths) {

            // create pot file path
            $potFilePath = $this->getPotFileForDomain($domain);

            // check for write
            if (!is_file($potFilePath)) {

                if (!is_writable(dirname($potFilePath))) {
                    throw new BaseZF_Service_GetText_Exception(sprintf('Unable to create POT file on path "%s"', dirname($potFilePath)));
                }

                touch($potFilePath);

            } else if (!is_writable($potFilePath)) {
                throw new BaseZF_Service_GetText_Exception(sprintf('Unable to write POT file on path "%s"', $potFilePath));
            }

            // create pot file
            $cleanPaths = array_map('escapeshellarg', $paths);
            $cmd = "find " . implode(' ', $cleanPaths) . " -type f -iname '*.php' -o -iname '*.phtml' | " .
                   "xgettext -d " . escapeshellarg($domain) . " --from-code " .  escapeshellarg($srcEncoding) . " -L PHP --keyword=" . escapeshellarg($srcKeyword) . " -s -o " . escapeshellarg($potFilePath) .
                   " --msgid-bugs-address=" . escapeshellarg($bugsAddress) . " -f -";

            exec($cmd, $results, $error);
            if ($error != 0) {
                throw new BaseZF_Service_GetText_Exception(sprintf('Unable to generate POT file on path "%s" with command "%s" cause: [%s] %s', $potFilePath , $cmd, $error, implode(' ', $results)));
            }

            $potFilePaths[] = $potFilePath;
        }

        return $potFilePaths;
    }

    /**
     *
     */
    public function updatePoFiles(array $locales, array $domains = null)
    {
        $domainsPaths = $this->getDomainsPaths($domains);
        $srcEncoding = $this->getConfig('srcEncoding');

        $poDomainFilePaths = array();
        foreach ($locales as $locale) {

            $poDomainFilePaths[$locale] = array();

            foreach ($domainsPaths as $domain => $paths) {

                $poDomainFilePath = $this->getPoFileForLocaleDomain($locale, $domain);
                $potFilePath = $this->getPotFileForDomain($domain);

                if (is_file($poDomainFilePath)) {

                    if (!is_writable($poDomainFilePath)) {
                        throw new BaseZF_Service_GetText_Exception(sprintf('Unable to write PO file on path "%s"', $poDomainFilePath));
                    }

                    $cmd = "msgmerge --previous " . escapeshellarg($poDomainFilePath) . " " . escapeshellarg($potFilePath) . " -o " . escapeshellarg($poDomainFilePath);

                    exec($cmd, $results, $error);
                    if ($error != 0) {
                        throw new BaseZF_Service_GetText_Exception(sprintf('Unable to merge PO file on path "%s" with command "%s" cause: [%s] %s', $poDomainFilePath , $cmd, $error, implode(' ', $results)));
                    }

                } else {

                    $cmd = "msginit -l " . escapeshellarg($locale . '.' . $srcEncoding) . " --no-translator --no-wrap -i " . escapeshellarg($potFilePath) . " -o " . escapeshellarg($poDomainFilePath);

                    exec($cmd, $results, $error);
                    if ($error != 0) {
                        throw new BaseZF_Service_GetText_Exception(sprintf('Unable to create PO file on path "%s" with command "%s" cause: [%s] %s', $poDomainFilePath , $cmd, $error, implode(' ', $results)));
                    }
                }

                $poDomainFilePaths[$locale][$domain] = $poDomainFilePath;
            }
        }

        return $poDomainFilePaths;
    }

    /**
     *
     */
    public function deployMoFiles(array $locales, array $domains = null, $deployFuzzy = false)
    {
        $domainsPaths = $this->getDomainsPaths($domains);

        $moDomainFilePaths = array();
        foreach ($locales as $locale) {

            $moDomainFilePaths[$locale] = array();

            foreach ($domainsPaths as $domain => $paths) {

                $poDomainFilePath = $this->getPoFileForLocaleDomain($locale, $domain);
                $moDomainFilePath = $this->getMoFileForLocaleDomain($locale, $domain);

                // check for write
                if (!is_file($moDomainFilePath)) {

                    if (!is_writable(dirname($moDomainFilePath))) {
                        throw new BaseZF_Service_GetText_Exception(sprintf('Unable to create MO file on path "%s"', dirname($moDomainFilePath)));
                    }

                } else if (!is_writable($moDomainFilePath)) {
                    throw new BaseZF_Service_GetText_Exception(sprintf('Unable to write MO file on path "%s"', $moDomainFilePath));
                }

                // buidl fuzzy or not cmd
                if ($deployFuzzy) {
                    $cmd = "msgfmt -f " . escapeshellarg($poDomainFilePath) . " -o " . escapeshellarg($moDomainFilePath);
                } else {
                    $cmd = "msgfmt " . escapeshellarg($poDomainFilePath) . " -o " . escapeshellarg($moDomainFilePath);
                }

                exec($cmd, $results, $error);
                if ($error != 0) {
                    throw new BaseZF_Service_GetText_Exception(sprintf('Unable to create MO file on path "%s" with command "%s" cause: [%s] %s', $poDomainFilePath , $cmd, $error, implode(' ', $results)));
                }

                $moDomainFilePaths[$locale][$domain] = $moDomainFilePath;
            }
        }

        return $moDomainFilePaths;
    }

    /**
     *
     */
    public function mergePoFile($locale, $domain, $newPoFile)
    {
        $poDomainFilePath = $this->getPoFileForLocaleDomain($locale, $domain);

        // check for write
        if (!is_file($poDomainFilePath)) {

            if (!is_writable(dirname($poDomainFilePath))) {
                throw new BaseZF_Service_GetText_Exception(sprintf('Unable to create PO file on path "%s"', dirname($poDomainFilePath)));
            }

        } else if (!is_writable($poDomainFilePath)) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to write PO file on path "%s"', $poDomainFilePath));
        }

        // check for read
        if (!is_file($newPoFile)) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to find new PO file on path "%s" for merge with file "%s"', $newPoFile, $poDomainFilePath));
        }

        // clean files
        $this->cleanPoFile($poDomainFilePath);
        $this->cleanPoFile($newPoFile);

        $cmd = "msgmerge --no-wrap -N " . escapeshellarg($newPoFile) . " " . escapeshellarg($poDomainFilePath) . " -o " . escapeshellarg($poDomainFilePath);

        exec($cmd, $results, $error);
        if ($error != 0) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to merge PO file on path "%s" with command "%s" cause: [%s] %s', $poDomainFilePath , $cmd, $error, implode(' ', $results)));
        }

        return $poDomainFilePath;
    }

    /**
     *
     */
    public function cleanPoFile($poDomainFilePath)
    {
        if (!is_file($poDomainFilePath)) {

            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to clean missing PO file on path "%s"', $poDomainFilePath));

        } else if (!is_writable($poDomainFilePath)) {

            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to clean PO file on path "%s" cause it in readonly', $poDomainFilePath));
        }

        $cmd = "msguniq --no-wrap " . escapeshellarg($poDomainFilePath) . " -o " . escapeshellarg($poDomainFilePath) . "";

        exec($cmd, $results, $error);
        if ($error != 0) {
            throw new BaseZF_Service_GetText_Exception(sprintf('Unable to clean PO file on path "%s" with command "%s" cause: [%s] %s', $poDomainFilePath , $cmd, $error, implode(' ', $results)));
        }

        return $poDomainFilePath;
    }

    /**
     *
     */
    public function updateMsgIdFromPoFile($locale, $domain,  $newPoFile, array $srcPath = null)
    {
        $oldKeyStrings = array();
        $newKeyStrings = array();
        $content = file_get_contents($newPoFile);
        $content = preg_replace('/#\~(.*?)$/isu', '', $content);

        // get data
        $matches = array();
        preg_match_all('/msgid\\s+"(.*?)"\\s+msgstr\\s+"(.*?)"\n/iu', $content, $matches);

        // clean new file
        file_put_contents($newPoFile, $content);

        foreach ($matches[1] as $index => $value) {
            if ( !empty($matches[2][$index]) && $value != $matches[2][$index]) {
                $oldKeyStrings[] = $value;
                $newKeyStrings[] = $matches[2][$index];
            }
        }

        // nothing to change
        if (empty($oldKeyStrings)) {
            return;
        }

        // continue cleaning
        foreach ($oldKeyStrings as $index => $value) {
            $content = preg_replace('/msgid\\s+"' . preg_quote($value) . '"/iu', 'msgid "' . $newKeyStrings[$index] . '"', $content);
        }

        file_put_contents($newPoFile, $content);

        $domainsPaths = $this->getDomainsPaths(array($domain));
        $locales = array('fr_FR', 'en_GB');

        // replace in PO files
        foreach ($locales as $locale) {
            foreach ($domainsPaths as $domain => $paths) {

                $poDomainFilePath = $this->getPoFileForLocaleDomain($locale, $domain);

                // ignore non existing file
                if (!file_exists($poDomainFilePath)) {
                    continue;
                }

                // replace
                $fileContent = file_get_contents($poDomainFilePath);
                foreach ($oldKeyStrings as $index => $value) {
                    $fileContent = preg_replace('/msgid\\s+"' . preg_quote($value) . '"/iu', 'msgid "' . $newKeyStrings[$index] . '"', $fileContent);
                }

                $fileContent = preg_replace('/#\~(.*?)$/isu', '', $fileContent);

                file_put_contents($poDomainFilePath, $fileContent);

            }
        }

        // replace in sources
        foreach ($domainsPaths as $domain => $paths) {
        }
    }

    /**
     *
     */
    public function translatePoFiles(array $locales, $adapter = 'array', $adapterOptions, $hasFuzzy = true)
    {
        //@todo
    }

    //
    // Other Public API
    //

    /**
     *
     */
    public function getPoFileForLocaleDomain($locale, $domain)
    {
        $localeDirPath = $this->getConfig('localeDirPath');
        $poDir = $this->getConfig('poDir');

        return $localeDirPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $poDir . DIRECTORY_SEPARATOR . $domain . '.po';
    }

    /**
     *
     */
    public function getMoFileForLocaleDomain($locale, $domain)
    {
        $localeDirPath = $this->getConfig('localeDirPath');
        $poDir = $this->getConfig('poDir');

        return $localeDirPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $poDir . DIRECTORY_SEPARATOR . $domain . '.mo';
    }

    /**
     *
     */
    public function getPotFileForDomain($domain)
    {
        $potDirPath = $this->getConfig('potDirPath');

        return $potDirPath . DIRECTORY_SEPARATOR . $domain . '.pot';
    }

    /**
     *
     */
    public function getDomainsPaths(array $domainsRequired = null)
    {
        $domainsPaths = $this->getConfig('domainsPaths');

        if (!is_null($domainsRequired)) {
            foreach ($domainsPaths as $domain => $paths) {
                if (in_array($domain, $domainsRequired) === false) {
                    unset($domainsPaths[$domain]);
                }
            }
        }

        return $domainsPaths;
    }
}

