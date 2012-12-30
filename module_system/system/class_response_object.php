<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/


class class_response_object {

    private $strStatusCode = "";
    private $stResponseType = "";
    private $strRedirectUrl = "";
    private $strContent = "";
    private $arrAdditionalHeaders = array();

    /**
     * @var class_response_object
     */
    private static $objInstance = null;

    private function __construct() {
        $this->strStatusCode = class_http_statuscodes::SC_OK;
        $this->stResponseType = class_http_responsetypes::STR_TYPE_HTML;
    }

    public static function getInstance() {
        if(self::$objInstance == null) {
            self::$objInstance = new class_response_object();
        }

        return self::$objInstance;
    }


    public function sendHeaders() {
        if($this->strRedirectUrl != "") {
            $this->strStatusCode = class_http_statuscodes::SC_REDIRECT;
            $this->arrAdditionalHeaders[] = "Location: ".uniStrReplace("&amp;", "&", $this->strRedirectUrl);
        }

        header($this->getStrStatusCode());
        header($this->getStResponseType());

        foreach($this->arrAdditionalHeaders as $strOneHeader) {
            header($strOneHeader);
        }
    }


    public function addHeader($strHeader) {
        $this->arrAdditionalHeaders[] = $strHeader;
    }

    public function setStrStatusCode($strStatusCode) {
        $this->strStatusCode = $strStatusCode;
    }

    public function getStrStatusCode() {
        return $this->strStatusCode;
    }

    public function setStResponseType($stResponseType) {
        $this->stResponseType = $stResponseType;
    }

    public function getStResponseType() {
        return $this->stResponseType;
    }

    public function setStrContent($strContent) {
        $this->strContent = $strContent;
    }

    public function getStrContent() {
        return $this->strContent;
    }

    public function setStrRedirectUrl($strRedirectUrl) {
        $this->strRedirectUrl = $strRedirectUrl;
    }

    public function getStrRedirectUrl() {
        return $this->strRedirectUrl;
    }

}
