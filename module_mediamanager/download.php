<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Mediamanager;

use Kajona\Mediamanager\System\MediamanagerFile;
use Kajona\Mediamanager\System\MediamanagerLogbook;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\Filesystem;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Root;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\SystemSetting;

/**
 * Used to send a file to the user
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class DownloadManager extends Root
{

    /**
     * Constructor
     *
     * @param string $strSystemid
     */
    public function __construct($strSystemid)
    {
        parent::__construct($strSystemid);

        //Increase max execution time
        if (@ini_get("max_execution_time") < 7200 && @ini_get("max_execution_time") > 0) {
            @ini_set("max_execution_time", "7200");
        }
    }

    /**
     * Sends the requested file to the browser
     *
     * @return string
     */
    public function actionDownload()
    {
        //Load filedetails

        if (validateSystemid($this->getSystemid())) {

            /** @var $objFile MediamanagerFile */
            $objFile = Objectfactory::getInstance()->getObject($this->getSystemid());
            //Succeeded?
            if ($objFile instanceof MediamanagerFile && $objFile->getIntRecordStatus() == "1" && $objFile->getIntType() == MediamanagerFile::$INT_TYPE_FILE) {
                //Check rights
                if ($objFile->rightRight2()) {
                    //Log the download
                    MediamanagerLogbook::generateDlLog($objFile);
                    $objFilesystem = new Filesystem();
                    $objFilesystem->streamFile($objFile->getStrFilename());
                    return "";

                }
                else {
                    ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
                }

            }
            else {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_NOT_FOUND);
            }

        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_NOT_FOUND);
        }

        //if we reach up here, something gone wrong :/
        ResponseObject::getInstance()->setStrRedirectUrl(str_replace(array("_indexpath_", "&amp;"), array(_indexpath_, "&"), Link::getLinkPortalHref(SystemSetting::getConfigValue("_pages_errorpage_"))));
        ResponseObject::getInstance()->sendHeaders();
        ResponseObject::getInstance()->sendContent();
        return "";
    }
}


//Create a object
$objDownload = new DownloadManager(getGet("systemid"));
$objDownload->actionDownload();
CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array(RequestEntrypointEnum::DOWNLOAD()));

