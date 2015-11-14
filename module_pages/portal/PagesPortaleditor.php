<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal;
use class_carrier;
use class_model;
use class_module_system_setting;
use class_objectfactory;
use Kajona\Pages\System\PagesPortaleditorActionAbstract;
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
        $arrReturn = array("systemid" => array(), "placeholder" => array());
        foreach($this->arrActions as $objOneAction) {

            if($objOneAction instanceof PagesPortaleditorSystemidAction) {
                $arrReturn["systemid"][$objOneAction->getStrSystemid()][] = array("type" => $objOneAction->getObjAction()."", "link" => $objOneAction->getStrLink());
            }

            if($objOneAction instanceof PagesPortaleditorPlaceholderAction) {
                $arrReturn["placeholder"][$objOneAction->getStrPlaceholder()][] = array("type" => $objOneAction->getObjAction()."", "link" => $objOneAction->getStrLink(), "element" => $objOneAction->getStrElement());
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

        /** @var class_model $objInstance */
        $objInstance = class_objectfactory::getInstance()->getObject($strSystemid);
        if ($objInstance == null || class_module_system_setting::getConfigValue("_pages_portaleditor_") != "true") {
            return $strOutput;
        }

        if (!class_carrier::getInstance()->getObjSession()->isAdmin() || !$objInstance->rightEdit() || class_carrier::getInstance()->getObjSession()->getSession("pe_disable") == "true") {
            return $strOutput;
        }

        return "<div class='peElementWrapper' data-systemid='{$strSystemid}' data-element='{$strElement}'>{$strOutput}</div>";
    }

    /**
     * Adds the code to render a placeholder-fragment for the portal-editor
     * @param $strPlaceholder
     *
     * @return string
     */
    public static function getPlaceholderWrapper($strPlaceholder)
    {
        return "<span data-placeholder='{$strPlaceholder}'></span>";
    }

    public static function isActive()
    {
        return class_module_system_setting::getConfigValue("_pages_portaleditor_") == "true"
            && class_carrier::getInstance()->getObjSession()->getSession("pe_disable") != "true"
            && class_carrier::getInstance()->getObjSession()->isAdmin();
    }
}
