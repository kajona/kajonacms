<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Navigation\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\FilterBase;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\OrmComparatorEnum;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmPropertyCondition;
use Kajona\System\System\SystemModule;

/**
 * Model for a navigation tree itself
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 * @targetTable navigation.navigation_id
 *
 * @module navigation
 * @moduleId _navigation_modul_id_
 */
class NavigationTree extends Model implements ModelInterface, AdminListableInterface
{

    /**
     * @var string
     * @tableColumn navigation.navigation_name
     * @listOrder
     * @fieldMandatory
     * @fieldLabel commons_title
     *
     * @addSearchIndex
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn navigation.navigation_folder_i
     */
    private $strFolderId = "";


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_treeRoot";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return "";
    }

    /**
     * Returns an array of all navigation-trees available
     *
     * @param string $strPrevid
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return NavigationTree[]
     * @static
     */
    public static function getObjectListFiltered(FilterBase $objFilter = null, $strPrevid = "", $intStart = null, $intEnd = null)
    {
        return parent::getObjectListFiltered(null, SystemModule::getModuleIdByNr(_navigation_modul_id_), $intStart, $intEnd);
    }

    public static function getObjectCountFiltered(FilterBase $objFilter = null, $strPrevid = "")
    {
        return parent::getObjectCountFiltered(null, SystemModule::getModuleIdByNr(_navigation_modul_id_));
    }


    /**
     * Looks up a navigation by its name
     *
     * @param string $strName
     *
     * @return NavigationTree
     * @static
     */
    public static function getNavigationByName($strName)
    {
        $objOrm = new OrmObjectlist();
        $objOrm->addWhereRestriction(new OrmPropertyCondition("strName", OrmComparatorEnum::Equal(), $strName));
        $arrRows = $objOrm->getObjectList("Kajona\\Navigation\\System\\NavigationTree", SystemModule::getModuleIdByNr(_navigation_modul_id_));
        if (count($arrRows) == 1) {
            return $arrRows[0];
        }

        return null;

    }

    /**
     * Loads al nodes of a navigation, skipping inactive and non-viewable ones.
     * Includes transformed page-nodes!
     *
     * @return array
     */
    public function getCompleteNaviStructure()
    {
        $arrReturn = array();
        $arrReturn["node"] = null;
        $arrReturn["subnodes"] = $this->loadSingleLevel($this->getSystemid());
        return $arrReturn;
    }

    /**
     * Loads a singe level of nodes, internal recursion helper
     *
     * @param string $strParentNode
     *
     * @return array
     */
    private function loadSingleLevel($strParentNode)
    {
        $arrReturn = array();

        $arrCurLevel = NavigationPoint::getDynamicNaviLayer($strParentNode);

        if (isset($arrCurLevel["node"]) && isset($arrCurLevel["subnodes"])) {
            //switch between added nodes and "real" nodes
            $arrTemp = array();
            $arrTemp["node"] = $arrCurLevel["node"];
            $arrTemp["subnodes"] = $arrCurLevel["subnodes"];

            $arrReturn[] = $arrCurLevel;

        }

        /** @var NavigationPoint $objOneNode */
        foreach ($arrCurLevel as $strKey => $objOneNode) {

            if ($strKey !== "node" && $strKey !== "subnodes") {

                if ($objOneNode->getIntRecordStatus() == 1 && $objOneNode->rightView()) {
                    $arrTemp = array();
                    $arrTemp["node"] = $objOneNode;
                    $arrTemp["subnodes"] = $this->loadSingleLevel($objOneNode->getSystemid());

                    $arrReturn[] = $arrTemp;
                }
            }
        }

        return $arrReturn;
    }


    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    /**
     * @param string $strName
     *
     * @return void
     */
    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @return string
     */
    public function getStrFolderId()
    {
        return $this->strFolderId;
    }

    /**
     * @param string $strFolderId
     *
     * @return void
     */
    public function setStrFolderId($strFolderId)
    {
        $this->strFolderId = $strFolderId;
    }

}
