<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\System\System;



class ResponseObject {

    private $strStatusCode = "";
    private $strResponseType = "";
    private $strRedirectUrl = "";
    private $strContent = "";
    private $arrAdditionalHeaders = array();

    /**
     * @var ResponseObject
     */
    private static $objInstance = null;

    /**
     *
     */
    private function __construct() {
        $this->strStatusCode = HttpStatuscodes::SC_OK;
        $this->strResponseType = HttpResponsetypes::STR_TYPE_HTML;
    }

    /**
     * @return ResponseObject
     */
    public static function getInstance() {
        if(self::$objInstance == null) {
            self::$objInstance = new ResponseObject();
        }

        return self::$objInstance;
    }


    /**
     *
     */
    public function sendHeaders() {
        if($this->strRedirectUrl != "") {
            $this->strStatusCode = HttpStatuscodes::SC_REDIRECT;
            $this->arrAdditionalHeaders[] = "Location: ".uniStrReplace("&amp;", "&", $this->strRedirectUrl);
        }

        header($this->getStrStatusCode());
        header($this->getStrResponseType());

        foreach($this->arrAdditionalHeaders as $strOneHeader) {
            header($strOneHeader);
        }
    }


    public function sendContent() {

        ignore_user_abort(true);
        if(trim($this->strContent) != "") {
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

        if(!Session::getInstance()->getBitClosed())
            Session::getInstance()->sessionClose();
    }


    /**
     * @param $strHeader
     */
    public function addHeader($strHeader) {
        $this->arrAdditionalHeaders[] = $strHeader;
    }

    /**
     * @param $strStatusCode
     */
    public function setStrStatusCode($strStatusCode) {
        $this->strStatusCode = $strStatusCode;
    }

    /**
     * @return string
     */
    public function getStrStatusCode() {
        return $this->strStatusCode;
    }

    /**
     * @param $stResponseType
     * @deprecated use setStrResponseType instead
     */
    public function setStResponseType($stResponseType) {
        $this->strResponseType = $stResponseType;
    }

    /**
     * @return string
     * @deprecated use getStrResponseType instead
     */
    public function getStResponseType() {
        return $this->strResponseType;
    }

    /**
     * @param $stResponseType
     */
    public function setStrResponseType($stResponseType) {
        $this->strResponseType = $stResponseType;
    }

    /**
     * @return string
     */
    public function getStrResponseType() {
        return $this->strResponseType;
    }

    /**
     * @param $strContent
     */
    public function setStrContent($strContent) {
        $this->strContent = $strContent;
    }

    /**
     * @return string
     */
    public function getStrContent() {
        return $this->strContent;
    }

    /**
     * @param $strRedirectUrl
     */
    public function setStrRedirectUrl($strRedirectUrl) {
        $this->strRedirectUrl = $strRedirectUrl;
    }

    /**
     * @return string
     */
    public function getStrRedirectUrl() {
        return $this->strRedirectUrl;
    }

}
