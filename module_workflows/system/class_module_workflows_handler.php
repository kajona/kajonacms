<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
 * @package module_workflows
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 * @targetTable workflows_handler.workflows_handler_id
 *
 * @module workflows
 * @moduleId _workflows_module_id_
 */
class class_module_workflows_handler extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn workflows_handler.workflows_handler_class
     * @tableColumnDatatype char254
     * @listOrder
     */
    private $strHandlerClass = "";

    /**
     * @var string
     * @tableColumn workflows_handler.workflows_handler_val1
     * @tableColumnDatatype char254
     *
     * @fieldType text
     */
    private $strConfigVal1 = "";

    /**
     * @var string
     * @tableColumn workflows_handler.workflows_handler_val2
     * @tableColumnDatatype char254
     *
     * @fieldType text
     */
    private $strConfigVal2 = "";

    /**
     * @var string
     * @tableColumn workflows_handler.workflows_handler_val3
     * @tableColumnDatatype text
     *
     * @fieldType text
     */
    private $strConfigVal3 = "";


    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_workflow";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        //count the number of instances
        $intCount = 0;
        if($this->getObjInstanceOfHandler() != null) {
            $intCount = class_module_workflows_workflow::getWorkflowsForClassCount(get_class($this->getObjInstanceOfHandler()), false);
        }
        return $this->getLang("handler_instances", "workflows", array($intCount));
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        if($this->getObjInstanceOfHandler() != null)
            return $this->getObjInstanceOfHandler()->getStrName();
        else
            return "";
    }


    /**
     * Loads a single handler-class, identified by the mapped class
     *
     * @param string $strClass
     * @return class_module_workflows_handler
     */
    public static function getHandlerByClass($strClass) {
        $strQuery = "SELECT * FROM
                            "._dbprefix_."workflows_handler,
                            "._dbprefix_."system_right,
                            "._dbprefix_."system
                   LEFT JOIN "._dbprefix_."system_date
                            ON system_id = system_date_id
                      WHERE system_id = workflows_handler_id
                        AND workflows_handler_class = ?
                        AND system_id = right_id";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strClass));
        class_orm_rowcache::addSingleInitRow($arrRow);
        if(count($arrRow) > 0)
            return class_objectfactory::getInstance()->getObject($arrRow["system_id"]);
        else
            return null;
    }

    /**
     * Synchronizes the list of handlers available on the filesystem compared to the list
     * of handlers available in the database.
     * Adds or removes handlers from or to the database.
     *
     */
    public static function synchronizeHandlerList() {
        //load the list of handlers in the filesystem
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/system/workflows", array(".php"));
        foreach($arrFiles as $strPath => $strOneFile) {

            $objInstance = class_classloader::getInstance()->getInstanceFromFilename($strPath, null, "interface_workflows_handler");
            if($objInstance !== null) {
                $objWorkflow = class_module_workflows_handler::getHandlerByClass(get_class($objInstance));

                if($objWorkflow == null) {
                    $objWorkflow = new class_module_workflows_handler();
                    $objWorkflow->setStrHandlerClass(get_class($objInstance));

                    $arrDefault = $objWorkflow->getObjInstanceOfHandler()->getDefaultValues();
                    if(isset($arrDefault[0]))   $objWorkflow->setStrConfigVal1($arrDefault[0]);
                    if(isset($arrDefault[1]))   $objWorkflow->setStrConfigVal2($arrDefault[1]);
                    if(isset($arrDefault[2]))   $objWorkflow->setStrConfigVal3($arrDefault[2]);

                    $objWorkflow->updateObjectToDb();
                }
            }
        }

        //find workflows to remove
        $arrWorkflows = self::getObjectList();
        /** @var class_module_workflows_handler $objOneWorkflow */
        foreach($arrWorkflows as $objOneWorkflow) {

            $strClassname = $objOneWorkflow->getStrHandlerClass();
            if(uniStrrpos($objOneWorkflow->getStrHandlerClass(), "\\") > 0)
                $strClassname = uniSubstr($objOneWorkflow->getStrHandlerClass(), uniStrrpos($objOneWorkflow->getStrHandlerClass(), "\\")+1);

            if(!in_array($strClassname.".php", $arrFiles))
                $objOneWorkflow->deleteObjectFromDatabase();
        }
    }

    /**
     * Creates a non-initialized instance of the concrete handler
     *
     * @return interface_workflows_handler
     */
    public function getObjInstanceOfHandler() {

        $strClassname = $this->getStrHandlerClass();
        if(uniStrrpos($this->getStrHandlerClass(), "\\") > 0)
            $strClassname = uniSubstr($this->getStrHandlerClass(), uniStrrpos($this->getStrHandlerClass(), "\\")+1);

        if($this->getStrHandlerClass() != "" && class_resourceloader::getInstance()->getPathForFile("/system/workflows/".$strClassname.".php") !== false) {
            $strClassname = uniStrReplace(".php", "", $this->getStrHandlerClass());
            $objReflection = new ReflectionClass($strClassname);

            if(!$objReflection->isAbstract()) {
                return new $strClassname();
            }
        }

        return null;
    }



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
