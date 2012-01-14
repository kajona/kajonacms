<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * This class provides the user and groupmanagement
 *
 * @package module_user
 * @author sidler@mulchprod.de
 */
class class_module_user_admin extends class_admin_simple implements interface_admin {

    private $STR_FILTER_SESSION_KEY = "USERLIST_FILTER_SESSION_KEY";

    //languages, the admin area could display (texts)
    protected $arrLanguages = array();

    /**
	 * Constructor
	 *
	 */
    public function __construct() {

        $this->setArrModuleEntry("modul", "user");
        $this->setArrModuleEntry("moduleId", _user_modul_id_);

        parent::__construct();
        $this->arrLanguages = explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs"));

        //backwards compatibility
        if($this->getAction() == "edit")
            $this->setAction("editUser");

        if($this->getParam("doFilter") != "") {
            $this->objSession->setSession($this->STR_FILTER_SESSION_KEY, $this->getParam("userlist_filter"));
            $this->setParam("pv", 1);
        }
    }


    protected function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("user_liste"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newUser", "", $this->getLang("user_anlegen"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "groupList", "", $this->getLang("gruppen_liste"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "groupNew", "", $this->getLang("gruppen_anlegen"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "loginLog", "", $this->getLang("loginlog"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    public function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "saveUser") {

            if($this->getSystemid() == "" || $this->getSystemid() != $this->objSession->getUserID())
                $arrReturn["user_username"] = "string";

            //merge with fields from source
            $objBlankUser = null;
            if($this->getSystemid() != "") {
                $objUser = new class_module_user_user($this->getSystemid());
                $objBlankUser = $objUser->getObjSourceUser();
            }
            else {
                $objUsersources = new class_module_user_sourcefactory();
                $objSubsystem = $objUsersources->getUsersource($this->getParam("usersource"));
                $objBlankUser = $objSubsystem->getNewUser();
            }
            if($objBlankUser != null) {
                $arrFields = $objBlankUser->getEditFormEntries();
                /* @var $objOneField class_usersources_form_entry */
                foreach($arrFields as $objOneField) {
                    if($objOneField->getBitRequired() && $objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE)
                        $arrReturn["user_".$objOneField->getStrName()] = "date";

                    else if($objOneField->getBitRequired() && $objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_EMAIL)
                        $arrReturn["user_".$objOneField->getStrName()] = "email";

                    else if($objOneField->getBitRequired() )
                        $arrReturn["user_".$objOneField->getStrName()] = "string";

                }
            }

        }
        if($strAction == "groupSave" ) {
            $arrReturn["group_name"] = "string";

            //merge with fields from source
            $objBlankGroup = null;
            if($this->getSystemid() != "") {
                $objUser = new class_module_user_group($this->getSystemid());
                $objBlankGroup = $objUser->getObjSourceGroup();
            }
            else {
                $objUsersources = new class_module_user_sourcefactory();
                $objSubsystem = $objUsersources->getUsersource($this->getParam("usersource"));
                $objBlankGroup = $objSubsystem->getNewGroup();
            }
            if($objBlankGroup != null) {
                $arrFields = $objBlankGroup->getEditFormEntries();
                /* @var $objOneField class_usersources_form_entry */
                foreach($arrFields as $objOneField) {
                    if($objOneField->getBitRequired() && $objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE)
                        $arrReturn["group_".$objOneField->getStrName()] = "date";

                    else if($objOneField->getBitRequired() && $objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_EMAIL)
                        $arrReturn["group_".$objOneField->getStrName()] = "email";

                    else if($objOneField->getBitRequired() )
                        $arrReturn["group_".$objOneField->getStrName()] = "string";

                }
            }
        }

        return $arrReturn;
    }

    /**
     * Renders the form to create a new entry
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "newUser"));
    }

    /**
     * Renders the form to edit an existing entry
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editUser", "&systemid=".$this->getSystemid()));
    }


    /**
	 * Returns a list of current users
	 *
	 * @return string
     * @autoTestable
     * @permissions view
	 */
    protected function actionList() {
        $strReturn = "";

        //add a filter-form
        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"]), "list");
        $strReturn .= $this->objToolkit->formInputText("userlist_filter", $this->getLang("user_username"), $this->objSession->getSession($this->STR_FILTER_SESSION_KEY));
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("userlist_filter"));
        $strReturn .= $this->objToolkit->formInputHidden("doFilter", "1");
        $strReturn .= $this->objToolkit->formClose();
        $strReturn .= $this->objToolkit->divider();

        $objArraySectionIterator = new class_array_section_iterator(class_module_user_user::getNumberOfUsers($this->objSession->getSession($this->STR_FILTER_SESSION_KEY)));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_user_user::getAllUsers($this->objSession->getSession($this->STR_FILTER_SESSION_KEY), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objArraySectionIterator, false, "userList");
        return $strReturn;
    }

    protected function renderStatusAction(class_model $objListEntry) {
        if($objListEntry instanceof class_module_user_user && $objListEntry->rightEdit()) {
            if($objListEntry->getIntActive() == 1)
                return $this->objToolkit->listButton(getLinkAdmin("user", "setUserStatus", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("user_active"), "icon_enabled.gif"));
            else
                return $this->objToolkit->listButton(getLinkAdmin("user", "setUserStatus", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("user_inactive"), "icon_disabled.gif"));
        }
    }

    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_user_user && $objListEntry->rightDelete())
            return $this->objToolkit->listDeleteButton($objListEntry->getStrDisplayName(), $this->getLang("user_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteUser", "&systemid=".$objListEntry->getSystemid()));

        if($objListEntry instanceof class_module_user_group) {
            if($objListEntry->getSystemid() != _guests_group_id_  && $objListEntry->getSystemid() != _admins_group_id_) {
                if($objListEntry->rightDelete())
                    return $this->objToolkit->listDeleteButton($objListEntry->getStrDisplayName(), $this->getLang("gruppe_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "groupDelete", "&systemid=".$objListEntry->getSystemid()));
            }
            else {
                return $this->objToolkit->listButton(getImageAdmin("icon_tonDisabled.gif", $this->getLang("gruppe_loeschen_x")));
            }
        }
        return "";
    }

    protected function getNewEntryAction($strListIdentifier) {
        if($strListIdentifier == "userList" && $this->getObjModule()->rightEdit())
            return $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "newUser", "", $this->getLang("user_anlegen"), $this->getLang("user_anlegen"), "icon_new.gif"));

        if($strListIdentifier == "groupList" && $this->getObjModule()->rightEdit())
            return $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "groupNew", "", $this->getLang("gruppen_anlegen"), $this->getLang("gruppen_anlegen"), "icon_new.gif"));
    }


    /**
     * @param class_model|class_module_user_user $objListEntry
     * @return array
     */
    protected function renderAdditionalActions(class_model $objListEntry) {
        $objUsersources = new class_module_user_sourcefactory();

        $arrReturn = array();
        if($objListEntry instanceof class_module_user_user && $objListEntry->rightEdit() && $objUsersources->getUsersource($objListEntry->getStrSubsystem())->getMembersEditable())
            $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("user", "editMemberships", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("user_zugehoerigkeit"), "icon_group.gif"));

        if($objListEntry instanceof class_module_user_user && $objListEntry->getObjSourceUser()->isEditable() && $objListEntry->getObjSourceUser()->isPasswortResetable() && $objListEntry->rightEdit() && checkEmailaddress($objListEntry->getStrEmail()))
            $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("user", "sendPassword", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("user_password_resend"), "icon_mail.gif"));

        if($objListEntry instanceof class_module_user_group && $objListEntry->rightEdit())
            $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("user", "groupMember", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("gruppe_mitglieder"), "icon_group.gif"));

        return $arrReturn;
    }

    protected function renderEditAction(class_model $objListEntry) {
        if($objListEntry instanceof class_module_user_group) {
            if($objListEntry->getSystemid() != _guests_group_id_  && $objListEntry->getSystemid() != _admins_group_id_) {
                if($objListEntry->rightEdit())
                    return $this->objToolkit->listButton(getLinkAdmin("user", "groupEdit", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("gruppe_bearbeiten"), "icon_pencil.gif"));
            }
            else
                return $this->objToolkit->listButton(getImageAdmin("icon_pencilDisabled.gif", $this->getLang("gruppe_bearbeiten_x")));
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
    protected function actionSendPassword() {
        $strReturn = "";
        $objUser = new class_module_user_user($this->getSystemid());

        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "sendPasswordFinal"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("user_resend_password_hint"));
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("user_username")." ".$objUser->getStrUsername());
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("user_email")." ".$objUser->getStrEmail());
        $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
        $strReturn .= $this->objToolkit->formClose();
        return $strReturn;
    }

    /**
     * @return string
     * @permissions edit
     */
    protected function actionSendPasswordFinal() {
        $strReturn = "";
        $objUser = new class_module_user_user($this->getSystemid());

        //add a one-time token and reset the password
        $strToken = generateSystemid();
        $objUser->setStrAuthcode($strToken);
        $objUser->updateObjectToDb();

        $strActivationLink = getLinkAdminHref("login", "pwdReset", "&systemid=".$objUser->getSystemid()."&authcode=".$strToken, false);

        $objMail = new class_mail();
        $objMail->addTo($objUser->getStrEmail());
        $objMail->setSubject($this->getLang("user_password_resend_subj"));
        $objMail->setText($this->getLang("user_password_resend_body").$strActivationLink);

        $objMail->sendMail();

        $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
        return $strReturn;
    }

    /**
     * Negates the status of an existing user
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSetUserStatus() {
        $strReturn = "";
        $objUser = new class_module_user_user($this->getSystemid());
        if($objUser->getIntActive() == 1)
            $objUser->setIntActive(0);
        else
            $objUser->setIntActive(1);

        if($objUser->updateObjectToDb())
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
        else
            throw new class_exception("Error updating user ".$this->getSystemid(), class_exception::$level_ERROR);

        return $strReturn;
    }

    protected function actionEditUser() {
        return $this->actionNewUser("edit");
    }

    /**
     * Creates a new user or edits a already existing one
     *
     * @param string $strAction
     * @return string
     * @autoTestable
     */
    protected function actionNewUser($strAction = "new") {
        $strReturn = "";

        //parse userid-param to remain backwards compatible
        if($this->getParam("systemid") == "" && validateSystemid($this->getParam("userid")))
            $this->setSystemid($this->getParam("userid"));

        //load a few default values
        //languages
        $arrLang = array();
        foreach ($this->arrLanguages as $strLanguage)
            $arrLang[$strLanguage] = $this->getLang("lang_".$strLanguage);

        //skins
        $arrSkinsTemp = class_adminskin_helper::getListOfAdminskinsAvailable();
        $arrSkins = array();
        foreach ($arrSkinsTemp as $strSkin)
            $arrSkins[$strSkin]	= $strSkin;

        //access to usersources
        $objUsersources = new class_module_user_sourcefactory();


        if($strAction == "new") {
            //easy one - provide the form to create a new user. validate if there are multiple user-sources available
            //for creating new users
            if(!$this->getObjModule()->rightEdit())
                return $this->getLang("commons_error_permissions");

            if($this->getParam("usersource") == "" || $objUsersources->getUsersource($this->getParam("usersource")) == null) {
                $arrSubsystems = $objUsersources->getArrUsersources();

                $arrDD = array();
                foreach($arrSubsystems as $strOneName) {
                    $objConcreteSubsystem = $objUsersources->getUsersource($strOneName);
                    if($objConcreteSubsystem->getCreationOfUsersAllowed())
                        $arrDD[$strOneName] = $strOneName;
                }

                if(count($arrDD) > 1) {
                    $strReturn  = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "newUser"));
                    $strReturn .= $this->objToolkit->formInputDropdown("usersource", $arrDD, $this->getLang("user_usersource"));
                    $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                    $strReturn .= $this->objToolkit->formClose();

                    return $strReturn;
                }
                else
                    $this->setParam("usersource", array_pop($arrDD));

            }

            //here we go, the source is set up, create the form
            $objSubsystem = $objUsersources->getUsersource($this->getParam("usersource"));
            $objBlankUser = $objSubsystem->getNewUser();
            if($objBlankUser != null) {
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveUser"));
                $strReturn .= $this->objToolkit->getValidationErrors($this, "saveUser");
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("user_personaldata"));
                //globally required
                $strReturn .= $this->objToolkit->formInputText("user_username", $this->getLang("user_username"), $this->getParam("user_username"));

                if($objBlankUser->isEditable()) {
                    //Fetch the fields from the source
                    $arrFields = $objBlankUser->getEditFormEntries();
                    /* @var $objOneField class_usersources_form_entry */
                    foreach($arrFields as $objOneField) {
                        if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE)
                            $strReturn .= $this->objToolkit->formDateSingle("user_".$objOneField->getStrName(), $this->getLang("user_".$objOneField->getStrName()), $objOneField->getStrValue() != "" ? new class_date($objOneField->getStrValue()) : null );

                        else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_TEXT)
                            $strReturn .= $this->objToolkit->formInputText("user_".$objOneField->getStrName(), $this->getLang("user_".$objOneField->getStrName()), $this->getParam("user_".$objOneField->getStrName()) != "" ? $this->getParam("user_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                        else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_LONGTEXT)
                            $strReturn .= $this->objToolkit->formInputTextArea("user_".$objOneField->getStrName(), $this->getLang("user_".$objOneField->getStrName()), $this->getParam("user_".$objOneField->getStrName()) != "" ? $this->getParam("user_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                        else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_EMAIL)
                            $strReturn .= $this->objToolkit->formInputText("user_".$objOneField->getStrName(), $this->getLang("user_".$objOneField->getStrName()), $this->getParam("user_".$objOneField->getStrName()) != "" ? $this->getParam("user_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                        else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_PASSWORD) {
                            $strReturn .= $this->objToolkit->formInputPassword("user_".$objOneField->getStrName(), $this->getLang("user_".$objOneField->getStrName()) );
                            $strReturn .= $this->objToolkit->formInputPassword("user_".$objOneField->getStrName()."2", $this->getLang("user_".$objOneField->getStrName()."2") );
                        }
                    }
                }

                //system-internal fields
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("user_system"));
                $strReturn .= $this->objToolkit->formInputDropdown("skin", $arrSkins, $this->getLang("user_skin"), ($this->getParam("skin") != "" ? $this->getParam("skin") : _admin_skin_default_));
                $strReturn .= $this->objToolkit->formInputDropdown("language", $arrLang, $this->getLang("user_language"), $this->getParam("language"));
                $strReturn .= $this->objToolkit->formInputCheckbox("adminlogin", $this->getLang("user_admin"), ($this->getParam("adminlogin") != "" ? true : false ));
                $strReturn .= $this->objToolkit->formInputCheckbox("portal", $this->getLang("user_portal"), ($this->getParam("portal") != "" ? true : false ));
                $strReturn .= $this->objToolkit->formInputCheckbox("aktiv", $this->getLang("user_aktiv"), ($this->getParam("aktiv") != "" ? true : false ));

                $strReturn .= $this->objToolkit->formInputHidden("systemid");
                $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
                $strReturn .= $this->objToolkit->formInputHidden("usersource", $this->getParam("usersource"));
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                $strReturn .= $this->objToolkit->formClose();

            }
        }
        else {
            //editing a user. this could be in two modes - globally, or in selfedit mode
            $bitSelfedit = false;
            if(!$this->getObjModule()->rightEdit()) {

                if($this->getSystemid() == $this->objSession->getUserID() && _user_selfedit_ == "true")
                    $bitSelfedit = true;
                else
                    return $this->getLang("commons_error_permissions");
            }


            $objUser = new class_module_user_user($this->getSystemid());
            $objSourceUser = $objUsersources->getSourceUser($objUser);
            $this->setParam("usersource", $objUser->getStrSubsystem());
            if($objSourceUser != null) {
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveUser"));
                $strReturn .= $this->objToolkit->getValidationErrors($this, "saveUser");
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("user_personaldata"));
                //globally required
                if(!$bitSelfedit)
                    $strReturn .= $this->objToolkit->formInputText("user_username", $this->getLang("user_username"), $this->getParam("user_username") != "" ? $this->getParam("user_username") : $objUser->getStrUsername(), "inputText", "", ($bitSelfedit ) );

                //Fetch the fields from the source
                if($objSourceUser->isEditable()) {
                    $arrFields = $objSourceUser->getEditFormEntries();
                    /* @var $objOneField class_usersources_form_entry */
                    foreach($arrFields as $objOneField) {
                        if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE)
                            $strReturn .= $this->objToolkit->formDateSingle("user_".$objOneField->getStrName(), $this->getLang("user_".$objOneField->getStrName()), $objOneField->getStrValue() != "" ? new class_date($objOneField->getStrValue()) : null );

                        if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_TEXT)
                            $strReturn .= $this->objToolkit->formInputText("user_".$objOneField->getStrName(), $this->getLang("user_".$objOneField->getStrName()), $this->getParam("user_".$objOneField->getStrName()) != "" ? $this->getParam("user_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                        if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_EMAIL)
                            $strReturn .= $this->objToolkit->formInputText("user_".$objOneField->getStrName(), $this->getLang("user_".$objOneField->getStrName()), $this->getParam("user_".$objOneField->getStrName()) != "" ? $this->getParam("user_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                        if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_PASSWORD) {
                            $strReturn .= $this->objToolkit->formInputPassword("user_".$objOneField->getStrName(), $this->getLang("user_".$objOneField->getStrName()) );
                            $strReturn .= $this->objToolkit->formInputPassword("user_".$objOneField->getStrName()."2", $this->getLang("user_".$objOneField->getStrName()."2") );
                        }
                    }
                }

                //system-internal fields
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("user_system"));
                $strReturn .= $this->objToolkit->formInputDropdown("skin", $arrSkins, $this->getLang("user_skin"),   ($this->getParam("skin") != "" ? $this->getParam("skin") :     ($objUser->getStrAdminskin() != "" ? $objUser->getStrAdminskin() : _admin_skin_default_)   )  );
                $strReturn .= $this->objToolkit->formInputDropdown("language", $arrLang, $this->getLang("user_language"), ($this->getParam("language") != "" ? $this->getParam("language") : $objUser->getStrAdminlanguage() ));
                if(!$bitSelfedit) {
                    $strReturn .= $this->objToolkit->formInputCheckbox("adminlogin", $this->getLang("user_admin"), ( issetPost("skin") ? ($this->getParam("adminlogin") != "" ? true : false ) :  $objUser->getIntAdmin() ));
                    $strReturn .= $this->objToolkit->formInputCheckbox("portal", $this->getLang("user_portal"), ( issetPost("skin") ? ($this->getParam("portal") != "" ? true : false) : $objUser->getIntPortal() ));
                    $strReturn .= $this->objToolkit->formInputCheckbox("aktiv", $this->getLang("user_aktiv"), ( issetPost("skin") ?  ($this->getParam("aktiv") != "" ? true : false ) : $objUser->getIntActive() ));
                }

                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("usersource", $this->getParam("usersource"));
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                $strReturn .= $this->objToolkit->formClose();

            }
        }


        return $strReturn;

    }


    /**
     * Stores the submitted data to the backend / the loginprovider
     * @return string
     */
    protected function actionSaveUser() {
        $strReturn = "";


        $objUsersources = new class_module_user_sourcefactory();
        $strPasswordKey = "";
        $objSubsystem = $objUsersources->getUsersource($this->getParam("usersource"));
        $objBlankUser = $objSubsystem->getNewUser();
        if($objBlankUser != null) {
            $arrFields = $objBlankUser->getEditFormEntries();
            /* @var $objOneField class_usersources_form_entry */
            foreach($arrFields as $objOneField) {
                if($objOneField->getBitRequired() && $objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_PASSWORD)
                    $strPasswordKey = "user_".$objOneField->getStrName();
            }
        }

        if(!$this->validateForm()
                | ($this->getParam("mode") == "new" && !$this->checkAdditionalNewData($strPasswordKey))
                | ($this->getParam("mode") == "edit" && !$this->checkAdditionalEditData($strPasswordKey)) ) {
            return $this->actionNewUser($this->getParam("mode"));
        }

        if($this->getParam("mode") == "new") {

            if(!$this->getObjModule()->rightEdit())
                return $this->getLang("commons_error_permissions");

            //create a new user and pass all relevant data

            $objUser = new class_module_user_user();

            $objUser->setStrUsername($this->getParam("user_username"));
            $objUser->setIntActive(($this->getParam("aktiv") != "" && $this->getParam("aktiv") == "checked") ?  1 :  0);
            $objUser->setIntAdmin(($this->getParam("adminlogin") != "" && $this->getParam("adminlogin") == "checked") ?  1 :  0);
            $objUser->setIntPortal(($this->getParam("portal") != "" && $this->getParam("portal") == "checked") ?  1 :  0);
            $objUser->setStrAdminskin($this->getParam("skin"));
            $objUser->setStrAdminlanguage($this->getParam("language"));
            $objUser->setStrSubsystem($this->getParam("usersource"));

            $objUser->updateObjectToDb();

            $objSourceUser = $objUser->getObjSourceUser();
            //pass fields to new source-object
            $arrSourceFields = $objSourceUser->getEditFormEntries();
            foreach($arrSourceFields as $objOneField) {
                if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE) {
                    $strName = "user_".$objOneField->getStrName();
                    if($this->getParam($strName."_year") != "") {
                        $objDate = new class_date();
                        $objDate->generateDateFromParams($strName, $this->getAllParams());
                        $objOneField->setStrValue($objDate->getLongTimestamp());
                    }
                }
                else {
                    $objOneField->setStrValue($this->getParam("user_".$objOneField->getStrName()));
                }
            }

            $objSourceUser->setEditFormEntries($arrSourceFields);
            $objSourceUser->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
        }
        else if($this->getParam("mode") == "edit") {

            $bitSelfedit = false;
            if(!$this->getObjModule()->rightEdit()) {

                if($this->getSystemid() == $this->objSession->getUserID() && _user_selfedit_ == "true")
                    $bitSelfedit = true;
                else
                    return $this->getLang("commons_error_permissions");
            }

            //create a new user and pass all relevant data
            $objUser = new class_module_user_user($this->getSystemid());

            if(!$bitSelfedit) {
                $objUser->setStrUsername($this->getParam("user_username"));
                $objUser->setIntActive(($this->getParam("aktiv") != "" && $this->getParam("aktiv") == "checked") ?  1 :  0);
                $objUser->setIntAdmin(($this->getParam("adminlogin") != "" && $this->getParam("adminlogin") == "checked") ?  1 :  0);
                $objUser->setIntPortal(($this->getParam("portal") != "" && $this->getParam("portal") == "checked") ?  1 :  0);
            }
            $objUser->setStrAdminskin($this->getParam("skin"));
            $objUser->setStrAdminlanguage($this->getParam("language"));

            $objUser->updateObjectToDb();

            $objSourceUser = $objUser->getObjSourceUser();
            //pass fields to new source-object
            $arrSourceFields = $objSourceUser->getEditFormEntries();
            foreach($arrSourceFields as $objOneField) {
                if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE) {
                    $strName = "user_".$objOneField->getStrName();
                    if($this->getParam($strName."_year") != "") {
                        $objDate = new class_date();
                        $objDate->generateDateFromParams($strName, $this->getAllParams());
                        $objOneField->setStrValue($objDate->getLongTimestamp());
                    }
                }
                else {
                    $objOneField->setStrValue($this->getParam("user_".$objOneField->getStrName()));
                }
            }

            $objSourceUser->setEditFormEntries($arrSourceFields);
            $objSourceUser->updateObjectToDb();

            //Reset the admin-skin cookie to force the new skin
            $objCookie = new class_cookie();
            //flush the db-cache
            $this->objDB->flushQueryCache();
            $this->objSession->resetUser();
            //and update the cookie
            $objCookie->setCookie("adminskin", $this->objSession->getAdminSkin(false));
            //update language set before
            $objCookie->setCookie("adminlanguage", $this->objSession->getAdminLanguage(false));


            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
        }



        return $strReturn;
    }

    /**
	 * Deletes a user from the database
	 *
	 * @return string
     * @permissions delete
	 */
    protected function actionDeleteUser() {
        //The user itself
        $objUser = new class_module_user_user($this->getSystemid());
        $objUser->deleteObject();
        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
    }

    //--group-management----------------------------------------------------------------------------------

    /**
	 * Returns the list of all current groups
	 *
	 * @return string
     * @autoTestable
     * @permissions view
	 */
    protected function actionGroupList() {
        $objArraySectionIterator = new class_array_section_iterator(class_module_user_group::getNumberOfGroups());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_user_group::getAllGroups($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator, false, "groupList");
    }


    protected function actionGroupEdit() {
        return $this->actionGroupNew("edit");
    }

    /**
     * Edits or creates a group (displays form)
     *
     * @param string $strMode
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionGroupNew($strMode = "new") {
        $strReturn = "";

        $objUsersources = new class_module_user_sourcefactory();

        if($strMode == "new") {

            if($this->getParam("usersource") == "" || $objUsersources->getUsersource($this->getParam("usersource")) == null) {
                $arrSubsystems = $objUsersources->getArrUsersources();

                $arrDD = array();
                foreach($arrSubsystems as $strOneName) {
                    $objConcreteSubsystem = $objUsersources->getUsersource($strOneName);
                    if($objConcreteSubsystem->getCreationOfGroupsAllowed())
                        $arrDD[$strOneName] = $strOneName;
                }

                if(count($arrDD) > 1) {
                    $strReturn  = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "groupNew"));
                    $strReturn .= $this->objToolkit->formInputDropdown("usersource", $arrDD, $this->getLang("group_usersource"));
                    $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                    $strReturn .= $this->objToolkit->formClose();

                    return $strReturn;
                }
                else
                    $this->setParam("usersource", array_pop($arrDD));
            }

            $objSource = $objUsersources->getUsersource($this->getParam("usersource"));
            $objNewGroup = $objSource->getNewGroup();

            $strReturn .= $this->objToolkit->getValidationErrors($this, "groupSave");
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "groupSave"));
            $strReturn .= $this->objToolkit->formInputText("group_name", $this->getLang("group_name"), $this->getParam("group_name"));

            //load the elements provided by the login-provider

            //Fetch the fields from the source
            if($objNewGroup->isEditable()) {
                $arrFields = $objNewGroup->getEditFormEntries();
                /* @var $objOneField class_usersources_form_entry */
                foreach($arrFields as $objOneField) {
                    if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE)
                        $strReturn .= $this->objToolkit->formDateSingle("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()), $objOneField->getStrValue() != "" ? new class_date($objOneField->getStrValue()) : null );

                    else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_TEXT)
                        $strReturn .= $this->objToolkit->formInputText("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()), $this->getParam("group_".$objOneField->getStrName()) != "" ? $this->getParam("group_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                    else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_LONGTEXT)
                        $strReturn .= $this->objToolkit->formInputTextArea("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()), $this->getParam("group_".$objOneField->getStrName()) != "" ? $this->getParam("group_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                    else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_EMAIL)
                        $strReturn .= $this->objToolkit->formInputText("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()), $this->getParam("group_".$objOneField->getStrName()) != "" ? $this->getParam("group_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                    else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_PASSWORD) {
                        $strReturn .= $this->objToolkit->formInputPassword("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()) );
                        $strReturn .= $this->objToolkit->formInputPassword("group_".$objOneField->getStrName()."2", $this->getLang("group_".$objOneField->getStrName()."2") );
                    }
                }
            }

            $strReturn .= $this->objToolkit->formInputHidden("systemid");
            $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
            $strReturn .= $this->objToolkit->formInputHidden("usersource", $this->getParam("usersource"));

            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
            $strReturn .= $this->objToolkit->formClose();
        }

        else {

            $objNewGroup = new class_module_user_group($this->getSystemid());
            $this->setParam("usersource", $objNewGroup->getStrSubsystem());

            $strReturn .= $this->objToolkit->getValidationErrors($this, "groupSave");
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "groupSave"));
            $strReturn .= $this->objToolkit->formInputText("group_name", $this->getLang("group_name"), $this->getParam("group_name") != "" ? $this->getParam("group_name") : $objNewGroup->getStrName());

            //load the elements provided by the login-provider

            //Fetch the fields from the source
            if($objNewGroup->getObjSourceGroup()->isEditable()) {
                $arrFields = $objNewGroup->getObjSourceGroup()->getEditFormEntries();
                /* @var $objOneField class_usersources_form_entry */
                foreach($arrFields as $objOneField) {
                    if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE)
                        $strReturn .= $this->objToolkit->formDateSingle("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()), $objOneField->getStrValue() != "" ? new class_date($objOneField->getStrValue()) : null );

                    else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_TEXT)
                        $strReturn .= $this->objToolkit->formInputText("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()), $this->getParam("group_".$objOneField->getStrName()) != "" ? $this->getParam("group_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                    else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_LONGTEXT)
                        $strReturn .= $this->objToolkit->formInputTextArea("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()), $this->getParam("group_".$objOneField->getStrName()) != "" ? $this->getParam("group_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                    else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_EMAIL)
                        $strReturn .= $this->objToolkit->formInputText("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()), $this->getParam("group_".$objOneField->getStrName()) != "" ? $this->getParam("group_".$objOneField->getStrName()) : $objOneField->getStrValue() );

                    else if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_PASSWORD) {
                        $strReturn .= $this->objToolkit->formInputPassword("group_".$objOneField->getStrName(), $this->getLang("group_".$objOneField->getStrName()) );
                        $strReturn .= $this->objToolkit->formInputPassword("group_".$objOneField->getStrName()."2", $this->getLang("group_".$objOneField->getStrName()."2") );
                    }
                }
            }

            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
            $strReturn .= $this->objToolkit->formInputHidden("usersource", $this->getParam("usersource"));

            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
            $strReturn .= $this->objToolkit->formClose();
        }

        return $strReturn;
    }

    /**
	 * Saves a new group to database
	 *
	 * @return string "" in case of success
	 */
    protected function actionGroupSave() {
        if(!$this->validateForm())
            return $this->actionGroupNew($this->getParam("mode"));



        if($this->getParam("mode") == "new") {

            if(!$this->getObjModule()->rightEdit())
                return $this->getLang("commons_error_permissions");

            //create a new group and pass all relevant data

            $objGroup = new class_module_user_group();
            $objGroup->setStrName($this->getParam("group_name"));
            $objGroup->setStrSubsystem($this->getParam("usersource"));
            $objGroup->updateObjectToDb();

            $objSourceGroup = $objGroup->getObjSourceGroup();
            //pass fields to new source-object
            $arrSourceFields = $objSourceGroup->getEditFormEntries();
            foreach($arrSourceFields as $objOneField) {
                if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE) {
                    $strName = "group_".$objOneField->getStrName();
                    if($this->getParam($strName."_year") != "") {
                        $objDate = new class_date();
                        $objDate->generateDateFromParams($strName, $this->getAllParams());
                        $objOneField->setStrValue($objDate->getLongTimestamp());
                    }
                }
                else {
                    $objOneField->setStrValue($this->getParam("group_".$objOneField->getStrName()));
                }
            }

            $objSourceGroup->setEditFormEntries($arrSourceFields);
            $objSourceGroup->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "groupList"));
            return;
        }
        else {

            if(!$this->getObjModule()->rightEdit())
                return $this->getLang("commons_error_permissions");

            //create a new group and pass all relevant data

            $objGroup = new class_module_user_group($this->getSystemid());
            $objGroup->setStrName($this->getParam("group_name"));
            $objGroup->updateObjectToDb();

            $objSourceGroup = $objGroup->getObjSourceGroup();
            //pass fields to new source-object
            $arrSourceFields = $objSourceGroup->getEditFormEntries();
            foreach($arrSourceFields as $objOneField) {
                if($objOneField->getIntType() == class_usersources_form_entry::$INT_TYPE_DATE) {
                    $strName = "group_".$objOneField->getStrName();
                    if($this->getParam($strName."_year") != "") {
                        $objDate = new class_date();
                        $objDate->generateDateFromParams($strName, $this->getAllParams());
                        $objOneField->setStrValue($objDate->getLongTimestamp());
                    }
                }
                else {
                    $objOneField->setStrValue($this->getParam("group_".$objOneField->getStrName()));
                }
            }

            $objSourceGroup->setEditFormEntries($arrSourceFields);
            $objSourceGroup->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "groupList"));
            return;
        }


    }



    /**
	 * Returns a list of users belonging to a specified group
	 *
	 * @return string
     * @permissions edit
	 */
    protected function actionGroupMember() {
        $strReturn = "";
        if($this->getSystemid() != "") {
            $objGroup = new class_module_user_group($this->getSystemid());
            $objSourceGroup = $objGroup->getObjSourceGroup();
            $strReturn .= $this->objToolkit->formHeadline($this->getLang("group_memberlist")."\"".$objGroup->getStrName()."\"");
            $objUsersources = new class_module_user_sourcefactory();

            $objArraySectionIterator = new class_array_section_iterator($objSourceGroup->getNumberOfMembers());
            $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection($objSourceGroup->getUserIdsForGroup($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

            $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "user", "groupMember", "systemid=".$this->getSystemid());
            $arrMembers = $arrPageViews["elements"];

            $strReturn .= $this->objToolkit->listHeader();
            $intI = 0;
            foreach ($arrMembers as $strSingleMemberId) {
                $objSingleMember = new class_module_user_user($strSingleMemberId);

                $strAction = "";
                if($objUsersources->getUsersource($objGroup->getStrSubsystem())->getMembersEditable() && in_array(_admins_group_id_, $this->objSession->getGroupIdsAsArray())) {
                    $strAction .= $this->objToolkit->listDeleteButton($objSingleMember->getStrUsername()." (".$objSingleMember->getStrForename() ." ". $objSingleMember->getStrName() .")"
                             ,$this->getLang("mitglied_loeschen_frage")
                             ,getLinkAdminHref($this->arrModule["modul"], "groupMemberDelete", "&groupid=".$objGroup->getSystemid()."&userid=".$objSingleMember->getSystemid()));
                }
                $strReturn .= $this->objToolkit->genericAdminList($objSingleMember->getSystemid(), $objSingleMember->getStrDisplayName(), getImageAdmin("icon_user.gif"), $strAction, $intI++);
            }
            $strReturn .= $this->objToolkit->listFooter().$arrPageViews["pageview"];
        }
        return $strReturn;
    }


    /**
	 * Deletes a membership
	 *
	 * @return string "" in case of success
     * @permissions delete
	 */
    protected function actionGroupMemberDelete() {
        $strReturn = "";
        $objGroup = new class_module_user_group($this->getParam("groupid"));
        $objUser = new class_module_user_user($this->getParam("userid"));
        if($objGroup->getObjSourceGroup()->removeMember($objUser->getObjSourceUser()))
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "groupMember", "systemid=".$this->getParam("groupid")));
        else
            throw new class_exception($this->getLang("member_delete_error"), class_exception::$level_ERROR);

        return $strReturn;
    }


    /**
	 * Deletes a group and all memberships
	 *
	 * @return void
     * @permissions delete
	 */
    protected function actionGroupDelete() {
        //Delete memberships
        $objGroup = new class_module_user_group($this->getSystemid());
        //delete group
        if($objGroup->deleteObject()) {
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "groupList"));
        }
        else
            throw new class_exception($this->getLang("gruppe_loeschen_fehler"), class_exception::$level_ERROR);
    }

    /**
	 * Shows a form to manage memberships of a user in groups
	 *
	 * @return string
     * @permissions edit
	 */
    protected function actionEditMemberships() {
        $strReturn = "";
        //open the form
        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveMembership"));
        //Create a list of checkboxes
        $objUser = new class_module_user_user($this->getSystemid());

        $strReturn .= $this->objToolkit->formHeadline($this->getLang("user_memberships")."\"".$objUser->getStrUsername()."\"");

        //Collect groups from the same source
        $objUsersources = new class_module_user_sourcefactory();
        $objSourcesytem = $objUsersources->getUsersource($objUser->getStrSubsystem());

        $arrGroups = $objSourcesytem->getAllGroupIds();
        $arrUserGroups = $objUser->getArrGroupIds();

        //to avoid privilege escalation, the admin-group has to be treated in a special manner
        //only render the group, if the current user is member of this group
        $bitShowAdmin = false;
        if(in_array(_admins_group_id_, $this->objSession->getGroupIdsAsArray()))
            $bitShowAdmin = true;

        foreach($arrGroups as $strSingleGroup) {

            if($strSingleGroup == _admins_group_id_ && !$bitShowAdmin)
                continue;


            $objSingleGroup = new class_module_user_group($strSingleGroup);
            if(in_array($strSingleGroup, $arrUserGroups)) {
                //user in group, checkbox checked
                $strReturn .= $this->objToolkit->formInputCheckbox($objSingleGroup->getSystemid(), $objSingleGroup->getStrName(), true);
            }
            else {
                //User not yet in group, checkbox unchecked
                $strReturn .= $this->objToolkit->formInputCheckbox($objSingleGroup->getSystemid(), $objSingleGroup->getStrName());
            }
        }

        $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
        $strReturn .= $this->objToolkit->formClose();
        return $strReturn;
    }


    /**
	 * Saves the memberships passed by param
	 *
	 * @return string "" in case of success
     * @permissions edit
	 */
    protected function actionSaveMembership() {

        $objUser = new class_module_user_user($this->getSystemid());
        $objUsersources = new class_module_user_sourcefactory();
        $objSourcesytem = $objUsersources->getUsersource($objUser->getStrSubsystem());

        $arrGroups = $objSourcesytem->getAllGroupIds();
        $arrUserGroups = $objUser->getArrGroupIds();

        //Searching for groups to enter
        foreach ($arrGroups as $strSingleGroup) {
            if($this->getParam($strSingleGroup) != "") {

                //add the user to this group
                if(!in_array($strSingleGroup, $arrUserGroups)) {
                    $objGroup = new class_module_user_group($strSingleGroup);
                    $objGroup->getObjSourceGroup()->addMember($objUser->getObjSourceUser());
                }
                else {
                    //user is already in the group, remove the marker
                    foreach($arrUserGroups as $strKey => $strValue)
                        if($strValue == $strSingleGroup)
                            $arrUserGroups[$strKey] = null;
                }

            }
        }

        //check, if the current user is member of the admin-group.
        //if not, remain the admin-group as-is
        if(!in_array(_admins_group_id_, $this->objSession->getGroupIdsAsArray())) {
            $intKey = array_search(_admins_group_id_, $arrUserGroups);
            if($intKey !== false) {
                $arrUserGroups[$intKey] = null;
            }
        }


        //loop the users' list in order to remove unwanted relations
        foreach($arrUserGroups as $strValue) {
            if(validateSystemid($strValue)) {
                $objGroup = new class_module_user_group($strValue);
                $objGroup->getObjSourceGroup()->removeMember($objUser->getObjSourceUser());
            }
        }

        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
    }


    /**
     * returns a list of the last logins
     *
     * @return string
     * @autoTestable
     * @permissions right1
     */
    protected function actionLoginLog() {
        $strReturn = "";
        //fetch log-rows
        $objLogbook = new class_module_user_log();
        $objArraySectionIterator = new class_array_section_iterator($objLogbook->getLoginLogsCount());
        $objArraySectionIterator->setIntElementsPerPage(_user_log_nrofrecords_);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objLogbook->getLoginLogsSection($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "user", "loginlog");
        $arrLogs = $arrPageViews["elements"];

        $arrRows = array();
        foreach(array_keys($arrLogs) as $intI) {
            $arrRows[$intI] = array();
            $arrRows[$intI][]	= $arrLogs[$intI]["user_log_id"];
            $arrRows[$intI][]	= ($arrLogs[$intI]["user_username"] != "" ? $arrLogs[$intI]["user_username"] : $arrLogs[$intI]["user_log_userid"]);
            $arrRows[$intI][]	= timeToString($arrLogs[$intI]["user_log_date"]);
            $arrRows[$intI][]	= ($arrLogs[$intI]["user_log_status"] == 0 ? $this->getLang("login_status_0") : $this->getLang("login_status_1"));
            $arrRows[$intI][]	= $arrLogs[$intI]["user_log_ip"];
        }

        //Building the surrounding table
        $arrHeader = array();
        $arrHeader[]	= $this->getLang("login_nr");
        $arrHeader[]	= $this->getLang("login_user");
        $arrHeader[]	= $this->getLang("commons_date");
        $arrHeader[]	= $this->getLang("login_status");
        $arrHeader[]	= $this->getLang("login_ip");
        //and fetch the table
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrRows);
        $strReturn .= $arrPageViews["pageview"];

        return $strReturn;
    }


    /**
     * Creates a browser-like view of the users available
     * @return string
     * @permissions view
     */
    protected function actionUserBrowser() {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        $strReturn = "";
        $strFormElement = $this->getParam("form_element");
        if($this->getSystemid() == "") {
            //show groups
            $arrUsers = class_module_user_group::getAllGroups();
            $strReturn .= $this->objToolkit->listHeader();
            $intI = 0;
            foreach($arrUsers as $objSingleGroup) {
                $strAction = "";
                $strAction .= $this->objToolkit->listButton(getLinkAdmin("user", "userBrowser", "&form_element=".$this->getParam("form_element")."&systemid=".$objSingleGroup->getSystemid()."&filter=".$this->getParam("filter"), $this->getLang("user_browser_show"), $this->getLang("user_browser_show"), "icon_folderActionOpen.gif"));

                if($this->getParam("allowGroup") == "1")
                    $strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("group_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strFormElement."', '".addslashes($objSingleGroup->getStrName())."'], ['".$strFormElement."_id', '".$objSingleGroup->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif"));

                $strReturn .= $this->objToolkit->simpleAdminList($objSingleGroup, $strAction, $intI++);

            }
        }
        else {
            //show members of group
            $objGroup = new class_module_user_group($this->getSystemid());
            $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();
            $strReturn .= $this->objToolkit->listHeader();
            $intI = 0;

            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "", "", $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "userBrowser", "&form_element=".$this->getParam("form_element")."&filter=".$this->getParam("filter")."&allowGroup=".$this->getParam("allowGroup"), $this->getLang("user_list_parent"), $this->getLang("user_list_parent"), "icon_folderActionLevelup.gif")), $intI++);
            foreach($arrUsers as $strSingleUser) {
                $objSingleUser = new class_module_user_user($strSingleUser);

                $strAction = "";
                if($this->getParam("filter") == "current" && $objSingleUser->getSystemid() == $this->objSession->getUserID())
                    $strAction .= $this->objToolkit->listButton(getImageAdmin("icon_acceptDisabled.gif"));
                else
                    $strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("user_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strFormElement."', '".addslashes($objSingleUser->getStrUsername())."'], ['".$strFormElement."_id', '".$objSingleUser->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif"));
                $strReturn .= $this->objToolkit->simpleAdminList($objSingleUser, $strAction, $intI++);

            }
        }

        return $strReturn;

    }


    /**
     * Checks, if two passwords are equal
     *
     * @param string $strPass1
     * @param string $strPass2
     * @return bool
     */
    protected function checkPasswords($strPass1, $strPass2) {
        return ($strPass1 == $strPass2);
    }

    /**
     * Checks, if a username is existing or not
     *
     * @param string $strName
     * @return bool
     */
    protected function checkUsernameNotExisting($strName) {
        $arrUsers = class_module_user_user::getAllUsersByName($strName);
        return (count($arrUsers) == 0);
    }

    protected function checkAdditionalNewData($strPasswordKey) {
        $bitPass = $strPasswordKey == "" || $this->checkPasswords($this->getParam($strPasswordKey), $this->getParam($strPasswordKey."2"));
        if(!$bitPass)
            $this->addValidationError("user_password", $this->getLang("required_password_equal"));

        $bitUsername = $this->checkUsernameNotExisting($this->getParam("user_username"));
        if(!$bitUsername)
            $this->addValidationError("user_username", $this->getLang("required_user_existing"));

        return $bitPass && $bitUsername;
    }

    protected function checkAdditionalEditData($strPasswordKey) {
        $bitPass = $this->checkPasswords($this->getParam($strPasswordKey), $this->getParam($strPasswordKey."2"));
        if(!$bitPass)
            $this->addValidationError("passwort", $this->getLang("required_password_equal"));

        $arrUsers = class_module_user_user::getAllUsersByName($this->getParam("user_username"));
        if(count($arrUsers) > 0) {
            $objUser = $arrUsers[0];
            if($objUser->getSystemid() != $this->getSystemid()) {
                $this->addValidationError("user_username", $this->getLang("required_user_existing"));
                $bitPass = false;
            }
        }

        return $bitPass;
    }


    /**
     * Returns a list of users and/or groups matching the passed query.
     *
     * @return string
     * @xml
     * @permissions view
     */
    protected function actionGetUserByFilter() {
        $strReturn = "";
        $strFilter = $this->getParam("filter");

        $arrElements = array();
        $objSource = new class_module_user_sourcefactory();

        if($this->getParam("user") == "true") {
            $arrElements = $objSource->getUserlistByUserquery($strFilter);
        }

        if($this->getParam("group") == "true") {
            $arrElements = array_merge($arrElements, $objSource->getGrouplistByQuery($strFilter));
        }

        usort($arrElements, array("class_module_user_admin", "sortUserAndGroups"));

        $strReturn .= "<result>\n";
        foreach ($arrElements as $objOneElement) {

            if($this->getParam("block") == "current" && $objOneElement->getSystemid() == $this->objSession->getUserID())
                continue;

            if($objOneElement instanceof class_module_user_user) {
                $strReturn .= "  <user>\n";
                $strReturn .= "    <title>".xmlSafeString($objOneElement->getStrUsername(). " (".$objOneElement->getStrName().", ".$objOneElement->getStrForename()." )")."</title>\n";
                $strReturn .= "    <systemid>".xmlSafeString($objOneElement->getSystemid())."</systemid>\n";
                $strReturn .= "    <icon>".xmlSafeString(_skinwebpath_."/pics/icon_user.gif")."</icon>\n";
                $strReturn .= "  </user>\n";
            }
            else if($objOneElement instanceof class_module_user_group) {
                $strReturn .= "  <user>\n";
                $strReturn .= "    <title>".xmlSafeString($objOneElement->getStrName())."</title>\n";
                $strReturn .= "    <systemid>".xmlSafeString($objOneElement->getSystemid())."</systemid>\n";
                $strReturn .= "    <icon>".xmlSafeString(_skinwebpath_."/pics/icon_group.gif")."</icon>\n";
                $strReturn .= "  </user>\n";
            }
        }
        $strReturn .= "</result>\n";
		return $strReturn;
    }


    public static function sortUserAndGroups($objA, $objB) {
        if($objA instanceof class_module_user_user)
            $strA = $objA->getStrUsername();
        else
            $strA = $objA->getStrName();

        if($objB instanceof class_module_user_user)
            $strB = $objB->getStrUsername();
        else
            $strB = $objB->getStrName();

        return strcmp(strtolower($strA), strtolower($strB));
    }
}
