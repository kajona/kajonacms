<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * The response object stores all relevant metadata of the http-response, including headers and the http-status code.
 *
 * @package Kajona\System\System
 * @author sidler@mulchprod.de
 */
class ResponseObject
{

    private $strStatusCode = "";
    private $strResponseType = "";
    private $strRedirectUrl = "";
    private $strContent = "";
    private $arrAdditionalHeaders = array();

    /**
     * @var RequestEntrypointEnum
     */
    private $objEntryPoint;

    /**
     * @var ResponseObject
     */
    private static $objInstance = null;

    /**
     *
     */
    private function __construct()
    {
        $this->objEntryPoint = RequestEntrypointEnum::INDEX();
        $this->strStatusCode = HttpStatuscodes::SC_OK;
        $this->strResponseType = HttpResponsetypes::STR_TYPE_HTML;
    }

    /**
     * @return ResponseObject
     */
    public static function getInstance()
    {
        if (self::$objInstance == null) {
            self::$objInstance = new ResponseObject();
        }

        return self::$objInstance;
    }


    /**
     * Sends all headers to the client
     */
    public function sendHeaders()
    {
        if ($this->strRedirectUrl != "") {
            $this->strStatusCode = HttpStatuscodes::SC_REDIRECT;
            $this->arrAdditionalHeaders[] = "Location: ".StringUtil::replace("&amp;", "&", $this->strRedirectUrl);
        }

        header($this->getStrStatusCode());
        header($this->getStrResponseType());

        foreach ($this->arrAdditionalHeaders as $strOneHeader) {
            header($strOneHeader);
        }
    }

    /**
     * Sends headers to the client, to allow conditionalGets
     *
     * @param string $strChecksum Checksum of the content. Must be unique for one state.
     */
    public function sendConditionalGetHeader($strChecksum)
    {
        $this->addHeader("ETag: ".$strChecksum);
        $this->addHeader("Cache-Control: max-age=86400, must-revalidate");
    }

    /**
     * Checks, if the browser sent the same checksum as provided. If so,
     * a http 304 is sent to the browser
     *
     * @param $strChecksum
     *
     * @return bool
     */
    public function processConditionalGetHeaders($strChecksum)
    {
        if (issetServer("HTTP_IF_NONE_MATCH")) {
            if (getServer("HTTP_IF_NONE_MATCH") == $strChecksum) {
                //strike. no further actions needed.
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_NOT_MODIFIED);
                ResponseObject::getInstance()->addHeader("ETag: ".$strChecksum);
                ResponseObject::getInstance()->addHeader("Cache-Control: max-age=86400, must-revalidate");

                return true;
            }
        }

        return false;
    }


    public function sendContent()
    {

        ignore_user_abort(true);
        if (trim($this->strContent) != "") {
            echo $this->strContent;
            @ob_flush();
            @flush();
        }
        else {
            header("Content-Length: 0");
            header("Content-Encoding: none");
            header("Connection: close");
            @ob_end_flush();
            @ob_flush();
            @flush();
        }

        if (!Session::getInstance()->getBitClosed()) {
            Session::getInstance()->sessionClose();
        }
    }


    /**
     * @param $strHeader
     */
    public function addHeader($strHeader)
    {
        $this->arrAdditionalHeaders[] = $strHeader;
    }

    /**
     * @param $strStatusCode
     */
    public function setStrStatusCode($strStatusCode)
    {
        $this->strStatusCode = $strStatusCode;
    }

    /**
     * @return string
     */
    public function getStrStatusCode()
    {
        return $this->strStatusCode;
    }

    /**
     * @param $stResponseType
     *
     * @deprecated use setStrResponseType instead
     */
    public function setStResponseType($stResponseType)
    {
        $this->strResponseType = $stResponseType;
    }

    /**
     * @return string
     * @deprecated use getStrResponseType instead
     */
    public function getStResponseType()
    {
        return $this->strResponseType;
    }

    /**
     * @param $stResponseType
     */
    public function setStrResponseType($stResponseType)
    {
        $this->strResponseType = $stResponseType;
    }

    /**
     * @return string
     */
    public function getStrResponseType()
    {
        return $this->strResponseType;
    }

    /**
     * @param $strContent
     */
    public function setStrContent($strContent)
    {
        $this->strContent = $strContent;
    }

    /**
     * @return string
     */
    public function getStrContent()
    {
        return $this->strContent;
    }

    /**
     * @param $strRedirectUrl
     */
    public function setStrRedirectUrl($strRedirectUrl)
    {
        $this->strRedirectUrl = $strRedirectUrl;
    }

    /**
     * @return string
     */
    public function getStrRedirectUrl()
    {
        return $this->strRedirectUrl;
    }

    /**
     * @param RequestEntrypointEnum $objEntryPoint
     */
    public function setObjEntrypoint(RequestEntrypointEnum $objEntryPoint)
    {
        $this->objEntryPoint = $objEntryPoint;
    }

    /**
     * @return RequestEntrypointEnum
     */
    public function getObjEntrypoint()
    {
        return $this->objEntryPoint;
    }
}
