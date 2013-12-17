<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                 *
********************************************************************************************************/


/**
 * Used to send a file to the user
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_download_manager extends class_root {

    /**
     * Constructor
     *
     * @param $strSystemid
     */
    public function __construct($strSystemid) {
        parent::__construct($strSystemid);

        //Increase max execution time
        @ini_set("max_execution_time", "7200");
    }

    /**
     * Sends the requested file to the browser

     */
    public function actionDownload() {
        //Load filedetails

        if(validateSystemid($this->getSystemid())) {

            /** @var $objFile class_module_mediamanager_file */
            $objFile = class_objectfactory::getInstance()->getObject($this->getSystemid());
            //Succeeded?
            if($objFile instanceof class_module_mediamanager_file && $objFile->getIntRecordStatus() == "1" && $objFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE) {
                //Check rights
                if($objFile->rightRight2()) {
                    //Log the download
                    class_module_mediamanager_logbook::generateDlLog($objFile);
                    //Send the data to the browser
                    $strBrowser = getServer("HTTP_USER_AGENT");
                    //Check the current browsertype
                    if(uniStrpos($strBrowser, "IE") !== false) {
                        //Internet Explorer
                        class_response_object::getInstance()->addHeader("Content-type: application/x-ms-download");
                        class_response_object::getInstance()->addHeader("Content-type: x-type/subtype\n");
                        class_response_object::getInstance()->addHeader("Content-type: application/force-download");
                        class_response_object::getInstance()->addHeader(
                            "Content-Disposition: attachment; filename=" . preg_replace(
                                '/\./', '%2e',
                                saveUrlEncode(trim(basename($objFile->getStrFilename()))), substr_count(basename($objFile->getStrFilename()), '.') - 1
                            )
                        );
                    }
                    else {
                        //Good: another browser vendor
                        class_response_object::getInstance()->addHeader("Content-Type: application/octet-stream");
                        class_response_object::getInstance()->addHeader("Content-Disposition: attachment; filename=" . saveUrlEncode(trim(basename($objFile->getStrFilename()))));
                    }
                    //Common headers
                    class_response_object::getInstance()->addHeader("Expires: Mon, 01 Jan 1995 00:00:00 GMT");
                    class_response_object::getInstance()->addHeader("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                    class_response_object::getInstance()->addHeader("Pragma: no-cache");
                    class_response_object::getInstance()->addHeader("Content-description: JustThum-Generated Data\n");
                    class_response_object::getInstance()->addHeader("Content-Length: " . filesize(_realpath_ . $objFile->getStrFilename()));

                    //End Session
                    $this->objSession->sessionClose();
                    class_response_object::getInstance()->sendHeaders();

                    //Loop the file
                    $ptrFile = @fopen(_realpath_ . $objFile->getStrFilename(), 'rb');
                    fpassthru($ptrFile);
                    @fclose($ptrFile);

                    return "";

                }
                else {
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_FORBIDDEN);
                }

            }
            else {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_NOT_FOUND);
            }

        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_NOT_FOUND);
        }

        //if we reach up here, something gone wrong :/
        class_response_object::getInstance()->sendHeaders();
        class_response_object::getInstance()->setStrRedirectUrl(str_replace(array("_indexpath_", "&amp;"), array(_indexpath_, "&"), getLinkPortalHref(_pages_errorpage_)));

        return "";
    }
}


//Create a object
$objDownload = new class_download_manager(getGet("systemid"));
$objDownload->actionDownload();


