<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                 *
********************************************************************************************************/

if(!require_once("./system/includes.php"))
	die("Error including necessary files");

//The base class
include_once(_realpath_."/system/class_root.php");
include_once(_realpath_."/system/class_http_statuscodes.php");


/**
 * Used to send a file to the user
 *
 * @package modul_downloads
 */
class class_download_portal extends class_root {

    /**
     * Constructor
     *
     */
	public function __construct() {
	    parent::__construct(array(), "", "portal");

	    //needed classes
        include_once(_realpath_."/system/class_modul_downloads_archive.php");
        include_once(_realpath_."/system/class_modul_downloads_file.php");
        include_once(_realpath_."/system/class_modul_downloads_logbook.php");

        //Increase max execution time
        @ini_set("max_execution_time", "7200");

	}

	/**
	 * actionblock
	 *
	 */
	public function action() {
	    if($this->getParam("action") == "")
	       $strAction = "download";
	    else
	       $strAction = $this->getParam("action");

		if($strAction == "download")
			$this->actionDownload();
	}


	/**
	 * Sends the requested file to the browser
	 *
	 */
	private function actionDownload() {
		//Load filedetails
		
		$bitRedirectToErrorPage = false;
		
		if($this->getSystemid() != "" && $this->getSystemid() != "0") {

		    $objFile = new class_modul_downloads_file($this->getSystemid());
			//Succeeded?
			if($objFile->getFilename() != "" && $objFile->getStatus() == "1") {
				//Check rights
				if($this->objRights->rightRight2($objFile->getSystemid())) {
				    //Log the download
					class_modul_downloads_logbook::generateDlLog($objFile);
					//Send the data to the browser
					$strBrowser = getServer("HTTP_USER_AGENT");
					//Check the current browsertype
					if(uniStrpos($strBrowser, "IE") !== false) {
						//Internet Explorer
						header("Content-type: application/x-ms-download");
		        		header("Content-type: x-type/subtype\n");
						header("Content-type: application/force-download");
						header("Content-Disposition: attachment; filename=".preg_replace('/\./', '%2e', saveUrlEncode(trim(basename($objFile->getFilename()))), substr_count(basename($objFile->getFilename()), '.') - 1));
					}
					else {
						//Good: another browser vendor
						header("Content-Type: application/octet-stream");
						header("Content-Disposition: attachment; filename=".saveUrlEncode(trim(basename($objFile->getFilename()))));
					}
					//Common headers
					header("Expires: Mon, 01 Jan 1995 00:00:00 GMT");
					header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
					header("Pragma: no-cache");
					header("Content-description: JustThum-Generated Data\n");
					header("Content-Length: ".filesize(_realpath_.$objFile->getFilename()));

					//End Session
					session_write_close();

					//Loop the file
					$ptrFile = @fopen(_realpath_.$objFile->getFilename(), 'rb');

					//Limited downloarate?
					if($objFile->getMaxKb() == 0 || $objFile->getMaxKb() == "" ) {
						//no limitation, just pass
						fpassthru($ptrFile);
					}
					else {
						//Limitation, send byte by byte
						while(!feof($ptrFile)) {
				   			$buffer = fread($ptrFile, 1024 * (int)$objFile->getMaxKb());
							print $buffer;
							sleep(1);
						}

					}
					@fclose($ptrFile);
				}
				else {
                    header(class_http_status_codes::$strSC_FORBIDDEN);
                    $bitRedirectToErrorPage = true;
				}
				
			}
			else {
				header(class_http_status_codes::$strSC_NOT_FOUND);
				$bitRedirectToErrorPage = true;
			}
			
		}
		else { 
            header(class_http_status_codes::$strSC_NOT_FOUND);
            $bitRedirectToErrorPage = true;
		}
		
		if($bitRedirectToErrorPage) {
			header("Location: ".str_replace(array("_indexpath_", "&amp;"), array(_indexpath_, "&"), getLinkPortalRaw(_pages_errorpage_)));
		}
	}
}


//Create a object
$objDownload = new class_download_portal();
$objDownload->action();
?>