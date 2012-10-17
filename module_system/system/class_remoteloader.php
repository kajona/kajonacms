<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                      *
********************************************************************************************************/



/**
 * Class providing a wrapper to remote objects. Provides methods to load text-files (e.g. xml-files)
 * from a remote server. Tries to establish a connection via file_get_contents or via sockets.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_remoteloader {

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
	private $intMaxCachetime = _remoteloader_max_cachetime_;


	/**
	 * Builds the query and tries to get the remote content either by a cache-lookup
	 * or via a remote-connection. Use $bitForceReload if you want to skip the cache-lookup.
	 *
	 * @param bool $bitForceReload
	 * @return string
	 * @throws class_exception
	 */
	public function getRemoteContent($bitForceReload = false) {

		$strReturn = false;

		//check all needed params
		if((int)$this->intPort < 0 || $this->strHost == "" || $this->strProtocolHeader == "")
		    throw new class_exception("Not all needed values given", class_exception::$level_ERROR);

		//first try: load it via the cache
		if ($bitForceReload === false) {
		    $strReturn = $this->loadByCache();

		    //if the cache was succesfull, return
	        if($strReturn !== false) {
	            class_logger::getInstance()->addLogRow("remote request found in cache", class_logger::$levelInfo);
	            return $strReturn;
	        }
		}

		//second try: file_get_content
		if($strReturn === false)
		    $strReturn = $this->connectByFileGetContents();

		//third: fsockopen
		if($strReturn === false)
		    $strReturn = $this->connectFSockOpen();

		//fourth: curl
		if($strReturn === false)
		    $strReturn = $this->connectViaCurl();

        //fifth try: sockets
		if($strReturn === false)
		    $strReturn = $this->connectViaSocket();


		//in case of an error, save the result to the cache, too:
		//the possibility of receiving a regular response within the next interval is rather small.
		//BUT: reduce the max cachetime to a third of its' original value.
		if($strReturn === false) {
			$this->intMaxCachetime = (int)($this->intMaxCachetime/3);
		}

		//and save to the cache
		if($strReturn !== false) {
		    $this->saveResponseToCache($strReturn);
		}

		//throw a general error?
		if($strReturn === false) {
			class_logger::getInstance()->addLogRow("remoteloader failed. protocol: ".$this->strProtocolHeader." host: ".$this->strHost." port: ".$this->intPort." params: ".$this->strQueryParams, class_logger::$levelWarning);
		    throw new class_exception("Error loading the remote content", class_exception::$level_ERROR);
		}

		class_logger::getInstance()->addLogRow("new remote-request succeeded. protocol: ".$this->strProtocolHeader." host: ".$this->strHost." port: ".$this->intPort." params: ".$this->strQueryParams, class_logger::$levelInfo);

		return $strReturn;
	}

	/**
	 * Creates a md5 based cache-checksum to identify the query
	 *
	 * @return string
	 */
	private function buildCacheChecksum() {
		return md5($this->strProtocolHeader.$this->strHost.$this->intPort.$this->strQueryParams);
	}

	/**
	 * Tries to find a valid cache-entry for the current query
	 *
	 * @return string or false in case of no matching entry
	 */
	private function loadByCache() {

        if(!$this->bitCacheEnabled)
            return false;

		$strReturn = false;

        //try to find an entry in the cache
        $objCachedEntry = class_cache::getCachedEntry(__CLASS__, $this->buildCacheChecksum());
        if($objCachedEntry != null)
            $strReturn = $objCachedEntry->getStrContent();

		return $strReturn;
	}

	/**
	 * Tries to load a remote located content via the built in php-function
	 * and returns the string
	 *
	 * @return string or false in case of an error
	 */
	private function connectByFileGetContents() {
		$strReturn = "";

		if(class_carrier::getInstance()->getObjConfig()->getPhpIni("allow_url_fopen") != 1)
            return false;

		$strReturn = file_get_contents( $this->strProtocolHeader.
		                                 $this->strHost.
		                                ($this->intPort > 0 ? ":".$this->intPort : "" ).
		                                 $this->strQueryParams);

		return $strReturn;
	}

	/**
	 * Tries to load a remote located content via the socket-class
	 * and returns the string
	 *
	 * @return string or false in case of an error
	 */
	private function connectViaSocket() {
		$strReturn = "";

		//request in list of supported protocols?
		if($this->strProtocolHeader == "http://" || $this->strProtocolHeader == "https://") {

	        try {
	            $objSocket = new class_socket($this->strHost, ($this->intPort > 0 ? $this->intPort : 80));
	            $objSocket->connect();
	            $objSocket->write("GET ".$this->strQueryParams." HTTP/1.1");
	            $objSocket->write("HOST: ".$this->strHost);
	            $objSocket->writeLimiter();
	            $strReturn = $objSocket->read();
	            $objSocket->close();

	            $strReturn = trim($strReturn);
	            if(uniStrpos($strReturn, "\r\n\r\n") !== false) {
	            	$strReturn = trim(uniSubstr($strReturn, uniStrpos($strReturn, "\r\n\r\n")));
	            }

	            if(uniStrpos($strReturn, "<") !== false) {
	            	$strReturn = trim(uniSubstr($strReturn, uniStrpos($strReturn, "<")));
	            }

	            //and, if given, remove the last 0
	            if(uniSubstr($strReturn, -1) == "0")
	               $strReturn = uniSubstr($strReturn, 0, -1);

	        }
	        catch (class_exception $objException) {
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
	private function connectFSockOpen() {
		$strReturn = "";

		//request in list of supported protocols?
		if($this->strProtocolHeader == "http://" || $this->strProtocolHeader == "https://") {

            try {
                $intErrorNumber = "";
                $strErrorString = "";

                $strProtocolAdd = "";
                if($this->strProtocolHeader == "http://")
                    $strProtocolAdd = "tcp://";
                if($this->strProtocolHeader == "https://")
                    $strProtocolAdd = "tls://";


                $arrUrl=parse_url($this->strProtocolHeader.$this->strHost);
                $objRemoteResource = @fsockopen($strProtocolAdd.$arrUrl['host'],($this->intPort > 0 ? $this->intPort : 80),$intErrorNumber,$strErrorString,10);

                if(!isset($arrUrl['path']))
                    $arrUrl['path'] = "";

                if(is_resource($objRemoteResource)) {
                    fwrite($objRemoteResource,"GET ".$arrUrl['path'].$this->strQueryParams." HTTP/1.0\r\n");
                    fwrite($objRemoteResource,"Host: ".$arrUrl['host']."\r\n");
                    fwrite($objRemoteResource,"Connection: close\r\n\r\n");

                    while(!feof($objRemoteResource)){
                        $strReturn .= fgets($objRemoteResource,1024);
                    }
                    fclose($objRemoteResource);
                }

                if ($intErrorNumber!=0)
                    return false;

                if(uniStrpos($strReturn, "\r\n\r\n") !== false) {
                    $strReturn = trim(uniSubstr($strReturn, uniStrpos($strReturn, "\r\n\r\n")));
                }

                $strReturn = trim($strReturn);
                if(uniStrpos($strReturn, "<") !== false) {
                    $strReturn = trim(uniSubstr($strReturn, uniStrpos($strReturn, "<")));
                }

                //and, if given, remove the last 0
                if(uniSubstr($strReturn, -1) == "0")
                    $strReturn = uniSubstr($strReturn, 0, -1);

            }
            catch (class_exception $objException) {
                $strReturn = false;
            }

		}
		else {
			//protocol not supported via fsockopen
		    $strReturn = false;
		}

        //no valid response found
        if (strlen($strReturn)==0)
            $strReturn = false;


		return $strReturn;
	}

	/**
	 * Tries to load a remote located content via curl extensions
	 * and returns the string
	 *
	 * @return string or false in case of an error
	 */
	private function connectViaCurl() {
	    $strReturn = "";

	    if(!function_exists("curl_exec"))
	        return false;

	    // create a new curl-handle
        $objHandle = curl_init();

        // set the params
        curl_setopt($objHandle, CURLOPT_URL, $this->strProtocolHeader.
		                                     $this->strHost.
		                                    ($this->intPort > 0 ? ":".$this->intPort : "" ).
		                                     $this->strQueryParams);
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
	 * @return bool
	 */
	private function saveResponseToCache($strResponse) {
        if(!$this->bitCacheEnabled)
            return true;

        //create a cache-instance
        $objCache = class_cache::getCachedEntry(__CLASS__, $this->buildCacheChecksum(), "", "", true);
        $objCache->setStrContent($strResponse);
        $objCache->setIntLeasetime(time()+(int)$this->intMaxCachetime);

        return $objCache->updateObjectToDb();

	}

    /**
     * Deletes all entries currently saved to the cache
     *
     * @return bool
     */
    public function flushCache() {
        return class_cache::flushCache(__CLASS__);
    }

	/**
	 * Sets the protocol to use. Default is https://.
	 *
	 * @param string $strHeader
	 */
	public function setStrProtocolHeader($strHeader) {
		$this->strProtocolHeader = $strHeader;
	}

	/**
	 * Sets the port to use. Default is 80.
	 *
	 * @param int $intPort
	 */
	public function setIntPort($intPort) {
		$this->intPort = (int)$intPort;
	}

	/**
	 * Sets the remote host
	 *
	 * @param string $strHost
	 */
	public function setStrHost($strHost) {
		$this->strHost = $strHost;
	}

	/**
	 * Sets additional query params, e.g. ?param=value&param2=value2 or /index.html
	 *
	 * @param string $strQueryParams
	 */
	public function setStrQueryParams($strQueryParams) {
		$this->strQueryParams = $strQueryParams;
	}

    /**
     * @param boolean $bitCacheEnabled
     */
    public function setBitCacheEnabled($bitCacheEnabled) {
        $this->bitCacheEnabled = $bitCacheEnabled;
    }

    /**
     * @return boolean
     */
    public function getBitCacheEnabled() {
        return $this->bitCacheEnabled;
    }

}



