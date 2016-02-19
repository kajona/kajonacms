<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\System;

use Kajona\Dashboard\Admin\Widgets\Adminwidget;
use Kajona\Dashboard\Admin\Widgets\AdminwidgetInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistRestriction;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;

/**
 * Class to represent a single adminwidget
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 *
 * @targetTable dashboard.dashboard_id
 * @module dashboard
 * @moduleId _dashboard_module_id_
 *
 * @sortManager class_common_sortmanager
 */
class DashboardWidget extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface {

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
     * @tableColumnIndex
     */
    private $strUser = "";

    /**
     * @var string
     * @tableColumn dashboard.dashboard_aspect
     * @tableColumnDatatype char254
     * @tableColumnIndex
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

        $arrWidgets = Resourceloader::getInstance()->getFolderContent("/admin/widgets", array(".php"));

        $arrReturn = array();
        foreach($arrWidgets as $strPath => $strFilename) {

            $objInstance = Classloader::getInstance()->getInstanceFromFilename($strPath, "Kajona\\Dashboard\\Admin\\Widgets\\Adminwidget", "Kajona\\Dashboard\\Admin\\Widgets\\AdminwidgetInterface");

            if($objInstance !== null) {
                $arrReturn[] = get_class($objInstance);
            }
        }

        return $arrReturn;
    }


    /**
     * Creates the concrete widget represented by this model-element
     *
     * @return AdminwidgetInterface|Adminwidget
     */
    public function getConcreteAdminwidget() {
        /** @var $objWidget AdminwidgetInterface|Adminwidget */
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
     * @return array of DashboardWidget
     */
    public function getWidgetsForColumn($strColumn, $strAspectFilter = "", $strUserId = "") {

        if($strUserId == "")
            $strUserId = $this->objSession->getUserID();

        $objORM = new OrmObjectlist();
        $objORM->addWhereRestriction(new OrmObjectlistRestriction("AND dashboard_user = ?", array($strUserId)));
        $objORM->addWhereRestriction(new OrmObjectlistRestriction("AND dashboard_column = ?", array($strColumn)));
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
            $strAspectId = SystemAspect::getCurrentAspectId();

        $strQuery = "SELECT system_id
        			  FROM "._dbprefix_."dashboard,
        			  	   "._dbprefix_."system
        			 WHERE dashboard_id = system_id
        			   AND system_prev_id = ?
        			   AND dashboard_user = ?
        			   AND dashboard_aspect = ?
        			   AND dashboard_class = ?
        	     ORDER BY system_sort ASC ";

        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array(
            SystemModule::getModuleByName("dashboard")->getSystemid(),
            $strUserid,
            $strAspectId,
            "root_node"
        ));

        if(!isset($arrRow["system_id"]) || !validateSystemid($arrRow["system_id"])) {
            //Create a new root-node on the fly
            $objWidget = new DashboardWidget();
            $objWidget->setStrAspect($strAspectId);
            $objWidget->setStrUser($strUserid);
            $objWidget->setStrClass("root_node");
            $objWidget->updateObjectToDb(SystemModule::getModuleByName("dashboard")->getSystemid());

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
     * @return DashboardWidget[]
     * @deprecated will be removed as soon as the v3->v4 update sequences will be removed
     */
    public static function getAllWidgets() {

        $arrParams = array();

        $strQuery = "SELECT system_id
        			  FROM "._dbprefix_."dashboard,
        			  	   "._dbprefix_."system
        			 WHERE dashboard_id = system_id
        	     ORDER BY system_sort ASC ";

        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        if(count($arrRows) > 0) {
            foreach ($arrRows as $arrOneRow) {
                $arrReturn[] = new DashboardWidget($arrOneRow["system_id"]);
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


