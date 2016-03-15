<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\Mediamanager\Admin;

use Kajona\Mediamanager\System\MediamanagerFile;
use Kajona\Mediamanager\System\MediamanagerRepo;
use Kajona\System\Admin\AdminController;
use Kajona\System\Admin\XmlAdminInterface;
use Kajona\System\System\Filesystem;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\Image2;
use Kajona\System\System\Imageplugins\ImageCrop;
use Kajona\System\System\Imageplugins\ImageRotate;
use Kajona\System\System\Logger;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\ResponseObject;

/**
 * admin-class of the mediamanager-module
 * Serves xml-requests, e.g. syncing a gallery
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 */
class MediamanagerAdminXml extends AdminController implements XmlAdminInterface
{

    /**
     * Create a new folder using the combination of passed folder & systemid
     *
     * @return string
     * @permissions edit
     */
    protected function actionCreateFolder()
    {
        $strReturn = "";

        /** @var MediamanagerRepo|MediamanagerFile $objInstance */
        $objInstance = Objectfactory::getInstance()->getObject($this->getSystemid());

        if ($objInstance->rightEdit()) {

            if ($objInstance instanceof MediamanagerFile && $objInstance->getIntType() == MediamanagerFile::$INT_TYPE_FOLDER) {
                $strPrevPath = $objInstance->getStrFilename();
            }

            elseif ($objInstance instanceof MediamanagerRepo) {
                $strPrevPath = $objInstance->getStrPath();
            }

            else {
                return "";
            }

            //create repo-instance
            $strFolder = $this->getParam("folder");

            //Create the folder
            $strFolder = createFilename($strFolder, true);
            //folder already existing?
            if (!is_dir(_realpath_."/".$strPrevPath."/".$strFolder)) {

                Logger::getInstance()->addLogRow("creating folder ".$strPrevPath."/".$strFolder, Logger::$levelInfo);

                $objFilesystem = new Filesystem();
                if ($objFilesystem->folderCreate($strPrevPath."/".$strFolder)) {
                    $strReturn = "<message>".xmlSafeString($this->getLang("folder_create_success"))."</message>";
                }
                else {
                    ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_INTERNAL_SERVER_ERROR);
                    $strReturn = "<message><error>".xmlSafeString($this->getLang("folder_create_error"))."</error></message>";
                }
            }
            else {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_INTERNAL_SERVER_ERROR);
                $strReturn = "<message><error>".xmlSafeString($this->getLang("folder_create_error"))."</error></message>";
            }
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }


    /**
     * Tries to save the passed file.
     * Therefore, the following post-params should be given:
     * action = fileUpload
     * folder = the folder to store the file within
     * systemid = the filemanagers' repo-id
     * inputElement = name of the inputElement
     *
     * @return string
     * @permissions right1
     */
    protected function actionFileupload()
    {
        $strReturn = "";

        /** @var MediamanagerRepo|MediamanagerFile $objFile */
        $objFile = Objectfactory::getInstance()->getObject($this->getSystemid());

        /**
         * @var MediamanagerRepo
         */
        $objRepo = null;

        if ($objFile instanceof MediamanagerFile) {
            $strFolder = $objFile->getStrFilename();
            if (!$objFile->rightEdit() || $objFile->getIntType() != MediamanagerFile::$INT_TYPE_FOLDER) {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
                return $strReturn;
            }

            $objRepo = Objectfactory::getInstance()->getObject($objFile->getPrevId());
            while (!$objRepo instanceof MediamanagerRepo) {
                $objRepo = Objectfactory::getInstance()->getObject($objRepo->getPrevId());
            }
        }
        elseif ($objFile instanceof MediamanagerRepo) {
            $objRepo = $objFile;
            $strFolder = $objFile->getStrPath();
            if (!$objFile->rightEdit()) {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
                return $strReturn;
            }

        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
            return $strReturn;
        }

        //Handle the fileupload
        $arrSource = $this->getParam($this->getParam("inputElement"));


        $bitJsonResponse = $this->getParam("jsonResponse") != "";

        $bitPostData = false;
        if (is_array($arrSource)) {
            $strFilename = $arrSource["name"];
        }
        else {
            $bitPostData = getPostRawData() != "";
            $strFilename = $arrSource;
        }

        $strTarget = $strFolder."/".createFilename($strFilename);
        $objFilesystem = new Filesystem();

        if (!file_exists(_realpath_."/".$strFolder)) {
            $objFilesystem->folderCreate($strFolder, true);
        }

        if ($objFilesystem->isWritable($strFolder)) {

            //Check file for correct filters
            $arrAllowed = explode(",", $objRepo->getStrUploadFilter());

            $strSuffix = uniStrtolower(uniSubstr($strFilename, uniStrrpos($strFilename, ".")));
            if ($objRepo->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {

                if ($bitPostData) {
                    $objFilesystem = new Filesystem();
                    $objFilesystem->openFilePointer($strTarget);
                    $bitCopySuccess = $objFilesystem->writeToFile(getPostRawData());
                    $objFilesystem->closeFilePointer();
                }
                else {
                    $bitCopySuccess = $objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"]);
                }
                if ($bitCopySuccess) {
                    if ($bitJsonResponse) {
                        $strReturn = json_encode(array('success' => true));
                    }
                    else {
                        $strReturn .= "<message>".$this->getLang("xmlupload_success")."</message>";
                    }

                    Logger::getInstance()->addLogRow("uploaded file ".$strTarget, Logger::$levelInfo);

                    $objRepo->syncRepo();
                }
                else {
                    if ($bitJsonResponse) {
                        $strReturn .= json_encode(array('error' => $this->getLang("xmlupload_error_copyUpload")));
                    }
                    else {
                        $strReturn .= "<message><error>".$this->getLang("xmlupload_error_copyUpload")."</error></message>";
                    }
                }
            }
            else {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);

                if ($bitJsonResponse) {
                    $strReturn .= json_encode(array('error' => $this->getLang("xmlupload_error_filter")));
                }
                else {
                    $strReturn .= "<message><error>".$this->getLang("xmlupload_error_filter")."</error></message>";
                }
            }
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_INTERNAL_SERVER_ERROR);

            if ($bitJsonResponse) {
                $strReturn .= json_encode(array('error' => $this->getLang("xmlupload_error_notWritable")));
            }
            else {
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("xmlupload_error_notWritable"))."</error></message>";
            }
        }


        if ($bitJsonResponse) {
            //disabled for ie. otherwise the upload won't work due to the headers.
            ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_HTML);
        }
        @unlink($arrSource["tmp_name"]);
        return $strReturn;
    }

    /**
     * Syncs the repo partially
     *
     * @return string
     * @permissions edit
     */
    protected function actionPartialSyncRepo()
    {
        $strReturn = "";
        $strResult = "";

        /** @var MediamanagerRepo|MediamanagerFile $objInstance */
        $objInstance = Objectfactory::getInstance()->getObject($this->getSystemid());

        if ($objInstance instanceof MediamanagerFile) {
            $arrSyncs = MediamanagerFile::syncRecursive($objInstance->getSystemid(), $objInstance->getStrFilename());
        }

        elseif ($objInstance instanceof MediamanagerRepo) {
            $arrSyncs = $objInstance->syncRepo();
        }

        else {
            return "";
        }


        $strResult .= $this->getLang("sync_end")."<br />";
        $strResult .= $this->getLang("sync_add").$arrSyncs["insert"]."<br />".$this->getLang("sync_del").$arrSyncs["delete"];

        $strReturn .= "<repo>".xmlSafeString(strip_tags($strResult))."</repo>";

        Logger::getInstance()->addLogRow("synced gallery partially >".$this->getSystemid().": ".$strResult, Logger::$levelInfo);

        return $strReturn;
    }

    /**
     * Syncs the repo partially
     *
     * @return string
     * @permissions edit
     */
    protected function actionSyncRepo()
    {
        $strReturn = "";

        /** @var MediamanagerRepo|MediamanagerFile $objInstance */
        $objInstance = Objectfactory::getInstance()->getObject($this->getSystemid());
        //close the session to avoid a blocking behaviour
        $this->objSession->sessionClose();
        if ($objInstance instanceof MediamanagerRepo) {
            $arrSyncs = $objInstance->syncRepo();
        }

        else {
            return "<error>mediamanager repo could not be loaded</error>";
        }

        $strResult = 0;

        $strResult += $arrSyncs["insert"] + $arrSyncs["delete"];
        $strReturn .= "<repo>".xmlSafeString(strip_tags($strResult))."</repo>";

        Logger::getInstance()->addLogRow("synced gallery partially >".$this->getSystemid().": ".$strResult, Logger::$levelInfo);

        return $strReturn;
    }


    /**
     * Tries to rotate the passed imaged.
     * The following params are needed:
     * action = rotateImage
     * folder = the files' location
     * file = the file to crop
     * systemid = the repo-id
     * angle
     *
     * @return string
     * @permissions edit
     */
    protected function actionRotate()
    {
        $strReturn = "";

        $strFile = $this->getParam("file");

        $objImage = new Image2();
        $objImage->setUseCache(false);
        $objImage->load($strFile);
        $objImage->addOperation(new ImageRotate($this->getParam("angle")));
        if ($objImage->save($strFile)) {
            Logger::getInstance()->addLogRow("rotated file ".$strFile, Logger::$levelInfo);
            $strReturn .= "<message>".xmlSafeString($this->getLang("xml_rotate_success"))."</message>";
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * Tries to save the passed cropping.
     * The following params are needed:
     * action = saveCropping
     * folder = the files' location
     * file = the file to crop
     * systemid = the repo-id
     * intX
     * intY
     * intWidth
     * intHeight
     *
     * @return string
     * @permissions edit
     */
    protected function actionSaveCropping()
    {
        $strReturn = "";

        $strFile = $this->getParam("file");

        $objImage = new Image2();
        $objImage->setUseCache(false);
        $objImage->load($strFile);
        $objImage->addOperation(new ImageCrop($this->getParam("intX"), $this->getParam("intY"), $this->getParam("intWidth"), $this->getParam("intHeight")));
        if ($objImage->save($strFile)) {
            Logger::getInstance()->addLogRow("cropped file ".$strFile, Logger::$levelInfo);
            $strReturn .= "<message>".xmlSafeString($this->getLang("xml_cropping_success"))."</message>";
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }


}

