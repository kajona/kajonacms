<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: download.php 3530 2011-01-06 12:30:26Z sidler $                                                 *
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
	 *
	 */
    public function actionDownload() {
		//Load filedetails

		$bitRedirectToErrorPage = false;

		if(validateSystemid($this->getSystemid())) {

            /** @var $objFile class_module_mediamanager_file */
		    $objFile = class_objectfactory::getInstance()->getObject($this->getSystemid());
			//Succeeded?
			if($objFile instanceof class_module_mediamanager_file && $objFile->getStatus() == "1" && $objFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE) {
				//Check rights
				if($objFile->rightRight2) {
				    //Log the download
					class_module_mediamanager_logbook::generateDlLog($objFile);
					//Send the data to the browser
					$strBrowser = getServer("HTTP_USER_AGENT");
					//Check the current browsertype
					if(uniStrpos($strBrowser, "IE") !== false) {
						//Internet Explorer
						header("Content-type: application/x-ms-download");
		        		header("Content-type: x-type/subtype\n");
						header("Content-type: application/force-download");
						header("Content-Disposition: attachment; filename=".preg_replace(
                            '/\./', '%2e',
                            saveUrlEncode(trim(basename($objFile->getStrFilename()))), substr_count(basename($objFile->getStrFilename()), '.') - 1
                        ));
					}
					else {
						//Good: another browser vendor
						header("Content-Type: application/octet-stream");
						header("Content-Disposition: attachment; filename=".saveUrlEncode(trim(basename($objFile->getStrFilename()))));
					}
					//Common headers
					header("Expires: Mon, 01 Jan 1995 00:00:00 GMT");
					header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
					header("Pragma: no-cache");
					header("Content-description: JustThum-Generated Data\n");
					header("Content-Length: ".filesize(_realpath_.$objFile->getStrFilename()));

					//End Session
					$this->objSession->sessionClose();

					//Loop the file
					$ptrFile = @fopen(_realpath_.$objFile->getStrFilename(), 'rb');
                    fpassthru($ptrFile);
					@fclose($ptrFile);
				}
				else {
                    header(class_http_statuscodes::$strSC_FORBIDDEN);
                    $bitRedirectToErrorPage = true;
				}

			}
			else {
				header(class_http_statuscodes::$strSC_NOT_FOUND);
				$bitRedirectToErrorPage = true;
			}

		}
		else {
            header(class_http_statuscodes::$strSC_NOT_FOUND);
            $bitRedirectToErrorPage = true;
		}

		if($bitRedirectToErrorPage) {
			header("Location: ".str_replace(array("_indexpath_", "&amp;"), array(_indexpath_, "&"), getLinkPortalHref(_pages_errorpage_)));
		}
	}
}


//Create a object
$objDownload = new class_download_manager(getGet("systemid"));
$objDownload->actionDownload();
