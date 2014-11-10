<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/


class class_response_object {

    private $strStatusCode = "";
    private $strResponseType = "";
    private $strRedirectUrl = "";
    private $strContent = "";
    private $arrAdditionalHeaders = array();

    /**
     * @var class_response_object
     */
    private static $objInstance = null;

    /**
     *
     */
    private function __construct() {
        $this->strStatusCode = class_http_statuscodes::SC_OK;
        $this->strResponseType = class_http_responsetypes::STR_TYPE_HTML;
    }

    /**
     * @return class_response_object
     */
    public static function getInstance() {
        if(self::$objInstance == null) {
            self::$objInstance = new class_response_object();
        }

        return self::$objInstance;
    }


    /**
     *
     */
    public function sendHeaders() {
        if($this->strRedirectUrl != "") {
            $this->strStatusCode = class_http_statuscodes::SC_REDIRECT;
            $this->arrAdditionalHeaders[] = "Location: ".uniStrReplace("&amp;", "&", $this->strRedirectUrl);
        }

        header($this->getStrStatusCode());
        header($this->getStrResponseType());

        foreach($this->arrAdditionalHeaders as $strOneHeader) {
            header($strOneHeader);
        }
    }


    public function sendContent() {

        if($this->strContent != "") {
            echo $this->strContent;
            ob_flush();
            flush();
        }

        if(!class_session::getInstance()->getBitClosed())
            class_session::getInstance()->sessionClose();

        class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array());
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
