<?php
/**
 * CornerGenerator class in /BazeZF/Service
 *
 * @category  BazeZF
 * @package   BazeZF_Service
 * @copyright Copyright (c) 2008 BazeZF
 * @author    Harold Thetiot (hthetiot)
 */

class BaseZF_Service_CornerGenerator
{
    const CORNER_TOP_LEFT = 'tl';

    const CORNER_TOP_RIGHT = 'tr';

    const CORNER_BOTTOM_LEFT = 'bl';

    const CORNER_BOTTOM_RIGHT = 'br';

    const CORNER_ALL = 'al';

    /**
     * Default options value
     */
    protected static $_defaultOptions = array(

        // corner properties
        'cornerType'    => self::CORNER_TOP_LEFT,
        'height'        => 10,
        'width'         => 10,
        'size'          => 10,
        'border'        => 0,
        'borderColor'   => 0,
        'innerColor'    => '000000',
        'outerColor'    => 'FFFFFF',

        // others options
        'cache'         => false,
        'cachePath'     => '/tmp/',
        'cacheExpires'  => 3600,
    );

    /**
     * Options alias for URL
     */
    static protected $_optionsAlias = array(
        'cn'    => 'cornerType',
        's'     => 'size',
        'h'     => 'height',
        'w'     => 'width',
        'b'     => 'border',
        'cb'    => 'borderColor',
        'ci'    => 'innerColor',
        'co'    => 'outerColor',
    );

    /**
     * Instance options value
     */
    protected $_options = array();

    /**
     * Image ressource
     */
    protected $_imageData = null;

    //
    // Public API
    //

    /**
     *
     */
    public function __construct(array $options = null)
    {
        if (is_array($options) && !empty($options)) {
            $options = array_merge(self::$_defaultOptions, $options);
            $this->setOptions($options);
        }

        $this->_request = new Zend_Controller_Request_Http();
        $this->_response = new Zend_Controller_Response_Http();
    }

    /**
     *
     */
    public function  setOptions(array $options)
    {
        $this->_options = $options;

        return $this;
    }

    /**
     *
     */
    public static function factoryFromRequest()
    {
        $requestParams = $_GET;
        $options = array();
        foreach (self::$_optionsAlias as $alias => $option) {

            if (isset($requestParams[$alias])) {
                $options[$option] = $requestParams[$alias];
            }
        }

        return new BaseZF_Service_CornerGenerator($options);
    }

    /**
     *
     */
    public function render()
    {
        try {

            $this->_getImageCacheData();

        } catch (BaseZF_Service_CornerGenerator_Exception $e) {

            $this->_createImageData();

            $this->_setImageCacheData();
        }
    }

    /**
     *
     */
    public function display()
    {
        // Generate unique Hash-ID by using Sha1
        $hashID = $this->_getImageCacheKey();

        // Specify the time when the page has
        // been changed. For example this date
        // can come from the database or any
        // file. Here we define a fixed date value:
        $lastChangeTime = 1144055759;

        // Define the proxy or cache expire time
        $expireTime = $this->_options['cacheExpires']; // seconds (= one hour)

        // Get request headers:
        $headers = array(
            'If-Modified-Since' => $this->_request->getHeader('If-Modified-Since'),
            'If-None-Match'     => $this->_request->getHeader('If-None-Match'),
        );

        // Set cache/proxy informations:
        $this->_response->setHeader('Cache-Control', 'max-age=' . $expireTime); // must-revalidate
        $this->_response->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $expireTime) . ' GMT');

        // Set last modified (this helps search engines
        // and other web tools to determine if a page has
        // been updated)
        $this->_response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastChangeTime) . ' GMT');

        // Send a "ETag" which represents the content
        // (this helps browsers to determine if the page
        // has been changed or if it can be loaded from
        // the cache - this will speed up the page loading)
        $this->_response->setHeader('ETag', $hashID);

        // Sent Content type header
        $this->_response->setHeader('Content-type', 'image/gif');

        // Sent to browser the file name
        $this->_response->setHeader('Content-Disposition', $hashID . '.gif');

        // The browser "asks" us if the requested page has
        // been changed and sends the last modified date he
        // has in it's internal cache. So now we can check
        // if the submitted time equals our internal time value.
        // If yes then the page did not get updated
        $pageWasUpdated = !(isset($headers['If-Modified-Since']) && strtotime($headers['If-Modified-Since']) == $lastChangeTime);

        // The second possibility is that the browser sends us
        // the last Hash-ID he has. If he does we can determine
        // if he has the latest version by comparing both IDs.
        // Warning: If-None-Match header can have a value like "hash0, hash1"
        $doIDsMatch = (isset($headers['If-None-Match']) && strpos($headers['If-None-Match'], $hashID) !== false);

        // Does one of the two ways apply?
        if (!$pageWasUpdated or $doIDsMatch) {

            // Okay, the browser already has the
            // latest version of our page in his
            // cache. So just tell him that
            // the page was not modified and DON'T
            // send the content -> this saves bandwith and
            // speeds up the loading for the visitor
            $this->_response->setHttpResponseCode(304);

            // That's all, now close the connection:
            $this->_response->setHeader('Connection', 'close');

            // Just the headers
            $this->_response->sendHeaders();

        } else {

            // render image
            $this->render();

            // Okay, the browser does not have the
            // latest version or does not have any
            // version cached. So we have to send him
            // the full page.
            $this->_response->setHttpResponseCode(200);

            // Tell the browser which size the content
            $this->_response->setHeader('Content-Length', mb_strlen($this->_response->getBody()));

            // Sent the headers
            $this->_response->sendHeaders();

            // Then Content
            $this->_response->outputBody();
        }
    }

    //
    // Generator functions
    //

    /**
     *
     */
    private function _createImageData()
    {
        // @todo
        $this->_createImageDataWithGD();
    }

    /**
     *
     */
    private function _createImageDataWithGD()
    {
        //convert colors to rgb
        $rgbInner = self::_hex2rgb($this->_options['innerColor']);
        $rgbOuter = self::_hex2rgb($this->_options['outerColor']);
        $rgbBorder = self::_hex2rgb($this->_options['borderColor']);

        //we want to render the image at twice the specified size then downsample later to antialias
        $width = $this->_options['width']*2;
        $height = $this->_options['height']*2;
        $bthickness = $this->_options['border']*2;

        // get corner type properties
        switch ($this->_options['cornerType']) {

            case self::CORNER_TOP_LEFT:
                $centerX = $width;
                $centerY = $height;
                $arcStart = 180;
                $arcEnd = 270;
                break;

            case self::CORNER_TOP_RIGHT:
                $centerX = -2;
                $centerY = $height;
                $arcStart = 270;
                $arcEnd = 360;
                break;

            case self::CORNER_BOTTOM_LEFT:
                $centerX = $width;
                $centerY = -1;
                $arcStart = 90;
                $arcEnd = 0;
                break;

            case self::CORNER_BOTTOM_RIGHT:
                $centerX = -2;
                $centerY = -1;
                $arcStart = 180;
                $arcEnd = 90;
                break;

            default:
                throw new BaseZF_Service_CornerGenerator_Exception(sprintf('invalide corner type for value "%s"', $this->_options['cornerType']));
        }

        $imageScratch = imagecreatetruecolor($width, $height);
        //imageantialias function seems broken in PHP4.3.8
        //imagCeantialias($im,TRUE);
        $innerColor = imagecolorallocate($imageScratch, $rgbInner[0], $rgbInner[1], $rgbInner[2]);
        $outerColor = imagecolorallocate($imageScratch, $rgbOuter[0], $rgbOuter[1], $rgbOuter[2]);
        $borderColor = imagecolorallocate($imageScratch, $rgbBorder[0], $rgbBorder[1], $rgbBorder[2]);

        //fill in background color
        imagefill($imageScratch, 0, 0, $outerColor);

        //first deal with border case
        if ($this->_options['border'] != 0) {
          //draw filled arc for border
          imagefilledarc($imageScratch, $centerX, $centerY, $width*2, $height*2, $arcStart, $arcEnd, $borderColor, IMG_ARC_PIE);
          //draw smaller filled arc for inner region
          imagefilledarc($imageScratch, $centerX, $centerY, $width*2-2*$bthickness+1, $height*2-2*$bthickness+1, $arcStart, $arcEnd, $innerColor, IMG_ARC_PIE);
        } else {
          //plain, non-bordered corner
          //draw filled arc
          imagefilledarc($imageScratch, $centerX, $centerY, $width*2, $height*2, $arcStart, $arcEnd, $innerColor, IMG_ARC_PIE);
        }

        //resample image down to antialias
        $imageDest = imagecreatetruecolor($this->_options['width'], $this->_options['height']);

        //why size-1 here?  I'm not exactly sure, but it works
        imagecopyresampled($imageDest, $imageScratch, 0, 0, 0, 0, $this->_options['width'], $this->_options['height'], $width-1, $height-1);
        imagedestroy($imageScratch);

        $tempFile = tempnam(sys_get_temp_dir() . '/', get_class($this) . '_');
        imagegif($imageDest, $tempFile);
        $this->_response->setBody(file_get_contents($tempFile));

        imagedestroy($imageDest);
   }

    //
    // Cache functions
    //

    /**
     *
     */
    private function _getImageCacheKey()
    {
        return sha1(serialize($this->_options));
    }

    /**
     *
     */
    private function _getImageCacheData()
    {
        // @todo
        throw new BaseZF_Service_CornerGenerator_Exception();
    }

    /**
     *
     */
    private function _setImageCacheData()
    {
        // @todo
    }

    //
    // Others functions
    //

    /**
     *
     */
    private static function _hex2rgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        $rgb = array();
        for($i=0; $i<3; $i++) {
            $temp = substr($hex,2*$i,2);
            $rgb[$i] = 16 * hexdec(substr($temp,0,1)) + hexdec(substr($temp,1,1));
        }

        return $rgb;
    }
}

