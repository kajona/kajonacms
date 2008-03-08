<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_remoteloader.php                                                                              *
*   Class providing a wrapper to remote objects. Provides methods to load text-files (e.g. xml-files)   *
*   from a remote server. Tries to establish a connection via file_get_contents or via sockets.         *
*   Fetched data is cached in order to speed up the performance.                                        *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                      *
********************************************************************************************************/



include_once(_systempath_."/class_socket.php");

/**
 * Class providing a wrapper to remote objects. Provides methods to load text-files (e.g. xml-files)
 * from a remote server. Tries to establish a connection via file_get_contents or via sockets.
 *
 * @package modul_system
 */
class class_remoteloader {

	/**
	 * Name of the table working a a cache.
	 * Being set in the constructor.
	 *
	 * @var string
	 */
	private $strCacheTable;
	
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
	 * @var unknown_type
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
	 * Constructor, as usual ;)
	 *
	 */
	public function __construct() {
		$this->strCacheTable = _dbprefix_."remoteloader_cache";
	}
	
	/**
	 * Builts the query and tries to get the remote content either by a cache-lookup
	 * or via a remote-connection
	 * 
	 * @return string
	 * @throws class_exception
	 */
	public function getRemoteContent() {
		
		$strReturn = false;
		
		//check all needed params
		if((int)$this->intPort < 0 || $this->strHost == "" || $this->strProtocolHeader == "")
		  throw new class_exception("Not all needed values given", class_exception::$level_ERROR);
		  
		//first try: load it via the cache  
		$strReturn = $this->loadByCache();
		
		//if the cache was succesfull, return
		if($strReturn !== false) {
			class_logger::getInstance()->addLogRow("remote request found in cache", class_logger::$levelInfo);
		    return $strReturn;
		}
		    
		
		//second try: file_get_content
		if($strReturn === false) 
		    $strReturn = $this->connectByFileGetContents();

		//third try: sockets
		if($strReturn === false)
		    $strReturn = $this->connectViaSocket();
		    
		    
		//in case of an error, save the result to the cache, too:
		//the possibility of receiving a regular time within the next interval is rather small.
		//BUT: reduce the max cachetime to a third of its' original value.
		if($strReturn === false)
			$this->intMaxCachetime = (int)($this->intMaxCachetime/3);    

		//and clean up the cache
        $this->doCacheCleanup();	
			
		//and save to the cache
		$this->saveResponseToCache($strReturn);

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
	private function builtCacheChecksum() {
		return md5($this->strProtocolHeader.$this->strHost.$this->intPort.$this->strQueryParams);
	}
	
	/**
	 * Tries to find a valid cache-entry for the current query
	 *
	 * @return string or false in case of no matching entry
	 */
	private function loadByCache() {
		$strReturn = "";
		
		$strQuery = "SELECT remoteloader_cache_response
		               FROM ".$this->strCacheTable."
		              WHERE remoteloader_cache_releasetime > ".(int)time()."
		                AND remoteloader_cache_checksum = '".dbsafeString($this->builtCacheChecksum())."'";
		
		$arrRow = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
        if(isset($arrRow["remoteloader_cache_response"]))
           	$strReturn = $arrRow["remoteloader_cache_response"];
        else
            $strReturn = false;
            
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
		
		$strReturn = @file_get_contents( $this->strProtocolHeader.
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
		if($this->strProtocolHeader == "http://") {
		
	        try {
	            $objSocket = new class_socket($this->strHost, ($this->intPort > 0 ? $this->intPort : 80));
	            $objSocket->connect();
	            $objSocket->write("GET ".$this->strQueryParams." HTTP/1.1");
	            $objSocket->write("HOST: ".$this->strHost);
	            $objSocket->writeLimiter();
	            $strReturn = $objSocket->read();
	            $objSocket->close();
	            
	            $strReturn = trim($strReturn);
	            
	            //in http-mode, try to skip the headers
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
	 * saves the response from the server to the internal cache
	 *
	 * @param string $strResponse
	 * @return bool
	 */
	private function saveResponseToCache($strResponse) {
		//calculate new releasetime
		$intReleasetime = time()+(int)$this->intMaxCachetime;
		
		$strQuery = "INSERT INTO ".$this->strCacheTable."
		                    (remoteloader_cache_checksum, remoteloader_cache_releasetime, remoteloader_cache_response) VALUES
		                    ('".dbsafeString($this->builtCacheChecksum())."', ".(int)$intReleasetime." , '".dbsafeString($strResponse, false)."')";
		
		return class_carrier::getInstance()->getObjDB()->_query($strQuery);
		
	}
	
	/**
	 * Removes invalid entries from the cache
	 *
	 * @return bool
	 */
	private function doCacheCleanup() {
		$strQuery = "DELETE FROM ".$this->strCacheTable." 
		                   WHERE remoteloader_cache_releasetime <= ".(int)time()."";
		
		return class_carrier::getInstance()->getObjDB()->_query($strQuery);
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
	
}



?>