<?php
/**
 * NotModifiedCache.php
 *
 * @category   BaseZF_Framework
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_Controller_Plugin_NotModifiedCache extends Zend_Controller_Plugin_Abstract
{
    /**
     * Defined by Zend_Controller_Plugin_Abstract
     */
    public function dispatchLoopShutdown()
    {
        $response = $this->getResponse();
        $request = $this->_request;

        // no cache if error found or
        // if response is not a Zend_Controller_Response_Http instance
        // if request is not a Zend_Controller_Request_Http instance
        if (!($response instanceOf Zend_Controller_Response_Http) ||
            !($request instanceOf Zend_Controller_Request_Http) ||
            $response->isException()
        ) {
            return;
        }

        // Generate unique Hash-ID by using MD5
        $hashID = md5($response->getBody());

        // Specify the time when the page has
        // been changed. For example this date
        // can come from the database or any
        // file. Here we define a fixed date value:
        $lastChangeTime = 1144055759;

        // Define the proxy or cache expire time
        $expireTime = 3600; // seconds (= one hour)

        // Get request headers needed:
        $headers = array(
            'If-Modified-Since' => $request->getHeader('If-Modified-Since'),
            'If-None-Match'     => $request->getHeader('If-None-Match'),
        );

        // Set cache/proxy informations:
        $response->setHeader('Cache-Control', 'max-age=' . $expireTime); // must-revalidate
        $response->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $expireTime) . ' GMT');

        // Set last modified (this helps search engines
        // and other web tools to determine if a page has
        // been updated)
        $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastChangeTime) . ' GMT');

        // Send a "ETag" which represents the content
        // (this helps browsers to determine if the page
        // has been changed or if it can be loaded from
        // the cache - this will speed up the page loading)
        $response->setHeader('ETag', $hashID);

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
            $response->setHttpResponseCode(304);

            // That's all, now close the connection:
            $response->setHeader('Connection', 'close');

            // The magical part:
            // No content here ;-)
            $front = Zend_Controller_Front::getInstance();
            $front->returnResponse(false);

            // Just the headers
            $response->sendHeaders();

        } else {

            // Okay, the browser does not have the
            // latest version or does not have any
            // version cached. So we have to send him
            // the full page.
            $response->setHttpResponseCode(200);

            // Tell the browser which size the content
            $response->setHeader('Content-Length', mb_strlen($response));
        }
    }
}

