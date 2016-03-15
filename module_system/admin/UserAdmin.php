<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\Admin\Formentries\FormentryCheckbox;
use Kajona\System\Admin\Formentries\FormentryDropdown;
use Kajona\System\Admin\Formentries\FormentryHeadline;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\Formentries\FormentryPlaintext;
use Kajona\System\Admin\Formentries\FormentryText;
use Kajona\System\Admin\Formentries\FormentryUser;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;
use Kajona\System\System\Config;
use Kajona\System\System\Cookie;
use Kajona\System\System\Date;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\Mail;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Reflection;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Session;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemPwchangehistory;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\UserGroup;
use Kajona\System\System\UserLog;
use Kajona\System\System\UserSourcefactory;
use Kajona\System\System\Usersources\UsersourcesGroupInterface;
use Kajona\System\System\Usersources\UsersourcesUserInterface;
use Kajona\System\System\UserUser;
use Kajona\System\System\Validators\EmailValidator;


/**
 * This class provides the user and groupmanagement
 *
 * @package module_user
 * @author  sidler@mulchprod.de
 *
 * @module user
 * @moduleId _user_modul_id_
 */
class UserAdmin extends AdminSimple implements AdminInterface
{

    private $STR_USERFILTER_SESSION_KEY = "USERLIST_FILTER_SESSION_KEY";
    private $STR_GROUPFILTER_SESSION_KEY = "GROUPLIST_FILTER_SESSION_KEY";

    //languages, the admin area could display (texts)
    protected $arrLanguages = array();

    /**
     * Constructor
     */
    public function __construct()
    {

        parent::__construct();
        $this->arrLanguages = explode(",", Carrier::getInstance()->getObjConfig()->getConfig("adminlangs"));

        //backwards compatibility
        if ($this->getAction() == "edit") {
            $this->setAction("editUser");
        }

    }

    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("user_liste"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", Link::getLinkAdmin($this->getArrModule("modul"), "groupList", "", $this->getLang("gruppen_liste"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right1", Link::getLinkAdmin($this->getArrModule("modul"), "loginLog", "", $this->getLang("loginlog"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * Renders the form to create a new entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew()
    {
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "newUser"));
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit()
    {
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editUser", "&systemid=".$this->getSystemid()));
    }


    /**
     * Returns a list of current users
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList()
    {

        if ($this->getParam("doFilter") != "") {
            $this->objSession->setSession($this->STR_USERFILTER_SESSION_KEY, $this->getParam("userlist_filter"));
            $this->setParam("pv", 1);
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul")));
            return "";
        }

        $strReturn = "";

        //add a filter-form
        $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "list"));
        $strReturn .= $this->objToolkit->formInputText("userlist_filter", $this->getLang("user_username"), $this->objSession->getSession($this->STR_USERFILTER_SESSION_KEY));
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("userlist_filter"));
        $strReturn .= $this->objToolkit->formInputHidden("doFilter", "1");
        $strReturn .= $this->objToolkit->formClose();

        $objIterator = new ArraySectionIterator(UserUser::getObjectCount($this->objSession->getSession($this->STR_USERFILTER_SESSION_KEY)));
        $objIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objIterator->setArraySection(
            UserUser::getObjectList(
                $this->objSession->getSession($this->STR_USERFILTER_SESSION_KEY),
                $objIterator->calculateStartPos(),
                $objIterator->calculateEndPos()
            )
        );

        $strReturn .= $this->renderList($objIterator, false, "userList");
        return $strReturn;
    }

    /**
     * @param Model $objListEntry
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @return string
     */
    protected function renderStatusAction(Model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        if ($objListEntry instanceof UserUser && $objListEntry->rightEdit()) {
            if ($objListEntry->getIntActive() == 1) {
                return $this->objToolkit->listButton(Link::getLinkAdmin("user", "setUserStatus", "&systemid=".$objListEntry->getSystemid()."&pv=".$this->getParam("pv"), "", $this->getLang("user_active"), "icon_enabled"));
            }
            else {
                return $this->objToolkit->listButton(Link::getLinkAdmin("user", "setUserStatus", "&systemid=".$objListEntry->getSystemid()."&pv=".$this->getParam("pv"), "", $this->getLang("user_inactive"), "icon_disabled"));
            }
        }
        return "";
    }

    /**
     * @param ModelInterface $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(ModelInterface $objListEntry)
    {
        if ($objListEntry instanceof UserUser && $objListEntry->rightDelete()) {

            if ($objListEntry->getSystemid() == Carrier::getInstance()->getObjSession()->getUserID()) {
                return $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_deleteDisabled", $this->getLang("user_loeschen_x")));
            }
            else {
                return $this->objToolkit->listDeleteButton($objListEntry->getStrDisplayName(), $this->getLang("user_loeschen_frage"), Link::getLinkAdminHref($this->getArrModule("modul"), "deleteUser", "&systemid=".$objListEntry->getSystemid()));
            }

        }

        if ($objListEntry instanceof UserGroup) {
            if ($objListEntry->getSystemid() != SystemSetting::getConfigValue("_guests_group_id_") && $objListEntry->getSystemid() != SystemSetting::getConfigValue("_admins_group_id_") && $this->isGroupEditable($objListEntry)) {
                if ($objListEntry->rightDelete()) {
                    return $this->objToolkit->listDeleteButton(
                        $objListEntry->getStrDisplayName(), $this->getLang("gruppe_loeschen_frage"), Link::getLinkAdminHref($this->getArrModule("modul"), "groupDelete", "&systemid=".$objListEntry->getSystemid())
                    );
                }
            }
            else {
                return $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_deleteDisabled", $this->getLang("gruppe_loeschen_x")));
            }
        }
        return "";
    }

    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return array|string
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        if ($strListIdentifier == "userList" && $this->getObjModule()->rightEdit()) {
            return $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "newUser", "", $this->getLang("action_new_user"), $this->getLang("action_new_user"), "icon_new"));
        }

        if ($strListIdentifier == "groupList" && $this->getObjModule()->rightEdit()) {
            return $this->objToolkit->listButton(Link::getLinkAdminDialog($this->getArrModule("modul"), "groupNew", "", $this->getLang("action_group_new"), $this->getLang("action_group_new"), "icon_new"));
        }

        return "";
    }

    /**
     * @param Model $objListEntry
     *
     * @return string
     */
    protected function renderTagAction(Model $objListEntry)
    {
        return "";
    }

    /**
     * @param Model $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(Model $objListEntry)
    {
        $objUsersources = new UserSourcefactory();
        if ($objListEntry instanceof UserUser && $objListEntry->rightEdit()) {
            /* @var UserUser $objListEntry */
            if ($objUsersources->getUsersource($objListEntry->getStrSubsystem())->getCreationOfUsersAllowed()) {
                return $this->objToolkit->listButton(Link::getLinkAdmin("user", "newUser", "&user_inherit_permissions_id=".$objListEntry->getSystemid()."&pv=".$this->getParam("pv")."&usersource=".$objListEntry->getStrSubsystem(), "", $this->getLang("commons_edit_copy", "common"), "icon_copy"));
            }
        }
        return "";
    }

    /**
     * @param Model|UserUser $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(Model $objListEntry)
    {
        $objUsersources = new UserSourcefactory();

        $arrReturn = array();
        if ($objListEntry instanceof UserUser && $objListEntry->rightEdit() && $objUsersources->getUsersource($objListEntry->getStrSubsystem())->getMembersEditable()) {
            $arrReturn[] = $this->objToolkit->listButton(
                Link::getLinkAdminDialog("user", "editMemberships", "&systemid=".$objListEntry->getSystemid()."&folderview=1", "", $this->getLang("user_zugehoerigkeit"), "icon_group", $objListEntry->getStrUsername())
            );
        }
        elseif ($objListEntry instanceof UserUser && $objListEntry->rightEdit()) {
            $arrReturn[] = $this->objToolkit->listButton(
                Link::getLinkAdminDialog("user", "browseMemberships", "&systemid=".$objListEntry->getSystemid()."&folderview=1", "", $this->getLang("user_zugehoerigkeit"), "icon_group", $objListEntry->getStrUsername())
            );
        }


        $objValidator = new EmailValidator();

        if ($objListEntry instanceof UserUser
            && $objListEntry->getObjSourceUser()->isEditable()
            && $objListEntry->getIntActive() == 1
            && $objListEntry->getObjSourceUser()->isPasswordResettable()
            && $objListEntry->rightEdit()
            && $objValidator->validate($objListEntry->getStrEmail())
        ) {
            $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdmin("user", "sendPassword", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("user_password_resend"), "icon_mailNew"));
        }

        if ($objListEntry instanceof UserUser && $objListEntry->getIntActive() == 1) {
            $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdminDialog("messaging", "new", "&messaging_user_id=".$objListEntry->getSystemid(), "", $this->getLang("user_send_message"), "icon_mail", $this->getLang("user_send_message")));
        }

        if ($objListEntry instanceof UserUser && $objListEntry->getIntActive() == 1 && Carrier::getInstance()->getObjSession()->isSuperAdmin()) {
            $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdmin("user", "switchToUser", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("user_switch_to"), "icon_userswitch"));
        }

        if ($objListEntry instanceof UserGroup && $objListEntry->rightEdit()) {
            $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdmin("user", "groupMember", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_group_member"), "icon_group"));
        }

        return $arrReturn;
    }

    /**
     * @param Model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(Model $objListEntry, $bitDialog = false)
    {
        if ($objListEntry instanceof UserGroup) {
            if ($objListEntry->getSystemid() != SystemSetting::getConfigValue("_guests_group_id_") && $objListEntry->getSystemid() != SystemSetting::getConfigValue("_admins_group_id_") && $this->isGroupEditable($objListEntry)) {
                if ($objListEntry->rightEdit()) {
                    return $this->objToolkit->listButton(Link::getLinkAdminDialog("user", "groupEdit", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_group_edit"), "icon_edit"));
                }
            }
            else {
                return $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_editDisabled", $this->getLang("gruppe_bearbeiten_x")));
            }
        }
        return parent::renderEditAction($objListEntry);
    }


    /**
     * Shows a form in order to start the process of resetting a users password.
     * The step wil be completed by an email, containing a temporary password and a confirmation link.
     *
     * @return string
     * @permissions edit
     */
    protected function actionSendPassword()
    {
        $strReturn = "";
        $objUser = new UserUser($this->getSystemid());

        $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "sendPasswordFinal"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("user_resend_password_hint"));
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("user_username")." ".$objUser->getStrUsername());
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("form_user_email")." ".$objUser->getStrEmail());
        $strReturn .= $this->objToolkit->formInputCheckbox("form_user_sendusername", $this->getLang("form_user_sendusername"));
        $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
        $strReturn .= $this->objToolkit->formClose();
        $strReturn .= $this->objToolkit->divider();

        // show recent password resets
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("user_last_pwchanges_info"));
        $strReturn .= $this->objToolkit->listHeader();
        $arrChanges = SystemPwchangehistory::getHistoryByUser($objUser->getStrSystemid());
        foreach ($arrChanges as $objChange) {
            $strReturn .= $this->objToolkit->simpleAdminList($objChange);
        }
        $strReturn .= $this->objToolkit->listFooter();

        return $strReturn;
    }

    /**
     * @return string
     * @permissions edit
     */
    protected function actionSendPasswordFinal()
    {
        $strReturn = "";
        $objUser = new UserUser($this->getSystemid());

        //add a one-time token and reset the password
        $strToken = generateSystemid();
        $objUser->setStrAuthcode($strToken);
        $objUser->updateObjectToDb();

        $strActivationLink = Link::getLinkAdminHref("login", "pwdReset", "&systemid=".$objUser->getSystemid()."&authcode=".$strToken, false);

        Carrier::getInstance()->getObjLang()->setStrTextLanguage($objUser->getStrAdminlanguage());

        $objMail = new Mail();
        $objMail->addTo($objUser->getStrEmail());
        $objMail->setSubject($this->getLang("user_password_resend_subj"));
        $objMail->setText($this->getLang("user_password_resend_body", array($strActivationLink)));

        if ($this->getParam("form_user_sendusername") != "") {
            $objMail->setText($this->getLang("user_password_resend_body_username", array($objUser->getStrUsername(), $strActivationLink)));
        }

        $objMail->sendMail();

        // insert log entry
        $objNow = new Date();
        $objPwChange = new SystemPwchangehistory();
        $objPwChange->setStrTargetUser($objUser->getStrSystemid());
        $objPwChange->setStrActivationLink($strActivationLink);
        $objPwChange->setStrChangeDate($objNow->getLongTimestamp());
        $objPwChange->updateObjectToDb();

        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul")));
        return $strReturn;
    }

    /**
     * Negates the status of an existing user
     *
     * @throws Exception
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSetUserStatus()
    {
        $strReturn = "";
        $objUser = new UserUser($this->getSystemid());
        if ($objUser->getIntActive() == 1) {
            $objUser->setIntActive(0);
        }
        else {
            $objUser->setIntActive(1);
        }

        if ($objUser->updateObjectToDb()) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "&pv=".$this->getParam("pv")));
        }
        else {
            throw new Exception("Error updating user ".$this->getSystemid(), Exception::$level_ERROR);
        }

        return $strReturn;
    }

    /**
     * @return string
     */
    protected function actionEditUser()
    {
        return $this->actionNewUser("edit");
    }

    /**
     * Creates a new user or edits an already existing one
     *
     * @param string $strAction
     * @param AdminFormgenerator|null $objForm
     *
     * @return string
     * @autoTestable
     */
    protected function actionNewUser($strAction = "new", AdminFormgenerator $objForm = null)
    {
        $strReturn = "";

        //parse userid-param to remain backwards compatible
        if ($this->getParam("systemid") == "" && validateSystemid($this->getParam("userid"))) {
            $this->setSystemid($this->getParam("userid"));
        }

        //load a few default values
        //languages
        $arrLang = array();
        foreach ($this->arrLanguages as $strLanguage) {
            $arrLang[$strLanguage] = $this->getLang("lang_".$strLanguage);
        }


        //access to usersources
        $objUsersources = new UserSourcefactory();

        if ($strAction == "new") {
            //easy one - provide the form to create a new user. validate if there are multiple user-sources available
            //for creating new users
            if (!$this->getObjModule()->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }

            if ($this->getParam("usersource") == "" || $objUsersources->getUsersource($this->getParam("usersource")) == null) {
                $arrSubsystems = $objUsersources->getArrUsersources();

                $arrDD = array();
                foreach ($arrSubsystems as $strOneName) {
                    $objConcreteSubsystem = $objUsersources->getUsersource($strOneName);
                    if ($objConcreteSubsystem->getCreationOfUsersAllowed()) {
                        $arrDD[$strOneName] = $objConcreteSubsystem->getStrReadableName();
                    }
                }

                if (count($arrDD) > 1) {
                    $strReturn = $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "newUser"));
                    $strReturn .= $this->objToolkit->formInputDropdown("usersource", $arrDD, $this->getLang("user_usersource"));
                    $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                    $strReturn .= $this->objToolkit->formClose();

                    return $strReturn;
                }
                else {
                    $arrKeys = array_keys($arrDD);
                    $this->setParam("usersource", array_pop($arrKeys));
                }

            }

            //here we go, the source is set up, create the form
            $objSubsystem = $objUsersources->getUsersource($this->getParam("usersource"));
            $objBlankUser = $objSubsystem->getNewUser();
            if ($objBlankUser != null) {

                if ($objForm == null) {
                    $objForm = $this->getUserForm($objBlankUser, false, "new");
                }

                $objForm->addField(new FormentryHidden("", "usersource"))->setStrValue($this->getParam("usersource"));

                return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "saveUser"));
            }
        }
        else {
            //editing a user. this could be in two modes - globally, or in selfedit mode
            $bitSelfedit = false;
            if (!$this->getObjModule()->rightEdit()) {

                if ($this->getSystemid() == $this->objSession->getUserID() && SystemSetting::getConfigValue("_user_selfedit_") == "true") {
                    $bitSelfedit = true;
                }
                else {
                    return $this->getLang("commons_error_permissions");
                }
            }

            //get user and userForm
            $objUser = new UserUser($this->getSystemid());
            $objSourceUser = $objUsersources->getSourceUser($objUser);

            if ($objForm == null) {
                $objForm = $this->getUserForm($objSourceUser, $bitSelfedit, "edit");
            }


            //set user name
            $strUserName = $this->getParam("user_username") != "" ? $this->getParam("user_username") : $objUser->getStrUsername();
            $objForm->getField("user_username")->setStrValue($strUserName);
            if ($bitSelfedit) {
                $objForm->getField("user_username")->setBitReadonly(true)->setStrValue($objUser->getStrUsername());
            }

            if ($objUser->getStrAdminskin() != "" && $objForm->getField("user_skin") != null) {
                $objForm->getField("user_skin")->setStrValue($objUser->getStrAdminskin());
            }

            if ($objUser->getStrAdminModule() != "" && $objForm->getField("user_startmodule") != null) {
                $objForm->getField("user_startmodule")->setStrValue($objUser->getStrAdminModule());
            }

            if ($objUser->getIntItemsPerPage() != "" && $objForm->getField("user_items_per_page") != null) {
                $objForm->getField("user_items_per_page")->setStrValue($objUser->getIntItemsPerPage());
            }

            $objForm->getField("user_language")->setStrValue($objUser->getStrAdminlanguage());

            if (!$bitSelfedit) {
                if ($objForm->getField("user_adminlogin") != null) {
                    $objForm->getField("user_adminlogin")->setStrValue($objUser->getIntAdmin());
                }

                if ($objForm->getField("user_portal") != null) {
                    $objForm->getField("user_portal")->setStrValue($objUser->getIntPortal());
                }

                if ($objForm->getField("user_active") != null) {
                    $objForm->getField("user_active")->setStrValue($objUser->getIntActive());
                }
            }

            $objForm->addField(new FormentryHidden("", "usersource"))->setStrValue($this->getParam("usersource"));

            return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "saveUser"));
        }


        return $strReturn;

    }

    /**
     * @param UsersourcesUserInterface $objUser
     * @param bool $bitSelfedit
     * @param string $strMode
     *
     * @return AdminFormgenerator|Model
     */
    protected function getUserForm(UsersourcesUserInterface $objUser, $bitSelfedit, $strMode)
    {

        //load a few default values
        //languages
        $arrLang = array();
        foreach ($this->arrLanguages as $strLanguage) {
            $arrLang[$strLanguage] = $this->getLang("lang_".$strLanguage);
        }

        //skins
        $arrSkinsTemp = AdminskinHelper::getListOfAdminskinsAvailable();
        $arrSkins = array();
        foreach ($arrSkinsTemp as $strSkin) {
            $arrSkins[$strSkin] = $strSkin;
        }

        //possible start-modules
        $arrModules = array();
        foreach (SystemModule::getModulesInNaviAsArray() as $arrOneModule) {
            $objOneModule = SystemModule::getModuleByName($arrOneModule["module_name"]);
            if (!$objOneModule->rightView()) {
                continue;
            }

            $arrModules[$objOneModule->getStrName()] = $objOneModule->getAdminInstanceOfConcreteModule()->getLang("modul_titel");
        }


        $objForm = new AdminFormgenerator("user", $objUser);
        $objForm->addField(new FormentryHeadline())->setStrValue($this->getLang("user_personaldata"));

        //globals
        $objName = $objForm->addField(new FormentryText("user", "username"))->setBitMandatory(true)->setStrLabel($this->getLang("user_username"))->setStrValue($this->getParam("user_username"));
        if ($bitSelfedit) {
            $objName->setBitReadonly(true);
        }

        //generic
        //adding elements is more generic right here - load all methods
        if ($objUser->isEditable()) {
            $objAnnotations = new Reflection($objUser);
            $arrProperties = $objAnnotations->getPropertiesWithAnnotation("@fieldType");

            foreach ($arrProperties as $strProperty => $strValue) {
                $objField = $objForm->addDynamicField($strProperty);
                if ($objField->getStrEntryName() == "user_pass" && $strMode == "new") {
                    $objField->setBitMandatory(true);
                }
            }
        }

        //system-settings
        $objForm->addField(new FormentryHeadline())->setStrValue($this->getLang("user_system"));

        $strInheritPermissionsId = $this->getParam("user_inherit_permissions_id");
        if (!empty($strInheritPermissionsId)) {
            $objForm->addField(new FormentryHidden("user", "inherit_permissions_id"))
                ->setStrValue($strInheritPermissionsId);

            $objInheritUser = new UserUser($strInheritPermissionsId);
            $objForm->addField(new FormentryPlaintext("inherit_hint"))
                ->setStrValue($this->objToolkit->warningBox($this->getLang("user_copy_info", "", array($objInheritUser->getStrDisplayName())), "alert-info"));

            $objForm->setFieldToPosition("inherit_hint", 1);
        }

        $objForm->addField(new FormentryDropdown("user", "skin"))
            ->setArrKeyValues($arrSkins)
            ->setStrValue(($this->getParam("user_skin") != "" ? $this->getParam("user_skin") : SystemSetting::getConfigValue("_admin_skin_default_")))
            ->setStrLabel($this->getLang("user_skin"));

        $objForm->addField(new FormentryDropdown("user", "language"))
            ->setArrKeyValues($arrLang)
            ->setStrValue(($this->getParam("user_language") != "" ? $this->getParam("user_language") : ""))
            ->setStrLabel($this->getLang("user_language"))
            ->setBitMandatory(true);


        $objForm->addField(new FormentryDropdown("user", "startmodule"))
            ->setArrKeyValues($arrModules)
            ->setStrValue(($this->getParam("user_startmodule") != "" ? $this->getParam("user_startmodule") : "dashboard"))
            ->setStrLabel($this->getLang("user_startmodule"));

        $objForm->addField(new FormentryDropdown("user", "items_per_page"))
            ->setArrKeyValues(array(10 => 10, 15 => 15, 25 => 25, 50 => 50))
            ->setStrValue(($this->getParam("user_items_per_page") != "" ? $this->getParam("user_items_per_page") : null))
            ->setStrLabel($this->getLang("user_items_per_page"));

        if (!$bitSelfedit) {
            $objForm->addField(new FormentryCheckbox("user", "adminlogin"))->setStrLabel($this->getLang("user_admin"));
            $objForm->addField(new FormentryCheckbox("user", "portal"))->setStrLabel($this->getLang("user_portal"));
            $objForm->addField(new FormentryCheckbox("user", "active"))->setStrLabel($this->getLang("user_aktiv"));
        }

        if (count($objUser->getGroupIdsForUser()) == 0 && empty($strInheritPermissionsId)) {
            $objForm->addField(new FormentryPlaintext("group_hint"))->setStrValue($this->objToolkit->warningBox($this->getLang("form_user_hint_groups")));
            $objForm->setFieldToPosition("group_hint", 1);
        }

        $objForm->addField(new FormentryHidden("", "mode"))->setStrValue($strMode);

        $objUser->updateAdminForm($objForm);
        return $objForm;
    }

    /**
     * Stores the submitted data to the backend / the loginprovider
     *
     * @return string
     */
    protected function actionSaveUser()
    {
        $strReturn = "";
        $bitSelfedit = false;

        $objUsersources = new UserSourcefactory();
        if ($this->getParam("mode") == "new") {
            if (!$this->getObjModule()->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }

            $objSubsystem = $objUsersources->getUsersource($this->getParam("usersource"));
            $objBlankUser = $objSubsystem->getNewUser();
            $objForm = $this->getUserForm($objBlankUser, false, "new");
        }
        else {
            if (!$this->getObjModule()->rightEdit()) {
                if ($this->getSystemid() == $this->objSession->getUserID() && SystemSetting::getConfigValue("_user_selfedit_") == "true") {
                    $bitSelfedit = true;
                }
                else {
                    return $this->getLang("commons_error_permissions");
                }
            }

            $objUser = new UserUser($this->getSystemid());
            $objSourceUser = $objUsersources->getSourceUser($objUser);
            $objForm = $this->getUserForm($objSourceUser, $bitSelfedit, "edit");
        }


        if (($this->getParam("mode") == "new" && !$this->checkAdditionalNewData($objForm))
            | ($this->getParam("mode") == "edit" && !$this->checkAdditionalEditData($objForm))
            | !$objForm->validateForm()
        ) {
            return $this->actionNewUser($this->getParam("mode"), $objForm);
        }

        $objUser = null;
        if ($this->getParam("mode") == "new") {

            //create a new user and pass all relevant data
            $objUser = new UserUser();
            $objUser->setStrSubsystem($this->getParam("usersource"));

            $objUser->setStrUsername($this->getParam("user_username"));
            $objUser->setIntActive(($this->getParam("user_active") != "" && $this->getParam("user_active") == "checked") ? 1 : 0);
            $objUser->setIntAdmin(($this->getParam("user_adminlogin") != "" && $this->getParam("user_adminlogin") == "checked") ? 1 : 0);
            $objUser->setIntPortal(($this->getParam("user_portal") != "" && $this->getParam("user_portal") == "checked") ? 1 : 0);

        }
        elseif ($this->getParam("mode") == "edit") {

            //create a new user and pass all relevant data
            $objUser = new UserUser($this->getSystemid());

            if (!$bitSelfedit) {
                $objUser->setStrUsername($this->getParam("user_username"));
                $objUser->setIntActive(($this->getParam("user_active") != "" && $this->getParam("user_active") == "checked") ? 1 : 0);
                $objUser->setIntAdmin(($this->getParam("user_adminlogin") != "" && $this->getParam("user_adminlogin") == "checked") ? 1 : 0);
                $objUser->setIntPortal(($this->getParam("user_portal") != "" && $this->getParam("user_portal") == "checked") ? 1 : 0);
            }
        }

        $objUser->setStrAdminskin($this->getParam("user_skin"));
        $objUser->setStrAdminlanguage($this->getParam("user_language"));
        $objUser->setStrAdminModule($this->getParam("user_startmodule"));
        $objUser->setIntItemsPerPage($this->getParam("user_items_per_page"));

        $objUser->updateObjectToDb();
        $objSourceUser = $objUser->getObjSourceUser();
        $objForm = $this->getUserForm($objSourceUser, $bitSelfedit, $this->getParam("mode"));
        $objForm->updateSourceObject();
        $objSourceUser->updateObjectToDb();

        // assign user to the same groups if we have an user where we inherit the group settings
        if ($this->getParam("mode") == "new") {
            $strInheritUserId = $this->getParam("user_inherit_permissions_id");
            if (!empty($strInheritUserId)) {
                $objInheritUser = new UserUser($strInheritUserId);
                $arrGroupIds = $objInheritUser->getArrGroupIds();

                foreach ($arrGroupIds as $strGroupId) {
                    $objGroup = new UserGroup($strGroupId);
                    $objSourceGroup = $objGroup->getObjSourceGroup();
                    $objSourceGroup->addMember($objUser->getObjSourceUser());
                }

                $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editMemberships", "&systemid=".$objUser->getStrSystemid()));
                return "";
            }
        }

        if ($this->getParam("mode") == "edit") {
            //Reset the admin-skin cookie to force the new skin
            $objCookie = new Cookie();
            //flush the db-cache
            Carrier::getInstance()->getObjDB()->flushQueryCache();
            $this->objSession->resetUser();
            //and update the cookie
            $objCookie->setCookie("adminskin", $this->objSession->getAdminSkin(false, true));
            //update language set before
            $objCookie->setCookie("adminlanguage", $this->objSession->getAdminLanguage(false, true));
        }

        //flush the navigation cache in order to get new items for a possible updated list
        AdminHelper::flushActionNavigationCache();

        if ($this->getObjModule()->rightView()) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list"));
        }
        else {
            $this->adminReload(Link::getLinkAdminHref($objUser->getStrAdminModule()));
        }


        return $strReturn;
    }

    /**
     * Deletes a user from the database
     *
     * @return string
     * @permissions delete
     */
    protected function actionDeleteUser()
    {
        //The user itself
        $objUser = new UserUser($this->getSystemid());
        $objUser->deleteObject();
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list"));
    }

    //--group-management----------------------------------------------------------------------------------

    /**
     * Returns the list of all current groups
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionGroupList()
    {

        if ($this->getParam("doFilter") != "") {
            $this->objSession->setSession($this->STR_GROUPFILTER_SESSION_KEY, $this->getParam("grouplist_filter"));
            $this->setParam("pv", 1);
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "groupList"));
            return "";
        }

        $strReturn = "";

        //add a filter-form
        $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "groupList"));
        $strReturn .= $this->objToolkit->formInputText("grouplist_filter", $this->getLang("group_name"), $this->objSession->getSession($this->STR_GROUPFILTER_SESSION_KEY));
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("userlist_filter"));
        $strReturn .= $this->objToolkit->formInputHidden("doFilter", "1");
        $strReturn .= $this->objToolkit->formClose();

        $objArraySectionIterator = new ArraySectionIterator(UserGroup::getObjectCount($this->objSession->getSession($this->STR_GROUPFILTER_SESSION_KEY)));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(UserGroup::getObjectList($this->objSession->getSession($this->STR_GROUPFILTER_SESSION_KEY), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objArraySectionIterator, false, "groupList");
        return $strReturn;
    }

    /**
     * @return string
     */
    protected function actionGroupEdit()
    {
        return $this->actionGroupNew("edit");
    }

    /**
     * Edits or creates a group (displays form)
     *
     * @param string $strMode
     * @param AdminFormgenerator|null $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionGroupNew($strMode = "new", AdminFormgenerator $objForm = null)
    {

        $this->setArrModuleEntry("template", "/folderview.tpl");
        $objUsersources = new UserSourcefactory();

        if ($strMode == "new") {

            if ($this->getParam("usersource") == "" || $objUsersources->getUsersource($this->getParam("usersource")) == null) {
                $arrSubsystems = $objUsersources->getArrUsersources();

                $arrDD = array();
                foreach ($arrSubsystems as $strOneName) {
                    $objConcreteSubsystem = $objUsersources->getUsersource($strOneName);
                    if ($objConcreteSubsystem->getCreationOfGroupsAllowed()) {
                        $arrDD[$strOneName] = $objConcreteSubsystem->getStrReadableName();
                    }
                }

                if (count($arrDD) > 1) {
                    $strReturn = $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "groupNew"));
                    $strReturn .= $this->objToolkit->formInputDropdown("usersource", $arrDD, $this->getLang("group_usersource"));
                    $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                    $strReturn .= $this->objToolkit->formClose();

                    return $strReturn;
                }
                else {
                    $arrKeys = array_keys($arrDD);
                    $this->setParam("usersource", array_pop($arrKeys));
                }
            }

            $objSource = $objUsersources->getUsersource($this->getParam("usersource"));
            $objNewGroup = $objSource->getNewGroup();

            if ($objForm == null) {
                $objForm = $this->getGroupForm($objNewGroup);
            }
            $objForm->addField(new FormentryHidden("", "usersource"))->setStrValue($this->getParam("usersource"));
            $objForm->addField(new FormentryHidden("", "mode"))->setStrValue("new");

            return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "groupSave"));
        }

        else {

            $objNewGroup = new UserGroup($this->getSystemid());
            $this->setParam("usersource", $objNewGroup->getStrSubsystem());

            if ($objForm == null) {
                $objForm = $this->getGroupForm($objNewGroup->getObjSourceGroup());
            }
            $objForm->getField("group_name")->setStrValue($objNewGroup->getStrName());
            $objForm->addField(new FormentryHidden("", "usersource"))->setStrValue($this->getParam("usersource"));
            $objForm->addField(new FormentryHidden("", "mode"))->setStrValue("edit");
            return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "groupSave"));
        }

    }


    /**
     * @param UsersourcesGroupInterface|Model $objGroup
     *
     * @return AdminFormgenerator
     */
    private function getGroupForm(UsersourcesGroupInterface $objGroup)
    {

        $objForm = new AdminFormgenerator("group", $objGroup);

        //add the global group-name

        $objForm->addField(new FormentryText("group", "name"))->setBitMandatory(true)->setStrLabel($this->getLang("group_name"))->setStrValue($this->getParam("group_name"));

        if ($objGroup->isEditable()) {
            //adding elements is more generic right here - load all methods
            $objAnnotations = new Reflection($objGroup);

            $arrProperties = $objAnnotations->getPropertiesWithAnnotation("@fieldType");

            foreach ($arrProperties as $strProperty => $strValue) {
                $objForm->addDynamicField($strProperty);
            }

        }

        $objGroup->updateAdminForm($objForm);
        return $objForm;
    }

    /**
     * Saves a new group to database
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionGroupSave()
    {

        if (!$this->getObjModule()->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        if ($this->getParam("mode") == "new") {
            $objUsersources = new UserSourcefactory();
            $objSource = $objUsersources->getUsersource($this->getParam("usersource"));
            $objNewGroup = $objSource->getNewGroup();
            $objForm = $this->getGroupForm($objNewGroup);
        }
        else {
            $objNewGroup = new UserGroup($this->getSystemid());
            $objForm = $this->getGroupForm($objNewGroup->getObjSourceGroup());
        }

        if (!$objForm->validateForm()) {
            return $this->actionGroupNew($this->getParam("mode"), $objForm);
        }

        if ($this->getParam("mode") == "new") {
            $objGroup = new UserGroup();
            $objGroup->setStrSubsystem($this->getParam("usersource"));
        }
        else {
            $objGroup = new UserGroup($this->getSystemid());
        }

        $objGroup->setStrName($this->getParam("group_name"));
        $objGroup->updateObjectToDb();

        $objSourceGroup = $objGroup->getObjSourceGroup();

        $objForm = $this->getGroupForm($objSourceGroup);
        $objForm->updateSourceObject();

        $objSourceGroup->updateObjectToDb();

        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "groupList", "&peClose=1&blockAction=1"));
        return "";

    }

    private function isGroupEditable(UserGroup $objGroup)
    {
        //validate possible blocked groups
        $objConfig = Config::getInstance("module_system", "blockedgroups.php");
        $arrBlockedGroups = explode(",", $objConfig->getConfig("blockedgroups"));
        $arrBlockedGroups[] = SystemSetting::getConfigValue("_admins_group_id_");

        $bitRenderEdit = Carrier::getInstance()->getObjSession()->isSuperAdmin() || ($objGroup->rightEdit() && !in_array($objGroup->getSystemid(), $arrBlockedGroups));

        return $bitRenderEdit;
    }


    /**
     * Returns a list of users belonging to a specified group
     *
     * @param AdminFormgenerator $objForm
     *
     * @return string
     * @permissions edit
     */
    protected function actionGroupMember(AdminFormgenerator $objForm = null)
    {
        $strReturn = "";
        if ($this->getSystemid() != "") {

            $objGroup = new UserGroup($this->getSystemid());

            //validate possible blocked groups
            $bitRenderEdit = $this->isGroupEditable($objGroup);

            $objSourceGroup = $objGroup->getObjSourceGroup();
            $strReturn .= $this->objToolkit->formHeadline($this->getLang("group_memberlist")."\"".$objGroup->getStrName()."\"");

            $objUsersources = new UserSourcefactory();

            if ($objUsersources->getUsersource($objGroup->getStrSubsystem())->getMembersEditable() && $bitRenderEdit) {
                if ($objForm == null) {
                    $objForm = $this->getGroupMemberForm($objGroup);
                }

                $arrFolder = $this->objToolkit->getLayoutFolder($objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "addUserToGroup")), $this->getLang("group_add_user"));
                $strReturn .= $this->objToolkit->getFieldset($arrFolder[1], $arrFolder[0]);

            }


            $objIterator = new ArraySectionIterator($objSourceGroup->getNumberOfMembers());
            $objIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objIterator->setArraySection($objSourceGroup->getUserIdsForGroup($objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

            $strReturn .= $this->objToolkit->listHeader();
            foreach ($objIterator as $strSingleMemberId) {
                $objSingleMember = new UserUser($strSingleMemberId);

                $strAction = "";
                if ($objUsersources->getUsersource($objGroup->getStrSubsystem())->getMembersEditable() && $bitRenderEdit) {
                    $strAction .= $this->objToolkit->listDeleteButton(
                        $objSingleMember->getStrUsername()." (".$objSingleMember->getStrForename()." ".$objSingleMember->getStrName().")",
                        $this->getLang("mitglied_loeschen_frage"),
                        Link::getLinkAdminHref($this->getArrModule("modul"), "groupMemberDelete", "&groupid=".$objGroup->getSystemid()."&userid=".$objSingleMember->getSystemid())
                    );
                }
                $strReturn .= $this->objToolkit->genericAdminList($objSingleMember->getSystemid(), $objSingleMember->getStrDisplayName(), getImageAdmin("icon_user"), $strAction);
            }
            $strReturn .= $this->objToolkit->listFooter().$this->objToolkit->getPageview($objIterator, "user", "groupMember", "systemid=".$this->getSystemid());
        }
        return $strReturn;
    }

    /**
     * Adds a single user to a group
     *
     * @return string
     * @permissions edit
     */
    protected function actionAddUserToGroup()
    {
        $objGroup = new UserGroup($this->getSystemid());
        //validate possible blocked groups
        if (!$this->isGroupEditable($objGroup)) {
            return $this->getLang("commons_error_permissions");
        }

        $objForm = $this->getGroupMemberForm($objGroup);
        if (!$objForm->validateForm()) {
            return $this->actionGroupMember($objForm);
        }


        $objUser = new UserUser($objForm->getField("addusertogroup_user")->getStrValue());
        $objSourceGroup = $objGroup->getObjSourceGroup();

        $objSourceGroup->addMember($objUser->getObjSourceUser());
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "groupMember", "&systemid=".$objGroup->getSystemid()));
        return "";
    }

    /**
     * @param UserGroup $objGroup
     *
     * @return AdminFormgenerator
     */
    private function getGroupMemberForm(UserGroup $objGroup)
    {
        $objForm = new AdminFormgenerator("addUserToGroup", $objGroup);
        $objForm->addField(new FormentryUser("addUserToGroup", "user", null))->setStrValue($this->getParam("addusertogroup_user_id"))->setBitMandatory(true)->setStrLabel($this->getLang("user_username"));
        return $objForm;
    }

    /**
     * Deletes a membership
     *
     * @throws Exception
     * @return string "" in case of success
     * @permissions delete
     */
    protected function actionGroupMemberDelete()
    {
        $strReturn = "";
        $objGroup = new UserGroup($this->getParam("groupid"));
        //validate possible blocked groups
        if (!$this->isGroupEditable($objGroup)) {
            return $this->getLang("commons_error_permissions");
        }

        $objUser = new UserUser($this->getParam("userid"));
        if ($objGroup->getObjSourceGroup()->removeMember($objUser->getObjSourceUser())) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "groupMember", "systemid=".$this->getParam("groupid")));
        }
        else {
            throw new Exception($this->getLang("member_delete_error"), Exception::$level_ERROR);
        }

        return $strReturn;
    }


    /**
     * Deletes a group and all memberships
     *
     * @throws Exception
     * @return void
     * @permissions delete
     */
    protected function actionGroupDelete()
    {
        //Delete memberships
        $objGroup = new UserGroup($this->getSystemid());

        //validate possible blocked groups
        if (!$this->isGroupEditable($objGroup)) {
            throw new Exception($this->getLang("gruppe_loeschen_fehler"), Exception::$level_ERROR);
        }

        //delete group
        if ($objGroup->deleteObject()) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "groupList"));
        }
        else {
            throw new Exception($this->getLang("gruppe_loeschen_fehler"), Exception::$level_ERROR);
        }
    }

    /**
     * Shows a form to manage memberships of a user in groups
     *
     * @return string
     * @permissions edit
     */
    protected function actionEditMemberships()
    {
        $strReturn = "";
        //open the form
        $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "saveMembership"));
        //Create a list of checkboxes
        $objUser = new UserUser($this->getSystemid());

        $strReturn .= $this->objToolkit->formHeadline($this->getLang("user_memberships")."\"".$objUser->getStrUsername()."\"");

        //Collect groups from the same source
        $objUsersources = new UserSourcefactory();
        $objSourcesytem = $objUsersources->getUsersource($objUser->getStrSubsystem());

        $arrGroups = $objSourcesytem->getAllGroupIds();
        $arrUserGroups = $objUser->getArrGroupIds();

        $arrRows = array();
        foreach ($arrGroups as $strSingleGroup) {
            //to avoid privilege escalation, the admin-group has to be treated in a special manner
            //only render the group, if the current user is member of this group
            $objSingleGroup = new UserGroup($strSingleGroup);
            if (!$this->isGroupEditable($objSingleGroup)) {
                continue;
            }

            $strCheckbox = $this->objToolkit->formInputCheckbox($objSingleGroup->getSystemid(), "", in_array($strSingleGroup, $arrUserGroups));
            $strCheckbox = uniSubstr($strCheckbox, uniStrpos($strCheckbox, "<input"));
            $strCheckbox = uniSubstr($strCheckbox, 0, uniStrpos($strCheckbox, ">") + 1);

            $arrRows[] = array($strCheckbox, $objSingleGroup->getStrName());

//            $strReturn .= $this->objToolkit->formInputCheckbox($objSingleGroup->getSystemid(), $objSingleGroup->getStrName(), in_array($strSingleGroup, $arrUserGroups));

        }

        $strReturn .= <<<HTML
    <a href="javascript:KAJONA.admin.permissions.toggleEmtpyRows('[lang,permissions_toggle_visible,system]', '[lang,permissions_toggle_hidden,system]', 'table.kajona-data-table tr');" id="rowToggleLink" class="rowsVisible">[lang,permissions_toggle_visible,system]</a><br /><br />
HTML;


        $strReturn .= $this->objToolkit->dataTable(array(), $arrRows);

        $strReturn .= "<script type=\"text/javascript\">
                KAJONA.admin.permissions.toggleEmtpyRows('".$this->getLang("permissions_toggle_visible", "system")."', '".$this->getLang("permissions_toggle_hidden", "system")."', 'table.kajona-data-table tr');
                </script>";

        $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
        $strReturn .= $this->objToolkit->formClose();
        return $strReturn;
    }

    /**
     * Generates a read-only list of group-assignments for a single user
     *
     * @return string
     * @permissions edit
     */
    protected function actionBrowseMemberships()
    {
        $objUser = new UserUser($this->getSystemid());
        $strReturn = $this->objToolkit->listHeader();
        foreach ($objUser->getObjSourceUser()->getGroupIdsForUser() as $strOneId) {
            $objGroup = new UserGroup($strOneId);
            $strReturn .= $this->objToolkit->genericAdminList($strOneId, $objGroup->getStrDisplayName(), AdminskinHelper::getAdminImage("icon_group"), "");
        }
        $strReturn .= $this->objToolkit->listFooter();
        return $strReturn;
    }


    /**
     * Saves the memberships passed by param
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveMembership()
    {

        $objUser = new UserUser($this->getSystemid());
        $objUsersources = new UserSourcefactory();
        $objSourcesytem = $objUsersources->getUsersource($objUser->getStrSubsystem());

        $arrGroups = $objSourcesytem->getAllGroupIds();
        $arrUserGroups = $objUser->getArrGroupIds();

        //validate possible blocked groups
        $objConfig = Config::getInstance("module_system", "blockedgroups.php");
        $arrBlockedGroups = explode(",", $objConfig->getConfig("blockedgroups"));

        //Searching for groups to enter
        foreach ($arrGroups as $strSingleGroup) {

            $objGroup = new UserGroup($strSingleGroup);
            //skipped for blocked groups, those won't be updated
            if (!$this->isGroupEditable($objGroup)) {
                continue;
            }


            if ($this->getParam($strSingleGroup) != "") {

                //add the user to this group
                if (!in_array($strSingleGroup, $arrUserGroups)) {
                    $objGroup->getObjSourceGroup()->addMember($objUser->getObjSourceUser());
                }
                else {
                    //user is already in the group, remove the marker
                    foreach ($arrUserGroups as $strKey => $strValue) {
                        if ($strValue == $strSingleGroup) {
                            $arrUserGroups[$strKey] = null;
                        }
                    }
                }

            }
        }

        //check, if the current user is member of the admin-group.
        //if not, remain the admin-group as-is
        if (!Carrier::getInstance()->getObjSession()->isSuperAdmin()) {
            $intKey = array_search(SystemSetting::getConfigValue("_admins_group_id_"), $arrUserGroups);
            if ($intKey !== false) {
                $arrUserGroups[$intKey] = null;
            }

            foreach ($arrBlockedGroups as $strOneGroup) {
                $intKey = array_search($strOneGroup, $arrUserGroups);
                if ($intKey !== false) {
                    $arrUserGroups[$intKey] = null;
                }
            }
        }

        //loop the users' list in order to remove unwanted relations
        foreach ($arrUserGroups as $strValue) {
            if (validateSystemid($strValue)) {
                $objGroup = new UserGroup($strValue);
                $objGroup->getObjSourceGroup()->removeMember($objUser->getObjSourceUser());
            }
        }

        if ($this->getParam("folderview")) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "&peClose=1&blockAction=1"));
        }
        else {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list"));
        }
    }


    /**
     * returns a list of the last logins
     *
     * @return string
     * @autoTestable
     * @permissions right1
     */
    protected function actionLoginLog()
    {
        $strReturn = "";
        //fetch log-rows
        $objLogbook = new UserLog();
        $objIterator = new ArraySectionIterator($objLogbook->getLoginLogsCount());
        $objIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objIterator->setArraySection(UserLog::getLoginLogs($objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $arrRows = array();
        foreach ($objIterator as $arrLogRow) {
            $arrSingleRow = array();
            $arrSingleRow[] = $arrLogRow["user_log_sessid"];
            $arrSingleRow[] = ($arrLogRow["user_username"] != "" ? $arrLogRow["user_username"] : $arrLogRow["user_log_userid"]);
            $arrSingleRow[] = dateToString(new Date($arrLogRow["user_log_date"]));
            $arrSingleRow[] = $arrLogRow["user_log_enddate"] != "" ? dateToString(new Date($arrLogRow["user_log_enddate"])) : "";
            $arrSingleRow[] = ($arrLogRow["user_log_status"] == 0 ? $this->getLang("login_status_0") : $this->getLang("login_status_1"));
            $arrSingleRow[] = $arrLogRow["user_log_ip"];

            $strUtraceLinkMap = "href=\"http://www.utrace.de/ip-adresse/".$arrLogRow["user_log_ip"]."\" target=\"_blank\"";
            $strUtraceLinkText = "href=\"http://www.utrace.de/whois/".$arrLogRow["user_log_ip"]."\" target=\"_blank\"";

            if ($arrLogRow["user_log_ip"] != "127.0.0.1" && $arrLogRow["user_log_ip"] != "::1") {
                $arrSingleRow[] = $this->objToolkit->listButton(Link::getLinkAdminManual($strUtraceLinkMap, "", $this->getLang("login_utrace_showmap"), "icon_earth"))
                    ." ".$this->objToolkit->listButton(Link::getLinkAdminManual($strUtraceLinkText, "", $this->getLang("login_utrace_showtext"), "icon_text"));
            }
            else {
                $arrSingleRow[] = $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_earthDisabled", $this->getLang("login_utrace_noinfo")))." ".
                    $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_textDisabled", $this->getLang("login_utrace_noinfo")));
            }

            $arrRows[] = $arrSingleRow;
        }

        //Building the surrounding table
        $arrHeader = array();
        $arrHeader[] = $this->getLang("login_sessid");
        $arrHeader[] = $this->getLang("login_user");
        $arrHeader[] = $this->getLang("login_logindate");
        $arrHeader[] = $this->getLang("login_logoutdate");
        $arrHeader[] = $this->getLang("login_status");
        $arrHeader[] = $this->getLang("login_ip");
        $arrHeader[] = $this->getLang("login_utrace");
        //and fetch the table
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrRows);
        $strReturn .= $this->objToolkit->getPageview($objIterator, "user", "loginlog");

        return $strReturn;
    }


    /**
     * Creates a browser-like view of the users available
     *
     * @return string
     */
    protected function actionUserBrowser()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        $strReturn = "";
        $strFormElement = $this->getParam("form_element");
        if ($this->getSystemid() == "") {
            //show groups
            $arrUsers = UserGroup::getObjectList();
            $strReturn .= $this->objToolkit->listHeader();
            foreach ($arrUsers as $objSingleGroup) {
                $strAction = "";
                $strAction .= $this->objToolkit->listButton(
                    Link::getLinkAdmin(
                        "user",
                        "userBrowser",
                        "&form_element=".$this->getParam("form_element")."&systemid=".$objSingleGroup->getSystemid()."&filter=".$this->getParam("filter")."&checkid=".$this->getParam("checkid"),
                        $this->getLang("user_browser_show"),
                        $this->getLang("user_browser_show"),
                        "icon_folderActionOpen"
                    )
                );

                if ($this->getParam("allowGroup") == "1") {
                    $strAction .= $this->objToolkit->listButton(
                        "<a href=\"#\" title=\"".$this->getLang("group_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strFormElement."', '".addslashes($objSingleGroup->getStrName())."'], ['".$strFormElement."_id', '".$objSingleGroup->getSystemid()."']]);\">".getImageAdmin("icon_accept")
                    );
                }

                $strReturn .= $this->objToolkit->simpleAdminList($objSingleGroup, $strAction);

            }
        }
        else {
            //show members of group
            $objGroup = new UserGroup($this->getSystemid());
            $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();
            $strReturn .= $this->objToolkit->listHeader();

            $strReturn .= $this->objToolkit->genericAdminList(
                generateSystemid(),
                "",
                "",
                $this->objToolkit->listButton(
                    Link::getLinkAdmin(
                        $this->getArrModule("modul"),
                        "userBrowser",
                        "&form_element=".$this->getParam("form_element")."&filter=".$this->getParam("filter")."&allowGroup=".$this->getParam("allowGroup")."&checkid=".$this->getParam("checkid"),
                        $this->getLang("user_list_parent"),
                        $this->getLang("user_list_parent"),
                        "icon_folderActionLevelup"
                    )
                )
            );

            $strCheckId = $this->getParam("checkid");
            $arrCheckIds = json_decode($strCheckId);


            foreach ($arrUsers as $strSingleUser) {
                $objSingleUser = new UserUser($strSingleUser);

                $bitRenderAcceptLink = true;
                if (!empty($arrCheckIds) && is_array($arrCheckIds)) {

                    foreach ($arrCheckIds as $strCheckId) {

                        if (!$this->hasUserViewPermissions($strCheckId, $objSingleUser)) {
                            $bitRenderAcceptLink = false;
                            break;
                        }
                    }
                }

                $strAction = "";
                if (!$bitRenderAcceptLink || $objSingleUser->getIntActive() == 0 || ($this->getParam("filter") == "current" && $objSingleUser->getSystemid() == $this->objSession->getUserID())) {
                    $strAction .= $this->objToolkit->listButton(getImageAdmin("icon_acceptDisabled"));
                }
                else {
                    $strAction .= $this->objToolkit->listButton(
                        "<a href=\"#\" title=\"".$this->getLang("user_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strFormElement."', '".addslashes($objSingleUser->getStrUsername())."'], ['".$strFormElement."_id', '".$objSingleUser->getSystemid()."']]);\">".getImageAdmin("icon_accept")
                    );
                }
                $strReturn .= $this->objToolkit->simpleAdminList($objSingleUser, $strAction);

            }
        }

        return $strReturn;

    }

    /**
     * @return string
     * @throws Exception
     * @permissions edit
     */
    protected function actionSwitchToUser()
    {
        $strReturn = "";
        if (SystemModule::getModuleByName("system")->rightEdit() && Carrier::getInstance()->getObjSession()->isSuperAdmin()) {

            //reset the aspect
            $strAddon = "";
            $objDefaultAspect = SystemAspect::getDefaultAspect();

            if ($objDefaultAspect !== null) {
                $strAddon = "&aspect=".$objDefaultAspect->getSystemid();
            }

            $objNewUser = new UserUser($this->getSystemid());
            if ($this->objSession->switchSessionToUser($objNewUser)) {
                AdminHelper::flushActionNavigationCache();
                $this->adminReload(Link::getLinkAdminHref("dashboard", "", $strAddon));
                return "";
            }
            else {
                throw new Exception("session switch failed", Exception::$level_ERROR);
            }
        }
        else {
            $strReturn .= $this->getLang("commons_error_permissions");
        }

        return $strReturn;
    }


    /**
     * Checks, if two passwords are equal
     *
     * @param string $strPass1
     * @param string $strPass2
     *
     * @return bool
     */
    protected function checkPasswords($strPass1, $strPass2)
    {
        return ($strPass1 == $strPass2);
    }

    /**
     * Checks, if a username is existing or not
     *
     * @param string $strName
     *
     * @return bool
     */
    protected function checkUsernameNotExisting($strName)
    {
        $arrUsers = UserUser::getAllUsersByName($strName);
        return (count($arrUsers) == 0);
    }

    /**
     * @param AdminFormgenerator $objForm
     *
     * @return bool
     */
    protected function checkAdditionalNewData(AdminFormgenerator $objForm)
    {

        $arrParams = Carrier::getAllParams();
        $bitPass = true;
        if (isset($arrParams["user_pass"])) {
            $bitPass = $this->checkPasswords($this->getParam("user_pass"), $this->getParam("user_pass2"));
            if (!$bitPass) {
                $objForm->addValidationError("user_password", $this->getLang("required_password_equal"));
            }
        }

        $bitUsername = $this->checkUsernameNotExisting($this->getParam("user_username"));
        if (!$bitUsername) {
            $objForm->addValidationError("user_username", $this->getLang("required_user_existing"));
        }

        return $bitPass && $bitUsername;
    }

    /**
     * @param AdminFormgenerator $objForm
     *
     * @return bool
     */
    protected function checkAdditionalEditData(AdminFormgenerator $objForm)
    {

        $arrParams = Carrier::getAllParams();
        $bitPass = true;
        if (isset($arrParams["user_pass"])) {
            $bitPass = $this->checkPasswords($this->getParam("user_pass"), $this->getParam("user_pass2"));
            if (!$bitPass) {
                $objForm->addValidationError("password", $this->getLang("required_password_equal"));
            }
        }

        $arrUsers = UserUser::getAllUsersByName($this->getParam("user_username"));
        if (count($arrUsers) > 0) {
            $objUser = $arrUsers[0];
            if ($objUser->getSystemid() != $this->getSystemid()) {
                $objForm->addValidationError("user_username", $this->getLang("required_user_existing"));
                $bitPass = false;
            }
        }

        return $bitPass;
    }

    /**
     * A internal helper to verify if the passed user is allowed to view the listed systemids
     *
     * @param $strValidateId
     * @param UserUser $objUser
     *
     * @return bool
     */
    private function hasUserViewPermissions($strValidateId, UserUser $objUser)
    {
        $objInstance = Objectfactory::getInstance()->getObject($strValidateId);

        if ($objInstance != null) {
            $objCurUser = new UserUser($this->objSession->getUserID());

            try {
                Session::getInstance()->switchSessionToUser($objUser, true);
                if ($objInstance->rightView()) {
                    Session::getInstance()->switchSessionToUser($objCurUser, true);
                    return true;
                }
            }
            catch (Exception $objEx) {
            }
            Session::getInstance()->switchSessionToUser($objCurUser, true);
        }

        return false;
    }


    /**
     * Returns a list of users and/or groups matching the passed query.
     *
     * @return string
     * @xml
     */
    protected function actionGetUserByFilter()
    {
        $strFilter = $this->getParam("filter");
        $strCheckId = $this->getParam("checkid");
        $arrCheckIds = json_decode($strCheckId);

        $arrUsers = array();
        $objSource = new UserSourcefactory();

        if ($this->getParam("user") == "true") {
            $arrUsers = $objSource->getUserlistByUserquery($strFilter);
        }

        if ($this->getParam("group") == "true") {
            $arrUsers = array_merge($arrUsers, $objSource->getGrouplistByQuery($strFilter));
        }

        usort($arrUsers, function ($objA, $objB) {
            if ($objA instanceof UserUser) {
                $strA = $objA->getStrUsername();
            }
            else {
                $strA = $objA->getStrName();
            }

            if ($objB instanceof UserUser) {
                $strB = $objB->getStrUsername();
            }
            else {
                $strB = $objB->getStrName();
            }

            return strcmp(strtolower($strA), strtolower($strB));
        });


        $arrReturn = array();
        foreach ($arrUsers as $objOneElement) {

            if ($this->getParam("block") == "current" && $objOneElement->getSystemid() == $this->objSession->getUserID()) {
                continue;
            }

            $bitUserHasRightView = true;
            if (!empty($arrCheckIds) && is_array($arrCheckIds) && $objOneElement instanceof UserUser) {

                foreach ($arrCheckIds as $strCheckId) {

                    if (!$this->hasUserViewPermissions($strCheckId, $objOneElement)) {
                        $bitUserHasRightView = false;
                        break;
                    }
                }
            }


            if ($bitUserHasRightView) {
                $arrEntry = array();

                if ($objOneElement instanceof UserUser) {
                    $arrEntry["title"] = $objOneElement->getStrDisplayName();
                    $arrEntry["label"] = $objOneElement->getStrDisplayName();
                    $arrEntry["value"] = $objOneElement->getStrDisplayName();
                    $arrEntry["systemid"] = $objOneElement->getSystemid();
                    $arrEntry["icon"] = AdminskinHelper::getAdminImage("icon_user");
                }
                elseif ($objOneElement instanceof UserGroup) {
                    $arrEntry["title"] = $objOneElement->getStrName();
                    $arrEntry["value"] = $objOneElement->getStrName();
                    $arrEntry["label"] = $objOneElement->getStrName();
                    $arrEntry["systemid"] = $objOneElement->getSystemid();
                    $arrEntry["icon"] = AdminskinHelper::getAdminImage("icon_group");
                }

                $arrReturn[] = $arrEntry;
            }
        }

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
        return json_encode($arrReturn);
    }

}
