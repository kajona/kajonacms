<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Portalupload\Portal\Elements;

use class_carrier;
use class_element_portal;
use class_filesystem;
use class_http_responsetypes;
use class_http_statuscodes;
use class_link;
use class_module_mediamanager_file;
use class_module_mediamanager_repo;
use class_objectfactory;
use class_response_object;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * Portal Element to load the login-form, or a small "status" area, providing an logout link
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementPortaluploadPortal extends ElementPortal implements PortalElementInterface {

    /**
     * @return string
     */
    public function loadData() {
        $strReturn = "";

        if($this->getParam("submitPortaluploadForm") == "1") {
            $strReturn .= $this->doUpload();
        }
        elseif($this->getParam("submitAjaxUpload") == "1") {
            $strReturn .= $this->doAjaxUpload();
        }
        else {
            $strReturn .= $this->uploadForm();
        }


        return $strReturn;
    }


    /**
     * @param string $formErrors
     *
     * @return string
     */
    private function uploadForm($formErrors = "") {
        $strReturn = "";
        //validate the rights
        $objFilemanagerRepo = new class_module_mediamanager_repo($this->arrElementData["char2"]);


        if($objFilemanagerRepo->rightRight1()) {


            $strTemplateID = $this->objTemplate->readTemplate("/module_portalupload/" . $this->arrElementData["char1"], "portalupload_uploadform");

            $strDlFolderId = "";
            if($this->getParam("action") == "mediaFolder") {
                $strDlFolderId = $this->getParam("systemid");
            }

            $arrTemplate = array();
            $arrTemplate["portaluploadDlfolder"] = $strDlFolderId;

            // check if there was an successfull upload before
            if($this->getParam("uploadSuccess") == "1") {
                $arrTemplate["portaluploadSuccess"] = $this->getLang("portaluploadSuccess");
            }

            $arrTemplate["formErrors"] = $formErrors;

            $strAllowedFileRegex = uniStrReplace(array(".", ","), array("", "|"), $objFilemanagerRepo->getStrUploadFilter());

            $arrTemplate["formAction"] = class_link::getLinkPortalHref($this->getPagename(), "", $this->getAction(), "", $strDlFolderId);
            $arrTemplate["maxFileSize"] = \Kajona\System\System\Carrier::getInstance()->getObjConfig()->getPhpMaxUploadSize();
            $arrTemplate["acceptFileTypes"] = $strAllowedFileRegex != "" ? "/(\.|\/)(".$strAllowedFileRegex.")$/i" : "''";
            $arrTemplate["elementId"] = $this->arrElementData["content_id"];
            $arrTemplate["mediamanagerRepoId"] = $objFilemanagerRepo->getSystemid();

            $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

        }
        else {
            $strReturn .= $this->getLang("commons_error_permissions");
        }

        return $strReturn;
    }

    /**
     * Internal upload handler to handle xml uploads.
     * Used as a backend by the jquery upload plugin.
     * Terminates the request.
     * @return string
     */
    private function doAjaxUpload() {
        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);

        $strUpload = $this->doUpload(true);

        if($strUpload === true)
            $strUpload = $this->getLang("portaluploadSuccess");
        else
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_FORBIDDEN);

        $this->flushCompletePagesCache();
        class_response_object::getInstance()->sendHeaders();
        echo json_encode($strUpload);
        die();
    }


    /**
     * Will be kept for legacy compatibility
     *
     * @param bool $bitJsonResponse
     *
     * @return string
     */
    private function doUpload($bitJsonResponse = false) {
        $strReturn = "";

        //prepare the folder to be used as a target-folder for the upload
        $objFilemanagerRepo = new class_module_mediamanager_repo($this->arrElementData["char2"]);
        $objDownloadfolder = null;

        //add a special subfolder?
        $strPath = $objFilemanagerRepo->getStrPath();
        if($this->getParam("portaluploadDlfolder") != "") {
            /** @var $objDownloadfolder class_module_mediamanager_file */
            $objDownloadfolder = class_objectfactory::getInstance()->getObject($this->getParam("portaluploadDlfolder"));

            //check if the folder is within the current repo
            /** @var $objTemp class_module_mediamanager_file */
            $objTemp = $objDownloadfolder;
            while(validateSystemid($objTemp->getSystemid()) && ($objTemp instanceof class_module_mediamanager_file || $objTemp instanceof class_module_mediamanager_repo)) {
                if($objTemp->getSystemid() == $this->arrElementData["char2"]) {
                    $strPath = $objDownloadfolder->getStrFilename();
                    break;
                }
                $objTemp = class_objectfactory::getInstance()->getObject($objTemp->getPrevId());

            }
        }

        //upload the file...
        if($objFilemanagerRepo->rightRight1()) {

            //Handle the fileupload
            $arrSource = $this->getParam("portaluploadFile");

            $strTarget = $strPath . "/" . createFilename($arrSource["name"]);

            $objFilesystem = new class_filesystem();
            if($objFilesystem->isWritable($strPath)) {

                //Check file for correct filters
                $arrAllowed = explode(",", $objFilemanagerRepo->getStrUploadFilter());
                $strSuffix = uniStrtolower(uniSubstr($arrSource["name"], uniStrrpos($arrSource["name"], ".")));
                if($objFilemanagerRepo->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {
                    if($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {

                        //upload was successfull. try to sync the downloads-archive.
                        if($objDownloadfolder != null && $objDownloadfolder instanceof class_module_mediamanager_file)
                            class_module_mediamanager_file::syncRecursive($objDownloadfolder->getSystemid(), $objDownloadfolder->getStrFilename());
                        else
                            $objFilemanagerRepo->syncRepo();

                        $this->flushCompletePagesCache();

                        if($bitJsonResponse)
                            return true;

                        //reload the site to display the new file
                        if(validateSystemid($this->getParam("portaluploadDlfolder")))
                            $this->portalReload(class_link::getLinkPortalHref($this->getPagename(), "", "mediaFolder", "uploadSuccess=1", $this->getParam("portaluploadDlfolder")));
                        else
                            $this->portalReload(class_link::getLinkPortalHref($this->getPagename(), "", "", $this->getAction(), "uploadSuccess=1", $this->getSystemid()));
                    }
                    else {
                        $strReturn .= $this->uploadForm($this->getLang("portaluploadCopyUploadError"));
                    }
                }
                else {
                    @unlink($arrSource["tmp_name"]);

                    $strReturn .= $this->uploadForm($this->getLang("portaluploadFilterError"));
                }
            }
            else {
                $strReturn .= $this->uploadForm($this->getLang("portaluploadNotWritableError"));
            }

        }
        else {
            $strReturn .= $this->getLang("commons_error_permissions");
        }

        return $strReturn;
    }

}
