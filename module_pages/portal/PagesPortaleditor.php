<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal;
use class_carrier;
use \Kajona\System\System\Model;
use class_module_system_setting;
use class_objectfactory;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\PagesPortaleditorActionAbstract;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorPlaceholderAction;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;

/**
 * The V5 way of generating the portal-editor. now way more object-oriented then in v4, so a plug n play mechanism
 *
 * @author sidler@mulchprod.de
 *
 * @module pages
 * @moduleId _pages_modul_id_
 */
class PagesPortaleditor  {

    /**
     * @var PagesPortaleditor
     */
    private static $objInstance = null;

    /**
     * @var PagesPortaleditorActionAbstract[]
     */
    private $arrActions = array();

    /**
     * PagesPortaleditor constructor.
     */
    private function __construct()
    {
    }

    /**
     * Converts the portaleditor actions to a json-object
     * @return string
     */
    public function convertToJs() {

        $arrActions = $this->arrActions;
        usort($arrActions, function(PagesPortaleditorActionAbstract $objActionA, PagesPortaleditorActionAbstract $objActionB) {

            if($objActionA->getObjAction()->equals(PagesPortaleditorActionEnum::MOVE()) && !$objActionB->getObjAction()->equals(PagesPortaleditorActionEnum::MOVE())) {
                return -1;
            }

            if(!$objActionA->getObjAction()->equals(PagesPortaleditorActionEnum::MOVE()) && $objActionB->getObjAction()->equals(PagesPortaleditorActionEnum::MOVE())) {
                return 1;
            }

            return strcmp($objActionA->getObjAction(), $objActionB->getObjAction());
        });

        $arrReturn = array("systemIds" => array(), "placeholder" => array());
        foreach($arrActions as $objOneAction) {

            if($objOneAction instanceof PagesPortaleditorSystemidAction) {
                $arrReturn["systemIds"][$objOneAction->getStrSystemid()][] = array("type" => $objOneAction->getObjAction()."", "link" => $objOneAction->getStrLink(), "systemid" => $objOneAction->getStrSystemid());
            }

            if($objOneAction instanceof PagesPortaleditorPlaceholderAction) {
                $arrReturn["placeholder"][$objOneAction->getStrPlaceholder()][] = array("type" => $objOneAction->getObjAction()."", "link" => $objOneAction->getStrLink(), "element" => $objOneAction->getStrElement(), "name" => $objOneAction->getStrElement());
            }
        }
        return json_encode($arrReturn);
    }

    /**
     * @return PagesPortaleditor
     */
    public static function getInstance()
    {
        if(self::$objInstance == null) {
            self::$objInstance = new PagesPortaleditor();
        }

        return self::$objInstance;
    }

    /**
     * Registers an additional action-entry for the current page
     * @param PagesPortaleditorActionAbstract $objAction
     */
    public function registerAction(PagesPortaleditorActionAbstract $objAction)
    {
        $this->arrActions[] = $objAction;
    }


    /**
     * Adds the wrapper for an element rendered by the portal-editor
     * @param $strOutput
     * @param $strSystemid
     * @param $strElement
     *
     * @return string
     */
    public static function addPortaleditorContentWrapper($strOutput, $strSystemid, $strElement = "")
    {

        if (!validateSystemid($strSystemid)) {
            return $strOutput;
        }

        /** @var \Kajona\System\System\Model $objInstance */
        $objInstance = class_objectfactory::getInstance()->getObject($strSystemid);
        if ($objInstance == null || class_module_system_setting::getConfigValue("_pages_portaleditor_") != "true") {
            return $strOutput;
        }

        if (!class_carrier::getInstance()->getObjSession()->isAdmin() || !$objInstance->rightEdit() || class_carrier::getInstance()->getObjSession()->getSession("pe_disable") == "true") {
            return $strOutput;
        }

        //if the parent one is a block, we want to avoid it being a drag n drop entry
        $objParent = class_objectfactory::getInstance()->getObject($objInstance->getStrPrevId());

        $strClass = "peElementWrapper";
        if($objInstance->getIntRecordStatus() == 0) {
            $strClass .= " peInactiveElement";
        }

        if($objParent instanceof PagesPageelement && $objParent->getStrPlaceholder() == "block") {
            $strClass .= " peNoDnd";
        }

        return "<div class='{$strClass}' data-systemid='{$strSystemid}' data-element='{$strElement}' onmouseover='KAJONA.admin.portaleditor.elementActionToolbar.show(this)'  onmouseout='KAJONA.admin.portaleditor.elementActionToolbar.hide(this)'>{$strOutput}</div>";
    }

    /**
     * Adds the code to render a placeholder-fragment for the portal-editor
     * @param $strPlaceholder
     *
     * @return string
     */
    public static function getPlaceholderWrapper($strPlaceholder, $strContent = "")
    {
        return "<div class='pePlaceholderWrapper' data-placeholder='{$strPlaceholder}' data-name='{$strPlaceholder}'>{$strContent}</div>";

        return "<span data-placeholder='{$strPlaceholder}' data-name='{$strPlaceholder}'></span>";
    }

    public static function isActive()
    {
        return class_module_system_setting::getConfigValue("_pages_portaleditor_") == "true"
            && class_carrier::getInstance()->getObjSession()->getSession("pe_disable") != "true"
            && class_carrier::getInstance()->getObjSession()->isAdmin();
    }
}
