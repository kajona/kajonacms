<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Class providing a wrapper for sockets.
 * Use an instance of this class to acces a server-socket.
 *
 * An example how to use this class would be
 *   $objSocket = new Socket("www.kajona.de", 80);
 *   $objSocket->connect();
 *   $objSocket->write("GET / HTTP/1.1");
 *   $objSocket->write("HOST: www.kajona.de");
 *   $objSocket->writeLimiter();
 *   echo $objSocket->read();
 *   $objSocket->close();
 *
 * @package module_system
 */
class Socket {

    /**
     * LineLimiter used in most cases
     *
     * @var string
     */
    public static $strLineLimiter = "\r\n";

    /**
     * Defines the domain of the socket, here IP v4
     *
     * @var string
     */
    public static $strDomainIp4 = AF_INET;

    /**
     * Defines a IPv6 net as the current domain
     *
     * @var string
     */
    public static $strDomainIp5 = AF_INET6;

    /**
     * Socket of type stream, used in most cases (tcp)
     *
     * @var string
     */
    public static $strTypeStream = SOCK_STREAM;

    /**
     * Socket of type raw, implement the protocol yourself
     *
     * @var string
     */
    public static $strTypeRaw = SOCK_RAW;

    /**
     * Use tcp as protocol
     *
     * @var string
     */
    public static $strProtoTcp = "tcp";

    /**
     * use udp as protocol
     *
     * @var string
     */
    public static $strProtoUdp = "udp";

    /**
     * Number of bytes to be read from the socket at once
     *
     * @var int
     */
    private static $intReadSize = 1024;

    /**
     * servers socket port
     *
     * @var int
     */
    private $intPort = 0;

    /**
     * servers hostname
     *
     * @var string
     */
    private $strHostname = "";

    private $strDomain = null;
    private $strType = null;
    private $strProtocol = null;
    private $objSocket = null;

    /**
     * Creates a new instance of Socket
     *
     * @param string $strHostname
     * @param int $intPort
     *
     * @throws Exception
     */
    public function __construct($strHostname, $intPort) {
        $this->intPort = $intPort;
        $this->strHostname = $strHostname;

        //initial constants
        $bitSupportEnabled = true;
        //if(!defined("AF_INET"))

        //Sockets implemented?
        if(!function_exists("socket_create"))
            throw new Exception("Socket Support not enabled!: ", Exception::$level_ERROR);

    }

    public function __destruct() {
        $this->close();
    }

    /**
     * Connects the socket to the server on the port passed by the constructor
     *
     * @return bool
     * @throws Exception
     */
    public function connect() {
        //create socket
        $this->objSocket = socket_create($this->getStrDomain(), $this->getStrType(), getprotobyname($this->getStrProtocol()));
        if($this->objSocket === false)
            throw new Exception("Socket Exception: connection could not be established", Exception::$level_ERROR);
        //connect socket to server
        if(@socket_connect($this->objSocket, $this->strHostname, $this->intPort)) {
            return true;
        }
        else {
            throw new Exception("Socket Exception: ".socket_last_error(), Exception::$level_ERROR);
        }
    }

    /**
     * Closes the current socket
     *
     */
    public function close() {
        if($this->objSocket != null)
            socket_close($this->objSocket);
        $this->objSocket = null;
    }

    /**
     * Writes a string to the current socket
     *
     * @param string $strString
     * @return bool
     * @throws Exception
     */
    public function write($strString) {
        if($this->objSocket == null)
            throw new Exception("Socket Exception: Socket not connected", Exception::$level_ERROR);
        //write passed string
        $intNrWritten = socket_write($this->objSocket, $strString, strlen($strString));
        if($intNrWritten === false)
            throw new Exception("Socket Exception: ".socket_last_error(), Exception::$level_ERROR);
        //write limiter
        try {
            $this->writeLimiter();
        }
        catch (Exception $objException) {
            throw new Exception("Socket Exception: ".socket_last_error(), Exception::$level_ERROR);
        }
        return true;
    }

    /**
     * Sends a write-limiter to the socket.
     * This method is calles implicit after every write
     *
     * @return bool
     * @throws Exception
     */
    public function writeLimiter() {
        if($this->objSocket == null)
            throw new Exception("Socket Exception: Socket not connected", Exception::$level_ERROR);
        //write limiter
        $intNrWritten = socket_write($this->objSocket, Socket::$strLineLimiter, strlen(Socket::$strLineLimiter));
        if($intNrWritten === false)
            throw new Exception("Socket Exception: ".socket_last_error(), Exception::$level_ERROR);

        return true;
    }

    /**
     * Reads all data from the current socket
     * NOTE: Reads binary, not in ascii-mode!
     *
     * @return string
     * @throws Exception
     */
    public function read() {
        if($this->objSocket == null)
            throw new Exception("Socket Exception: Socket not connected", Exception::$level_ERROR);
        $strReturn = "";
        $strRead = socket_read($this->objSocket, Socket::$intReadSize, PHP_BINARY_READ);
        while(strlen($strRead) > 0 && $strRead !== false ) {
            $strReturn .= $strRead;
            if(strlen($strRead) < Socket::$intReadSize)
                $strRead = false;
            else
                $strRead = @socket_read($this->objSocket, Socket::$intReadSize, PHP_BINARY_READ);
        }

        return $strReturn;
    }



    public function setStrDomain($strDomain) {
        $this->strDomain = $strDomain;
    }

    public function setStrType($strType) {
        $this->strType = $strType;
    }

    public function setStrProtocol($strProtocol) {
        $this->strProtocol = $strProtocol;
    }

    public function getStrDomain() {
        if($this->strDomain == null)
            $this->strDomain = Socket::$strDomainIp4;

        return $this->strDomain;
    }

    public function getStrType() {
        if($this->strType == null)
            $this->strType = Socket::$strTypeStream;

        return $this->strType;
    }

    public function getStrProtocol() {
        if($this->strProtocol == null)
            $this->strProtocol = Socket::$strProtoTcp;

        return $this->strProtocol;
    }
}

