<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal;

use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\PagesPortaleditorActionAbstract;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorPlaceholderAction;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\ScriptletHelper;
use Kajona\System\System\ScriptletInterface;
use Kajona\System\System\Session;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemSetting;

/**
 * The V5 way of generating the portal-editor. now way more object-oriented then in v4, so a plug n play mechanism
 *
 * @author sidler@mulchprod.de
 *
 * @module pages
 * @moduleId _pages_modul_id_
 */
class PagesPortaleditor
{

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
     *
     * @return string
     */
    public function convertToJs()
    {

        $arrActions = $this->arrActions;
        usort($arrActions, function (PagesPortaleditorActionAbstract $objActionA, PagesPortaleditorActionAbstract $objActionB) {

            if ($objActionA->getObjAction()->equals(PagesPortaleditorActionEnum::MOVE()) && !$objActionB->getObjAction()->equals(PagesPortaleditorActionEnum::MOVE())) {
                return -1;
            }

            if (!$objActionA->getObjAction()->equals(PagesPortaleditorActionEnum::MOVE()) && $objActionB->getObjAction()->equals(PagesPortaleditorActionEnum::MOVE())) {
                return 1;
            }

            return strcmp($objActionA->getObjAction(), $objActionB->getObjAction());
        });

        $arrReturn = array("systemIds" => array(), "placeholder" => array());
        foreach ($arrActions as $objOneAction) {

            if ($objOneAction instanceof PagesPortaleditorSystemidAction) {
                $arrReturn["systemIds"][$objOneAction->getStrSystemid()][] = array("type" => $objOneAction->getObjAction()."", "link" => $objOneAction->getStrLink(), "systemid" => $objOneAction->getStrSystemid());
            }

            if ($objOneAction instanceof PagesPortaleditorPlaceholderAction) {
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
        if (self::$objInstance == null) {
            self::$objInstance = new PagesPortaleditor();
        }

        return self::$objInstance;
    }

    /**
     * Registers an additional action-entry for the current page
     *
     * @param PagesPortaleditorActionAbstract $objAction
     */
    public function registerAction(PagesPortaleditorActionAbstract $objAction)
    {
        $this->arrActions[] = $objAction;
    }


    /**
     * Adds the wrapper for an element rendered by the portal-editor
     *
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
        $objInstance = Objectfactory::getInstance()->getObject($strSystemid);
        if ($objInstance == null || SystemSetting::getConfigValue("_pages_portaleditor_") != "true") {
            return $strOutput;
        }

        if (!Carrier::getInstance()->getObjSession()->isAdmin() || !$objInstance->rightEdit() || Carrier::getInstance()->getObjSession()->getSession("pe_disable") == "true") {
            return $strOutput;
        }

        //if the parent one is a block, we want to avoid it being a drag n drop entry
        $objParent = Objectfactory::getInstance()->getObject($objInstance->getStrPrevId());

        $strClass = "peElementWrapper";
        if ($objInstance->getIntRecordStatus() == 0) {
            $strClass .= " peInactiveElement";
        }

        if ($objParent instanceof PagesPageelement && $objParent->getStrPlaceholder() == "block") {
            $strClass .= " peNoDnd";
        }

        return "<div class='{$strClass}' data-systemid='{$strSystemid}' data-element='{$strElement}' onmouseover='KAJONA.admin.portaleditor.elementActionToolbar.show(this)'  onmouseout='KAJONA.admin.portaleditor.elementActionToolbar.hide(this)'>{$strOutput}</div>";
    }

    /**
     * Adds the code to render a placeholder-fragment for the portal-editor
     *
     * @param $strPlaceholder
     *
     * @return string
     */
    public static function getPlaceholderWrapper($strPlaceholder, $strContent = "")
    {
        return "<div class='pePlaceholderWrapper' data-placeholder='{$strPlaceholder}' data-name='{$strPlaceholder}'>{$strContent}</div>";
    }

    /**
     * Checks if the portaledtitor is enabled in general
     * @return bool
     */
    public static function isActive()
    {
        return SystemSetting::getConfigValue("_pages_portaleditor_") == "true"
        && Carrier::getInstance()->getObjSession()->getSession("pe_disable") != "true"
        && Carrier::getInstance()->getObjSession()->isAdmin();
    }

    /**
     * Checks if the portaleditor is enabled in general and the current user has edit permissions on the current page
     * @param PagesPage $objPage
     *
     * @return bool
     */
    public static function isActiveOnPage(PagesPage $objPage)
    {
        return self::isActive() && $objPage->rightEdit();
    }


    /**
     * A helper method to inject the portaleditor code fragments in to the
     * current page-output.
     *
     * @param PagesPage $objPageData
     * @param string $strPageContent
     *
     * @return string
     * @todo move this to an external class
     */
    public static function injectPortalEditorPageCode(PagesPage $objPageData, $strPageContent)
    {

        if(!self::isActiveOnPage($objPageData)) {
            return $strPageContent;
        }


        AdminskinHelper::defineSkinWebpath();

        //save back the current portal text language and set the admin-one
        $strPortalLanguage = Carrier::getInstance()->getObjLang()->getStrTextLanguage();
        Carrier::getInstance()->getObjLang()->setStrTextLanguage(Carrier::getInstance()->getObjSession()->getAdminLanguage());

        $strPeToolbar = "";

        $strConfigFile = "'config_kajona_standard.js'";
        if (is_file(_realpath_."/project/admin/scripts/ckeditor/config_kajona_standard.js")) {
            $strConfigFile = "KAJONA_WEBPATH+'/project/admin/scripts/ckeditor/config_kajona_standard.js'";
        }

        //Add an iconbar
        $strPageEditUrl = Link::getLinkAdminHref("pages_content", "list", "&systemid=".$objPageData->getSystemid()."&language=".$strPortalLanguage, false);
        $strEditUrl = Link::getLinkAdminHref("pages", "editPage", "&systemid=".$objPageData->getSystemid()."&language=".$strPortalLanguage."&pe=1");
        $strNewUrl = Link::getLinkAdminHref("pages", "newPage", "&systemid=".$objPageData->getSystemid()."&language=".$strPortalLanguage."&pe=1");

        $strEditDate = timeToString($objPageData->getIntLmTime(), false);
        $strPageStatus = ($objPageData->getIntRecordStatus() == 1 ?
            Carrier::getInstance()->getObjLang()->getLang("systemtask_systemstatus_active", "system") :
            Carrier::getInstance()->getObjLang()->getLang("systemtask_systemstatus_inactive", "system"));

        $bitSetEnabled = Carrier::getInstance()->getObjSession()->getSession("pe_disable") != "true" ? 'false' : 'true';

        $strPeToolbar .= "<script type='text/javascript'>

        KAJONA.portal.loader.loadFile([
            '/core/module_pages/admin/scripts/kajona_portaleditor.js',
            '/core/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js',
            '/core/module_system/system/scripts/lang.js',
            '/core/module_system/admin/scripts/jqueryui/css/smoothness/jquery-ui.custom.css'
        ], function() {

            KAJONA.admin.peToolbarActions = [
                new KAJONA.admin.portaleditor.toolbar.Action('pages:pe_on_off', '', 'fa-power-off', function() { KAJONA.admin.portaleditor.switchEnabled({$bitSetEnabled}); }),
                new KAJONA.admin.portaleditor.toolbar.Separator(),
                new KAJONA.admin.portaleditor.toolbar.Action('pages:pe_status_page', '{$objPageData->getStrName()}','fa-file-o','' ),
                new KAJONA.admin.portaleditor.toolbar.Action('pages:pe_status_status','{$strPageStatus}','fa-eye',''),
                new KAJONA.admin.portaleditor.toolbar.Action('pages:pe_status_autor','{$objPageData->getLastEditUser()}','fa-user',''),
                new KAJONA.admin.portaleditor.toolbar.Action('pages:pe_status_time','{$strEditDate}','fa-clock-o',''),
                new KAJONA.admin.portaleditor.toolbar.Separator(),
                new KAJONA.admin.portaleditor.toolbar.Action('pages:pe_icon_page','','fa-pencil',function() { KAJONA.admin.portaleditor.openDialog('{$strEditUrl}'); return false;}),
                new KAJONA.admin.portaleditor.toolbar.Action('pages:pe_icon_new','','fa-plus-circle',function() { KAJONA.admin.portaleditor.openDialog('{$strNewUrl}'); return false;}),
                new KAJONA.admin.portaleditor.toolbar.Action('pages:pe_icon_edit','','fa-file-o',function() { document.location = '{$strPageEditUrl}';}),
                new KAJONA.admin.portaleditor.toolbar.Separator()
            ];

            KAJONA.admin.actions = ".PagesPortaleditor::getInstance()->convertToJs().";
            KAJONA.portal.loader.loadFile([
                '/core/module_v4skin/admin/skins/kajona_v4/js/bootstrap.min.js',
                '/core/module_v4skin/admin/skins/kajona_v4/js/kajona_dialog.js'
            ], function() {
                KAJONA.admin.portaleditor.peDialog = new KAJONA.admin.ModalDialog('peDialog', 0, true, true);
                KAJONA.admin.portaleditor.delDialog = new KAJONA.admin.ModalDialog('delDialog', 1, false, false);
            });

            KAJONA.admin.portaleditor.RTE.config = {
                language : '".(Session::getInstance()->getAdminLanguage() != "" ? Session::getInstance()->getAdminLanguage() : "en")."',
                filebrowserBrowseUrl : '".StringUtil::replace("&amp;", "&", Link::getLinkAdminHref("folderview", "browserChooser", "&form_element=ckeditor"))."',
                filebrowserImageBrowseUrl : '".StringUtil::replace("&amp;", "&", Link::getLinkAdminHref("mediamanager", "folderContentFolderviewMode", "systemid=".SystemSetting::getConfigValue("_mediamanager_default_imagesrepoid_")."&form_element=ckeditor&bit_link=1"))."',
                customConfig : {$strConfigFile},
                resize_minWidth : 640,
                filebrowserWindowWidth : 400,
                filebrowserWindowHeight : 500,
                filebrowserImageWindowWidth : 400,
                filebrowserImageWindowWindowHeight : 500
            };

            $(function() {
                KAJONA.admin.portaleditor.initPortaleditor();
                KAJONA.admin.portaleditor.elementActionToolbar.init();
                KAJONA.admin.portaleditor.globalToolbar.init();
                KAJONA.admin.tooltip.initTooltip();
                KAJONA.util.lang.initializeProperties();
            });
        });
        </script>";


        //Load portaleditor styles
        $strPeToolbar .= Carrier::getInstance()->getObjToolkit("portal")->getPeToolbar();

        $objScriptlets = new ScriptletHelper();
        $strPeToolbar = $objScriptlets->processString($strPeToolbar, ScriptletInterface::BIT_CONTEXT_ADMIN);

        //The toolbar has to be added right after the body-tag - to generate correct html-code
        $strTemp = StringUtil::substring($strPageContent, StringUtil::indexOf($strPageContent, "<body"));
        //find closing bracket
        $intTemp = StringUtil::indexOf($strTemp, ">") + 1;
        //and insert the code
        $strPageContent = StringUtil::substring($strPageContent, 0, StringUtil::indexOf($strPageContent, "<body") + $intTemp).$strPeToolbar.StringUtil::substring($strPageContent, StringUtil::indexOf($strPageContent, "<body") + $intTemp);


        //reset the portal texts language
        Carrier::getInstance()->getObjLang()->setStrTextLanguage($strPortalLanguage);


        return $strPageContent;
    }
}
