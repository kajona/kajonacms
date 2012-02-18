<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * Imports a single zip-archive based templatepack into the system.
 *
 * @package module_templatemanager
 *
 * @todo a lot of that functionality should be moved to a "real" package-manager
 */
class class_systemtask_templatepackimport extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();

        $this->setStrTextBase("templatemanager");
        $this->setBitMultipartform(true);
    }

    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "templatepackimport";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_templatepackimport_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {
        if($this->getParam("import_error") != "")
            return $this->getLang("import_uploaderror");

        $strReturn = "";
        $strFilename = $this->getParam("import_file");
        $strPackname = uniSubstr(basename($strFilename), 0, -4);

        if(is_file(_realpath_.$strFilename)) {
            if($this->getParam("step") == "") {
                $this->setStrReloadParam("&step=metadata&import_file=".$strFilename);
                $this->setStrProgressInformation($this->getLang("import_step_metadata"));
                return 10;
            }

            //check the metadata
            if($this->getParam("step") == "metadata") {
                $objZip = new class_zip();

                $strMetadata = $objZip->getFileFromArchive($strFilename, $strPackname."/metadata.xml");

                if($strMetadata === false) {
                    $this->setStrProgressInformation($this->getLang("import_error_metadata"));
                    return 100;
                }

                $objXml = new class_xml_parser();
                $objXml->loadString($strMetadata);
                $arrXml = $objXml->xmlToArray();

                if(
                    !isset($arrXml["templatepack"]["0"]["name"]["0"]["value"]) ||
                    !isset($arrXml["templatepack"]["0"]["author"]["0"]["value"]) ||
                    !isset($arrXml["templatepack"]["0"]["version"]["0"]["value"]) ||
                    !isset($arrXml["templatepack"]["0"]["licence"]["0"]["value"])
                ) {
                    $this->setStrProgressInformation($this->getLang("import_error_metadatacontent"));
                    return 100;
                }

                $this->setStrReloadParam("&step=extract&import_file=".$strFilename);
                $this->setStrProgressInformation($this->getLang("import_step_filecheck"));
                return 20;
            }


            if($this->getParam("step") == "extract") {
                $objZip = new class_zip();
                if($objZip->extractArchive($strFilename, _templatepath_))
                    $strReturn .= $this->getLang("import_finished");
                else
                    $strReturn .= $this->getLang("import_failed");

            }

        }
        else
            $strReturn = $this->getLang("import_uploaderror");


        //delete the original pack
        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete($strFilename);

        return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
    	$strReturn = "";

        $strReturn .= $this->objToolkit->formTextRow($this->getLang("import_hint"));
        $strReturn .= $this->objToolkit->formInputUpload("pack_filename", $this->getLang("import_filename"));

        return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getSubmitParams()
     * @return string
     */
    public function getSubmitParams() {
        $arrFile = $this->getParam("pack_filename");
        $strError = "";

        //upload to the current temp-dir
        $objFilesystem = new class_filesystem();
        $strTarget = "/project/temp/".createFilename(basename($arrFile["name"]));

        $strSuffix = uniStrtolower( uniSubstr($arrFile["name"], uniStrrpos($arrFile["name"], ".") ) );
        if($strSuffix == ".zip") {
            if($objFilesystem->copyUpload($strTarget, $arrFile["tmp_name"])) {
                class_logger::getInstance()->addLogRow("uploaded file ".$strTarget, class_logger::$levelInfo);
            }
            else
                $strError = "upload";
        }
        else
            $strError = "suffix";


        return "&import_file=".$strTarget."&import_error=".$strError;
    }
}
