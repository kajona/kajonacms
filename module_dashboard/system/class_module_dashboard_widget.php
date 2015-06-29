<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Class to represent a single adminwidget
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 *
 * @targetTable dashboard.dashboard_id
 * @module dashboard
 * @moduleId _dashboard_module_id_
 */
class class_module_dashboard_widget extends class_model implements interface_model {

    /**
     * @var string
     * @tableColumn dashboard.dashboard_column
     * @tableColumnDatatype char254
     */
    private $strColumn = "";

    /**
     * @var string
     * @tableColumn dashboard.dashboard_user
     * @tableColumnDatatype char20
     */
    private $strUser = "";

    /**
     * @var string
     * @tableColumn dashboard.dashboard_aspect
     * @tableColumnDatatype char254
     */
    private $strAspect = "";

    /**
     * @var string
     * @tableColumn dashboard.dashboard_class
     * @tableColumnDatatype char254
     */
    private $strClass = "";

    /**
     * @var string
     * @tableColumn dashboard.dashboard_content
     * @tableColumnDatatype text
     * @blockEscaping
     */
    private $strContent = "";


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return "dashboard widget ".$this->getSystemid();
    }


    /**
     * Looks up all widgets available in the filesystem.
     * ATTENTION: returns the class-name representation of a file, NOT the filename itself.
     *
     * @return string[]
     */
    public static function getListOfWidgetsAvailable() {

        return class_resourceloader::getInstance()->getFolderContent("/admin/widgets/", array(".php"), false, function($strFilename) {
            if($strFilename != "interface_adminwidget.php" && $strFilename != "class_adminwidget.php") {
                $strFilename = uniSubstr($strFilename, 0, -4);

                $objReflection = new ReflectionClass($strFilename);
                if(!$objReflection->isAbstract() && $objReflection->implementsInterface("interface_adminwidget"))
                    return true;
            }

            return false;
        },
        function(&$strFilename) {
            $strFilename = uniSubstr($strFilename, 0, -4);
        });
    }


    /**
     * Creates the concrete widget represented by this model-element
     *
     * @return interface_adminwidget|class_adminwidget
     */
    public function getConcreteAdminwidget() {
        /** @var $objWidget interface_adminwidget|class_adminwidget */
        $objWidget = new $this->strClass();
        //Pass the field-values
        $objWidget->setFieldsAsString($this->getStrContent());
        $objWidget->setSystemid($this->getSystemid());
        return $objWidget;
    }




    /**
     * Looks up the widgets placed in a given column and
     * returns a list of instances, reduced for the current user
     *
     * @param string $strColumn
     * @param string $strAspectFilter
     * @param string $strUserId
     *
     * @return array of class_module_dashboard_widget
     */
    public function getWidgetsForColumn($strColumn, $strAspectFilter = "", $strUserId = "") {

        if($strUserId == "")
            $strUserId = $this->objSession->getUserID();

        $objORM = new class_orm_objectlist();
        $objORM->addWhereRestriction(new class_orm_objectlist_restriction("AND dashboard_user = ?", array($strUserId)));
        $objORM->addWhereRestriction(new class_orm_objectlist_restriction("AND dashboard_column = ?", array($strColumn)));
        return $objORM->getObjectList(get_called_class(), self::getWidgetsRootNodeForUser($strUserId, $strAspectFilter));

    }

    /**
     * Searches the root-node for a users' widgets.
     * If not given, the node is created on the fly.
     * Those nodes are required to ensure a proper sort-handling on the system-table
     *
     * @param string $strUserid
     * @param string $strAspectId
     * @return string
     * @static
     */
    public static function getWidgetsRootNodeForUser($strUserid, $strAspectId = "") {

        if($strAspectId == "")
            $strAspectId = class_module_system_aspect::getCurrentAspectId();

        $strQuery = "SELECT system_id
        			  FROM "._dbprefix_."dashboard,
        			  	   "._dbprefix_."system
        			 WHERE dashboard_id = system_id
        			   AND system_prev_id = ?
        			   AND dashboard_user = ?
        			   AND dashboard_aspect = ?
        			   AND dashboard_class = ?
        	     ORDER BY system_sort ASC ";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array(
            class_module_system_module::getModuleByName("dashboard")->getSystemid(),
            $strUserid,
            $strAspectId,
            "root_node"
        ));

        if(!isset($arrRow["system_id"]) || !validateSystemid($arrRow["system_id"])) {
            //Create a new root-node on the fly
            $objWidget = new class_module_dashboard_widget();
            $objWidget->setStrAspect($strAspectId);
            $objWidget->setStrUser($strUserid);
            $objWidget->setStrClass("root_node");
            $objWidget->updateObjectToDb(class_module_system_module::getModuleByName("dashboard")->getSystemid());

            $strReturnId = $objWidget->getSystemid();
        }
        else
            $strReturnId = $arrRow["system_id"];

        return $strReturnId;
    }


    /**
     * Looks up the widgets placed in a given column and
     * returns a list of instances
     * Use with care!
     *
     * @return class_module_dashboard_widget[]
     * @deprecated will be removed as soon as the v3->v4 update sequences will be removed
     */
    public static function getAllWidgets() {

        $arrParams = array();

        $strQuery = "SELECT system_id
        			  FROM "._dbprefix_."dashboard,
        			  	   "._dbprefix_."system
        			 WHERE dashboard_id = system_id
        	     ORDER BY system_sort ASC ";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        if(count($arrRows) > 0) {
            foreach ($arrRows as $arrOneRow) {
                $arrReturn[] = new class_module_dashboard_widget($arrOneRow["system_id"]);
            }

        }
        return $arrReturn;
    }


    /**
     * @param string $strColumn
     * @return void
     */
    public function setStrColumn($strColumn) {
        $this->strColumn = $strColumn;
    }

    /**
     * @param string $strUser
     * @return void
     */
    public function setStrUser($strUser) {
        $this->strUser = $strUser;
    }

    /**
     * @return string
     */
    public function getStrColumn() {
        return $this->strColumn;
    }

    /**
     * @return string
     */
    public function getStrUser() {
        return $this->strUser;
    }

    /**
     * @return string
     */
    public function getStrAspect() {
        return $this->strAspect;
    }

    /**
     * @param string $strAspect
     * @return void
     */
    public function setStrAspect($strAspect) {
        $this->strAspect = $strAspect;
    }

    /**
     * @param string $strClass
     * @return void
     */
    public function setStrClass($strClass) {
        $this->strClass = $strClass;
    }

    /**
     * @return string
     */
    public function getStrClass() {
        return $this->strClass;
    }

    /**
     * @param string $strContent
     * @return void
     */
    public function setStrContent($strContent) {
        $this->strContent = $strContent;
    }

    /**
     * @return string
     */
    public function getStrContent() {
        return $this->strContent;
    }


}


