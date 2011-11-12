<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/

/**
 * A workflow handler stores all metadata of a single workflow-handler.
 * This means, this is not the real workflow-instance running, but rather a wrapper to
 * metainfos provided to a single handler, e.g. configuration values.
 *
 *
 * @package modul_workflows
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_modul_workflows_handler extends class_model implements interface_model  {


    private $strHandlerClass = "";
    private $strConfigVal1 = "";
    private $strConfigVal2 = "";
    private $strConfigVal3 = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_workflows";
		$arrModul["moduleId"] 			= _workflows_modul_id_;
		$arrModul["modul"]				= "workflows";
		$arrModul["table"]				= _dbprefix_."workflows_handler";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."workflows_handler" => "workflows_handler_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "workflow handler ".$this->getSystemid();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
         $strQuery = "SELECT * FROM ".$this->arrModule["table"].", 
                                    "._dbprefix_."system
	                   WHERE workflows_handler_id = ?
                         AND system_id= workflows_handler_id";

         $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
         
         $this->setStrHandlerClass($arrRow["workflows_handler_class"]);
         $this->setStrConfigVal1($arrRow["workflows_handler_val1"]);
         $this->setStrConfigVal2($arrRow["workflows_handler_val2"]);
         $this->setStrConfigVal3($arrRow["workflows_handler_val3"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        class_logger::getInstance()->addLogRow("updated workflow handler ".$this->getSystemid(), class_logger::$levelInfo);

        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET workflows_handler_class = ?,
                            workflows_handler_val1 = ?,
                            workflows_handler_val2 = ?,
                            workflows_handler_val3 = ?
                      WHERE workflows_handler_id = ?";
        
        return $this->objDB->_pQuery($strQuery, array(
            $this->getStrHandlerClass(),
            $this->getStrConfigVal1(),
            $this->getStrConfigVal2(),
            $this->getStrConfigVal3(),
            $this->getSystemid()
        ));
    }



    
    /**
     * Deletes a workflow handler from the database
     * @return bool
     */
	public function deleteWorkflowHandler() {

	    class_logger::getInstance()->addLogRow("deleted ".$this->getObjectDescription(), class_logger::$levelInfo);
        $strQuery = "DELETE FROM ".$this->arrModule["table"]." WHERE workflows_handler_id = ? ";

        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
            if($this->deleteSystemRecord($this->getSystemid()))
                return true;
        }
	    return false;
	}


    /**
     * Loads a single handler-class, identified by the mapped class
     *
     * @param string $strClass
     * @return class_modul_workflows_handler
     */
    public static function getHandlerByClass($strClass) {
        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows_handler
                      WHERE system_id = workflows_handler_id
                        AND workflows_handler_class = ?";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strClass));
        if(count($arrRow) > 0)
            return new class_modul_workflows_handler($arrRow["system_id"]);
        else 
            return null;
    }

    /**
     * Retrieves the list of workflow-handlers available
     *
     * @param int $intStart
     * @param int $intEnd
     * @return class_modul_workflows_workflow
     */
    public static function getAllworkflowHandlers($intStart = false, $intEnd = false) {
        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows_handler
                      WHERE system_id = workflows_handler_id
                   ORDER BY workflows_handler_class ASC";
                              

        if($intStart != false && $intEnd != false)
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array(), $intStart, $intEnd);
        else
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_workflows_handler($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Counts the number of workflows handlers available
     * @return int
     */
    public static function getAllworkflowHandlersCount() {
        $strQuery = "SELECT COUNT(*) FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows_handler
                      WHERE system_id = workflows_handler_id";

       $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
       return $arrRow["COUNT(*)"];
    }


    /**
     * Synchronizes the list of handlers available on the filesystem compared to the list
     * of handlers available in the database.
     * Adds or removes handlers from or to the database.
     * 
     */
    public static function synchronizeHandlerList() {
        //load the list of handlers in the filesystem
        $objFilesystem = new class_filesystem();
        $arrFiles = $objFilesystem->getFilelist(_systempath_."/workflows", array(".php"));
        foreach($arrFiles as $strOneFile) {
            $strClassname = uniStrReplace(".php", "", $strOneFile);
            $objWorkflow = class_modul_workflows_handler::getHandlerByClass($strClassname);
            if($objWorkflow == null) {
                $objWorkflow = new class_modul_workflows_handler();
                $objWorkflow->setStrHandlerClass($strClassname);

                $arrDefault = $objWorkflow->getObjInstanceOfHandler()->getDefaultValues();
                if(isset($arrDefault[0]))   $objWorkflow->setStrConfigVal1($arrDefault[0]);
                if(isset($arrDefault[1]))   $objWorkflow->setStrConfigVal2($arrDefault[1]);
                if(isset($arrDefault[2]))   $objWorkflow->setStrConfigVal3($arrDefault[2]);

                $objWorkflow->updateObjectToDb();
            }
        }

        //find workflows to remove
        $arrWorkflows = self::getAllworkflowHandlers();
        foreach($arrWorkflows as $objOneWorkflow) {
            if(!in_array($objOneWorkflow->getStrHandlerClass().".php", $arrFiles))
                $objOneWorkflow->deleteWorkflowHandler();
        }
    }

    /**
     * Creates a non-initialized instance of the concrete handler
     *
     * @return interface_workflows_handler
     */
    public function getObjInstanceOfHandler() {
        $strClassname = uniStrReplace(".php", "", $this->getStrHandlerClass());

        return new $strClassname();
    }


   

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrHandlerClass() {
        return $this->strHandlerClass;
    }

    public function setStrHandlerClass($strHandlerClass) {
        $this->strHandlerClass = $strHandlerClass;
    }

    public function getStrConfigVal1() {
        return $this->strConfigVal1;
    }

    public function setStrConfigVal1($strConfigVal1) {
        $this->strConfigVal1 = $strConfigVal1;
    }

    public function getStrConfigVal2() {
        return $this->strConfigVal2;
    }

    public function setStrConfigVal2($strConfigVal2) {
        $this->strConfigVal2 = $strConfigVal2;
    }

    public function getStrConfigVal3() {
        return $this->strConfigVal3;
    }

    public function setStrConfigVal3($strConfigVal3) {
        $this->strConfigVal3 = $strConfigVal3;
    }






}
?>