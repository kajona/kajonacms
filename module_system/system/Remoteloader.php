<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                      *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Class providing a wrapper to remote objects. Provides methods to load text-files (e.g. xml-files)
 * from a remote server. Tries to establish a connection via file_get_contents or via sockets.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class Remoteloader
{

    /**
     * @var bool
     */
    private $bitCacheEnabled = true;

    /**
     * The protocol to use, e.g. http://
     *
     * @var string
     */
    private $strProtocolHeader = "http://";

    /**
     * The port to open the connection, e.g. 80,
     * especially to be used with sockets.
     *
     * @var int
     */
    private $intPort = 80;

    /**
     * The host to query
     *
     * @var string
     */
    private $strHost = "";

    /**
     * Additional params to add after the port-definition
     *
     * @var string
     */
    private $strQueryParams = "";

    /**
     * The maximum time in seconds a request may be cached.
     * Default is set via the system-settings.
     *
     * @var int
     */
    private $intMaxCachetime = 3600;

    /**
     * @inject system_cache_manager
     * @var CacheManager
     */
    private $objCachemanager = null;


    public function __construct()
    {
        $objBuilder = new ObjectBuilder(Carrier::getInstance()->getContainer());
        $objBuilder->resolveDependencies($this);
        $this->intMaxCachetime = SystemSetting::getConfigValue("_remoteloader_max_cachetime_");
    }


    /**
     * Builds the query and tries to get the remote content either by a cache-lookup
     * or via a remote-connection. Use $bitForceReload if you want to skip the cache-lookup.
     *
     * @param bool $bitForceReload
     *
     * @return string
     * @throws Exception
     */
    public function getRemoteContent($bitForceReload = false)
    {

        $strReturn = false;

        //check all needed params
        if ((int)$this->intPort < 0 || $this->strHost == "" || $this->strProtocolHeader == "") {
            throw new Exception("Not all required values given", Exception::$level_ERROR);
        }

        //first try: load it via the cache
        if ($bitForceReload === false) {
            $strReturn = $this->loadByCache();

            //if the cache was succesfull, return
            if ($strReturn !== false) {
                Logger::getInstance(Logger::REMOTELOADER)->addLogRow("remote request found in cache", Logger::$levelInfo);
                return $strReturn;
            }
        }

        //second try: file_get_content
        if ($strReturn === false) {
            $strReturn = $this->connectByFileGetContents();
            Logger::getInstance(Logger::REMOTELOADER)->addLogRow("loaded via filegetcontents: ".$strReturn, Logger::$levelInfo);
        }

        //third try: curl
        if ($strReturn === false) {
            $strReturn = $this->connectViaCurl();
            Logger::getInstance(Logger::REMOTELOADER)->addLogRow("loaded via curl: ".$strReturn, Logger::$levelInfo);
        }

        //fourth try: fsockopen
        if ($strReturn === false) {
            $strReturn = $this->connectFSockOpen();
            Logger::getInstance(Logger::REMOTELOADER)->addLogRow("loaded via fsockopen: ".$strReturn, Logger::$levelInfo);
        }

        //fifth try: sockets
        if ($strReturn === false) {
            $strReturn = $this->connectViaSocket();
            Logger::getInstance(Logger::REMOTELOADER)->addLogRow("loaded via socket: ".$strReturn, Logger::$levelInfo);
        }


        //in case of an error, save the result to the cache, too:
        //the possibility of receiving a regular response within the next interval is rather small.
        //BUT: reduce the max cachetime to a third of its' original value.
        if ($strReturn === false) {
            $this->intMaxCachetime = (int)($this->intMaxCachetime / 3);
        }

        //and save to the cache
        if ($strReturn !== false) {
            $this->saveResponseToCache($strReturn);
        }

        //throw a general error?
        if ($strReturn === false) {
            Logger::getInstance(Logger::REMOTELOADER)->addLogRow(
                "remoteloader failed. protocol: ".$this->strProtocolHeader." host: ".$this->strHost." port: ".$this->intPort." params: ".$this->strQueryParams,
                Logger::$levelWarning
            );
            throw new Exception("Error loading the remote content", Exception::$level_ERROR);
        }

        Logger::getInstance(Logger::REMOTELOADER)->addLogRow(
            "new remote-request succeeded. protocol: ".$this->strProtocolHeader." host: ".$this->strHost." port: ".$this->intPort." params: ".$this->strQueryParams,
            Logger::$levelInfo
        );

        return $strReturn;
    }

    /**
     * Creates a md5 based cache-checksum to identify the query
     *
     * @return string
     */
    private function buildCacheChecksum()
    {
        return sha1(__CLASS__.$this->strProtocolHeader.$this->strHost.$this->intPort.$this->strQueryParams);
    }

    /**
     * Tries to find a valid cache-entry for the current query
     *
     * @return string or false in case of no matching entry
     */
    private function loadByCache()
    {

        if (!$this->bitCacheEnabled) {
            return false;
        }

        return $this->objCachemanager->getValue($this->buildCacheChecksum());
    }

    /**
     * Tries to load a remote located content via the built in php-function
     * and returns the string
     *
     * @return string or false in case of an error
     */
    private function connectByFileGetContents()
    {

        if (Carrier::getInstance()->getObjConfig()->getPhpIni("allow_url_fopen") != 1) {
            return false;
        }

        $objCtx = stream_context_create(
            array(
                'http'  => array(
                    'timeout'       => 1,
                    'max_redirects' => 5
                ),
                'https' => array(
                    'timeout'       => 1,
                    'max_redirects' => 5
                )
            )
        );

        $strReturn = @file_get_contents(
            $this->strProtocolHeader.
            $this->strHost.
            ($this->intPort > 0 ? ":".$this->intPort : "").
            $this->strQueryParams,
            false,
            $objCtx
        );

        return $strReturn;
    }

    /**
     * Tries to load a remote located content via the socket-class
     * and returns the string
     *
     * @return string or false in case of an error
     */
    private function connectViaSocket()
    {

        //request in list of supported protocols?
        if ($this->strProtocolHeader == "http://" || $this->strProtocolHeader == "https://") {

            try {
                $objSocket = new Socket($this->strHost, ($this->intPort > 0 ? $this->intPort : 80));
                $objSocket->connect();
                $objSocket->write("GET ".$this->strQueryParams." HTTP/1.1");
                $objSocket->write("HOST: ".$this->strHost);
                $objSocket->writeLimiter();
                $strReturn = $objSocket->read();
                $objSocket->close();

                $strReturn = trim($strReturn);
                if (uniStrpos($strReturn, "\r\n\r\n") !== false) {
                    $strReturn = trim(uniSubstr($strReturn, uniStrpos($strReturn, "\r\n\r\n")));
                }

                if (uniStrpos($strReturn, "<") !== false) {
                    $strReturn = trim(uniSubstr($strReturn, uniStrpos($strReturn, "<")));
                }

                //and, if given, remove the last 0
                if (uniSubstr($strReturn, -1) == "0") {
                    $strReturn = uniSubstr($strReturn, 0, -1);
                }

            }
            catch (Exception $objException) {
                Logger::getInstance(Logger::REMOTELOADER)->addLogRow("exception in socket: ".$objException->getMessage(), Logger::$levelInfo);
                //$objException->processException();
                $strReturn = false;
            }

        }
        else {
            //protocol not supported via sockets
            $strReturn = false;
        }

        return $strReturn;
    }

    /**
     * Tries to load a remote located content via fsockopen
     * and returns the string
     *
     * @return string or false in case of an error
     */
    private function connectFSockOpen()
    {
        $strReturn = "";

        //request in list of supported protocols?
        if ($this->strProtocolHeader == "http://" || $this->strProtocolHeader == "https://") {

            try {
                $intErrorNumber = "";
                $strErrorString = "";

                $strProtocolAdd = "";
                if ($this->strProtocolHeader == "http://") {
                    $strProtocolAdd = "tcp://";
                }
                if ($this->strProtocolHeader == "https://") {
                    $strProtocolAdd = "tls://";
                }


                $arrUrl = parse_url($this->strProtocolHeader.$this->strHost);
                $objRemoteResource = @fsockopen($strProtocolAdd.$arrUrl['host'], ($this->intPort > 0 ? $this->intPort : 80), $intErrorNumber, $strErrorString, 3);

                if (!isset($arrUrl['path'])) {
                    $arrUrl['path'] = "";
                }

                if (is_resource($objRemoteResource)) {
                    fwrite($objRemoteResource, "GET ".$arrUrl['path'].$this->strQueryParams." HTTP/1.0\r\n");
                    fwrite($objRemoteResource, "Host: ".$arrUrl['host']."\r\n");
                    fwrite($objRemoteResource, "Connection: close\r\n\r\n");

                    $bitStripped = false;
                    while (!feof($objRemoteResource)) {
                        $strTemp = fgets($objRemoteResource, 1024);
                        $strReturn .= $strTemp;
                        if ($strTemp == "\r\n" && !$bitStripped) {
                            $strReturn = "";
                            $bitStripped = true;
                        }
                    }
                    fclose($objRemoteResource);
                }

                if ($intErrorNumber != 0) {
                    return false;
                }

                //and, if given, remove the last 0
                if (uniSubstr($strReturn, -1) == "0") {
                    $strReturn = uniSubstr($strReturn, 0, -1);
                }

            }
            catch (Exception $objException) {
                Logger::getInstance(Logger::REMOTELOADER)->addLogRow("exception in fsock: ".$objException->getMessage(), Logger::$levelInfo);
                $strReturn = false;
            }

        }
        else {
            //protocol not supported via fsockopen
            $strReturn = false;
        }

        //no valid response found
        if (strlen($strReturn) == 0) {
            $strReturn = false;
        }


        return $strReturn;
    }

    /**
     * Tries to load a remote located content via curl extensions
     * and returns the string
     *
     * @return string or false in case of an error
     */
    private function connectViaCurl()
    {

        if (!function_exists("curl_exec")) {
            return false;
        }

        // create a new curl-handle
        $objHandle = curl_init();

        // set the params
        curl_setopt(
            $objHandle,
            CURLOPT_URL,
            $this->strProtocolHeader.$this->strHost.($this->intPort > 0 ? ":".$this->intPort : "").$this->strQueryParams
        );
        //response-header not needed
        curl_setopt($objHandle, CURLOPT_HEADER, false);
        //return as string
        curl_setopt($objHandle, CURLOPT_RETURNTRANSFER, true);
        //allow to follow redirects
        curl_setopt($objHandle, CURLOPT_FOLLOWLOCATION, true);
        //max number of redirects
        curl_setopt($objHandle, CURLOPT_MAXREDIRS, 2);

        //and execute...
        $strReturn = curl_exec($objHandle);

        //close the handle
        curl_close($objHandle);

        return $strReturn;
    }

    /**
     * saves the response from the server to the internal cache
     *
     * @param string $strResponse
     *
     * @return bool
     */
    private function saveResponseToCache($strResponse)
    {
        if (!$this->bitCacheEnabled) {
            return true;
        }

        return $this->objCachemanager->addValue($this->buildCacheChecksum(), $strResponse, (int)$this->intMaxCachetime);
    }

    /**
     * Sets the protocol to use. Default is http://.
     *
     * @param string $strHeader
     */
    public function setStrProtocolHeader($strHeader)
    {
        $this->strProtocolHeader = $strHeader;
    }

    /**
     * Sets the port to use. Default is 80.
     *
     * @param int $intPort
     */
    public function setIntPort($intPort)
    {
        $this->intPort = (int)$intPort;
    }

    /**
     * Sets the remote host
     *
     * @param string $strHost
     */
    public function setStrHost($strHost)
    {
        $this->strHost = $strHost;
    }

    /**
     * Sets additional query params, e.g. ?param=value&param2=value2 or /index.html
     *
     * @param string $strQueryParams
     */
    public function setStrQueryParams($strQueryParams)
    {
        $this->strQueryParams = $strQueryParams;
    }

    /**
     * @param boolean $bitCacheEnabled
     */
    public function setBitCacheEnabled($bitCacheEnabled)
    {
        $this->bitCacheEnabled = $bitCacheEnabled;
    }

    /**
     * @return boolean
     */
    public function getBitCacheEnabled()
    {
        return $this->bitCacheEnabled;
    }

}



