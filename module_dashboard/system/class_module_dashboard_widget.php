<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
class class_module_dashboard_widget extends class_model implements interface_model, interface_recorddeleted_listener, interface_userfirstlogin_listener {

    /**
     * @var string
     * @tableColumn dashboard_column
     */
    private $strColumn = "";

    /**
     * @var string
     * @tableColumn dashboard_user
     */
    private $strUser = "";

    /**
     * @var string
     * @tableColumn dashboard_aspect
     */
    private $strAspect = "";

    /**
     * @var string
     * @tableColumn dashboard_class
     */
    private $strClass = "";

    /**
     * @var string
     * @tableColumn dashboard_content
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
    public function getListOfWidgetsAvailable() {
        $arrReturn = array();

        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/admin/widgets/", array(".php"));

        foreach($arrFiles as $strOneFile) {
            if($strOneFile != "interface_adminwidget.php" && $strOneFile != "class_adminwidget.php") {
                $arrReturn[] = uniStrReplace(".php", "", $strOneFile);
            }
        }

        return $arrReturn;
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
     * Implementing callback to react on user-delete events
     *
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param $strSystemid
     * @param string $strSourceClass
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid, $strSourceClass) {
        if($strSourceClass == "class_module_user_user" && validateSystemid($strSystemid)) {
            $strQuery = "SELECT dashboard_id FROM "._dbprefix_."dashboard WHERE dashboard_user = ?";
            $arrRows = $this->objDB->getPArray($strQuery, array($strSystemid), null, null, false);
            foreach($arrRows as $arrOneRow) {
                $objWidget = new class_module_dashboard_widget($arrOneRow["dashboard_id"]);
                $objWidget->deleteObject();
            }
        }

        return true;
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

        $arrParams = array();
        $arrParams[] = $strUserId;
        $arrParams[] = $strColumn;
        $arrParams[] = self::getWidgetsRootNodeForUser($strUserId, $strAspectFilter);

        $strQuery = "SELECT system_id
        			  FROM "._dbprefix_."dashboard,
        			  	   "._dbprefix_."system
        			 WHERE dashboard_user = ?
        			   AND dashboard_column = ?
        			   AND dashboard_id = system_id
        			   AND system_prev_id = ?
                       /*".$strAspectFilter."*/
        	     ORDER BY system_sort ASC ";

        $arrRows = $this->objDB->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        if(count($arrRows) > 0) {
            foreach ($arrRows as $arrOneRow) {
            	$arrReturn[] = new class_module_dashboard_widget($arrOneRow["system_id"]);
            }

        }
        return $arrReturn;
    }

    /**
     * Searches the root-node for a users' widgets.
     * If not given, the node is created on the fly.
     * Those nodes are required to ensure a proper sort-handling on the system-table
     *
     * @static
     *
     * @param $strUserid
     * @param string $strAspectId
     * @return string
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
     * Callback method, triggered each time a user logs into the system for the very first time.
     * May be used to trigger actions or initial setups for the user.
     *
     * @param $strUserid
     *
     * @return bool
     */
    public function handleUserFirstLoginEvent($strUserid) {
        $bitReturn = true;

        //get all widgets and call them in order
        $arrWidgets = $this->getListOfWidgetsAvailable();
        foreach($arrWidgets as $strOneWidgetClass) {
            /** @var $objInstance interface_adminwidget */
            $objInstance = new $strOneWidgetClass();

            $bitReturn = $bitReturn && $objInstance->onFistLogin($strUserid);

        }

        return $bitReturn;
    }


    public function setStrColumn($strColumn) {
        $this->strColumn = $strColumn;
    }
    public function setStrUser($strUser) {
        $this->strUser = $strUser;
    }

    public function getStrColumn() {
        return $this->strColumn;
    }
    public function getStrUser() {
        return $this->strUser;
    }

    public function getStrAspect() {
        return $this->strAspect;
    }

    public function setStrAspect($strAspect) {
        $this->strAspect = $strAspect;
    }

    /**
     * @param string $strClass
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


