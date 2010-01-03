<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                          *
********************************************************************************************************/

/**
 * Resizes and compresses all uploaded pictures in /portal/pics/upload to save disk space
 *
 * @package modul_system
 */
class class_systemtask_compresspicuploads extends class_systemtask_base implements interface_admin_systemtask {

    //class vars
    private $strPicsPath = "/portal/pics/upload";
    private $intMaxWidth = 1024;
    private $intMaxHeight = 1024;

    private $intFilesTotal = 0;
    private $intFilesProcessed = 0;

	/**
	 * contructor to call the base constructor
	 */
	public function __construct() {
		parent::__construct();

		//Increase max execution time
        @ini_set("max_execution_time", "3600");
    }

    
    /**
     * @see interface_admin_systemtast::getGroupIdenitfier()
     * @return string 
     */
    public function getGroupIdentifier() {
        return "";
    }
    

    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
    	return "compresspicuploads";
    }

    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getText("systemtask_compresspicuploads_name");
    }

    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
    	$strReturn = "";

    	$this->intMaxWidth = (int)$this->getParam("intMaxWidth");
    	$this->intMaxHeight = (int)$this->getParam("intMaxHeight");

    	$this->recursiveImageProcessing($this->strPicsPath);

    	//build the return string
    	$strReturn .= $this->getText("systemtask_compresspicuploads_done")."<br />";
    	$strReturn .= $this->getText("systemtask_compresspicuploads_found").": ".$this->intFilesTotal."<br />";
    	$strReturn .= $this->getText("systemtask_compresspicuploads_processed").": ".$this->intFilesProcessed;
    	return $strReturn;
    }

    private function recursiveImageProcessing($strPath) {
        $objFilesystem = new class_filesystem();

        $arrFilesFolders = $objFilesystem->getCompleteList($strPath, array(".jpg", ".jpeg", ".png", ".gif"), array(), array(".", "..", ".svn"));
        $this->intFilesTotal += $arrFilesFolders["nrFiles"];

        foreach ($arrFilesFolders["folders"] as $strOneFolder) {
            $this->recursiveImageProcessing($strPath."/".$strOneFolder);
        }

        foreach ($arrFilesFolders["files"] as $arrOneFile) {
            $strImagePath = $strPath."/".$arrOneFile["filename"];

            $objImage = new class_image();
            $objImage->preLoadImage($strImagePath);

            if ($objImage->getIntWidth() > $this->intMaxWidth || $objImage->getIntHeight() > $this->intMaxHeight) {
                $bitResize = true;
                $intWidthNew = 0;
                $intHeightNew = 0;

                $floatRelation = $objImage->getIntWidth() / $objImage->getIntHeight();

                //chose more restricitve values
                $intHeightNew = $this->intMaxHeight;
                $intWidthNew = $this->intMaxHeight * $floatRelation;

                if($this->intMaxHeight == 0) {
                    if($this->intMaxWidth < $objImage->getIntWidth()) {
                        $intWidthNew = $this->intMaxWidth;
                        $intHeightNew = $intWidthNew / $floatRelation;
                    }
                    else
                        $bitResize = false;
                }
                elseif ($this->intMaxWidth == 0) {
                    if($this->intMaxHeight < $objImage->getIntHeight()) {
                        $intHeightNew = $this->intMaxHeight;
                        $intWidthNew = $intHeightNew * $floatRelation;
                    }
                    else
                        $bitResize = false;
                }
                elseif ($intHeightNew && $intHeightNew > $this->intMaxHeight || $intWidthNew > $this->intMaxWidth) {
                    $intHeightNew = $this->intMaxWidth / $floatRelation;
                    $intWidthNew = $this->intMaxWidth;
                }
                //round to integers
                $intHeightNew = (int)$intHeightNew;
                $intWidthNew = (int)$intWidthNew;
                //avoid 0-sizes
                if($intHeightNew < 1)
                    $intHeightNew = 1;
                if($intWidthNew < 1)
                    $intWidthNew = 1;

                if($bitResize) {
	                $objImage->resizeImage($intWidthNew, $intHeightNew);
	                $objImage->saveImage($strImagePath);
	                $objImage->releaseResources();

	                $this->intFilesProcessed++;
                }
            }
        }
    }

    /**
     * @see interface_admin_systemtast::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
        $strReturn = "";

        //show input fields to choose maximal width and height
        $strReturn .= $this->objToolkit->getTextRow($this->getText("systemtask_compresspicuploads_hint"));
        $strReturn .= $this->objToolkit->divider();
        $strReturn .= $this->objToolkit->formInputText("intMaxWidth", $this->getText("systemtask_compresspicuploads_width"), $this->intMaxWidth);
        $strReturn .= $this->objToolkit->formInputText("intMaxHeight", $this->getText("systemtask_compresspicuploads_height"), $this->intMaxHeight);

        return $strReturn;
    }

    /**
     * @see interface_admin_systemtast::getSubmitParams()
     * @return string
     */
    public function getSubmitParams() {
        return "&intMaxWidth=".$this->getParam('intMaxWidth')."&intMaxHeight=".$this->getParam('intMaxHeight');
    }

}
?>