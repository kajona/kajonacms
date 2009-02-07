<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

//base class
include_once(_adminpath_."/class_admin.php");
//Interface
include_once(_adminpath_."/interface_admin.php");
//model
include_once(_systempath_."/class_modul_user_log.php");
include_once(_systempath_."/class_modul_user_user.php");
include_once(_systempath_."/class_modul_user_group.php");

/**
 * This class provides the user and groupmanagement
 *
 * @package modul_system
 */
class class_modul_user_admin extends class_admin implements interface_admin {

    //languages, the admin area could display (texts)
    private $arrLanguages = array();

    /**
	 * Constructor
	 *
	 */
    public function __construct() {
        $arrModul["name"] 			= "modul_user";
        $arrModul["author"] 		= "sidler@mulchprod.de";
        $arrModul["moduleId"] 		= _user_modul_id_;
        $arrModul["modul"]			= "user";
        $arrModul["table"]			= _dbprefix_."user";

        //base class
        parent::__construct($arrModul);

        $this->arrLanguages = explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs"));
    }

    /**
	 * Action-Block, decides what to do
	 *
	 * @param string $strAction
	 */
    public function action($strAction = "") {
        if($strAction == "")
            $strAction = "list";

        $strReturn = "";

        try {
            if($strAction == "list")
                $strReturn = $this->actionList();
            if($strAction == "new" || $strAction == "edit")
                $strReturn = $this->actionNew($strAction);
            if($strAction == "save") {
                if($this->validateForm() & $this->checkAdditionalNewData()) {
                    $strReturn = $this->actionSave();
                    if($strReturn == "")
                        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
                }
                else {
                    $strReturn = $this->actionNew("new");
                }
            }
            if($strAction == "saveedit") {
                if($this->validateForm() & $this->checkAdditionalEditData()) {
                    $strReturn = $this->actionEdit();
                    if($strReturn == "")
                        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
                }
                else {
                    $strReturn = $this->actionNew("edit");
                }
            }
            if($strAction == "status") {
                $strReturn = $this->actionStatus();
                if($strReturn == "")
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
            }
            if($strAction == "deletefinal") {
                if($this->actionDeleteFinal())
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
                else
                    $strReturn = $this->getText("user_loeschen_fehler");
            }
            if($strAction == "grouplist")
                $strReturn = $this->actionGroupList();
            if($strAction == "groupnew" || $strAction == "groupedit")
                $strReturn = $this->actionGroupNew();
            if($strAction == "groupsave") {
                if($this->validateForm()) {
                    $strReturn = $this->actionGroupSave();
                    if($strReturn == "")
                        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "grouplist"));
                }
                else {
                    $strReturn = $this->actionGroupNew();
                }
            }
            if($strAction == "groupsaveedit") {
                if($this->validateForm()) {
                    $strReturn = $this->actionGroupSaveEdit();
                    if($strReturn == "")
                        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "grouplist"));
                }
                else {
                    $strReturn = $this->actionGroupNew();
                }
            }
            if($strAction == "groupmember")
                $strReturn = $this->actionGroupMember();
            if($strAction == "groupmemberdeletefinal") {
                $strReturn = $this->actionGroupMemberDeleteFinal();
                if($strReturn == "")
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "groupmember", "groupid=".$this->getParam("groupid")));
            }
            if($strAction == "groupdeletefinal") {
                $strReturn = $this->actionGroupDeleteFinal();
                if($strReturn == "")
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "grouplist"));
            }
            if($strAction == "membership")
                $strReturn = $this->actionMembership();
            if($strAction == "membershipsave") {
                $strReturn = $this->actionSaveMembership();
                if($strReturn == "")
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
            }
            if($strAction == "loginlog")
                $strReturn = $this->actionLoginLog();

        }
        catch (class_exception $objException) {
            $objException->processException();
            $strReturn .= "An internal error occured: ".$objException->getMessage();
        }

        $this->strTemp = $strReturn;
    }

    public function getOutputContent() {
        return $this->strTemp;
    }

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("modul_rechte"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("user_liste"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "new", "", $this->getText("user_anlegen"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "grouplist", "", $this->getText("gruppen_liste"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "groupnew", "", $this->getText("gruppen_anlegen"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "loginlog", "", $this->getText("loginlog"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    protected function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "save") {
            $arrReturn["username"] = "string";
            $arrReturn["email"] = "email";
            $arrReturn["passwort"] = "string";
            $arrReturn["passwort2"] = "string";
        }
        if($strAction == "saveedit") {
            //$arrReturn["username"] = "string";
            $arrReturn["email"] = "email";
        }
        if($strAction == "groupsave" || $strAction == "groupsaveedit") {
            $arrReturn["gruppename"] = "string";
        }

        return $arrReturn;
    }

//*"*****************************************************************************************************
//--USER-Mgmt--------------------------------------------------------------------------------------------


    /**
	 * Returns a list of current users
	 *
	 * @return string
	 */
    private function actionList() {
        $strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strReturn = $this->objToolkit->listHeader();
            $arrUsers = class_modul_user_user::getAllUsers();
            $intI = 0;
            foreach ($arrUsers as $objOneUser) 	{
                $strActions = "";
                if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
                    $strActions .= $this->objToolkit->listButton(getLinkAdmin("user", "edit", "&userid=".$objOneUser->getSystemid(), "", $this->getText("user_bearbeiten"), "icon_pencil.gif"));
                if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"])))
                    $strActions .= $this->objToolkit->listDeleteButton($objOneUser->getStrUsername(). " (".$objOneUser->getStrForename()." ".$objOneUser->getStrName() .")", $this->getText("user_loeschen_frage"),
                                   getLinkAdminHref($this->arrModule["modul"], "deletefinal", "&userid=".$objOneUser->getSystemid()));
                if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
                    $strActions .= $this->objToolkit->listButton(getLinkAdmin("user", "membership", "&userid=".$objOneUser->getSystemid(), "", $this->getText("user_zugehoerigkeit"), "icon_group.gif"));
                //new 2.1: the status icon
                if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
                    if($objOneUser->getIntActive() == 1)
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("user", "status", "&userid=".$objOneUser->getSystemid(), "", $this->getText("user_active"), "icon_enabled.gif"));
                    else
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("user", "status", "&userid=".$objOneUser->getSystemid(), "", $this->getText("user_inactive"), "icon_disabled.gif"));
                }
                $strCenter = $this->getText("user_logins").$objOneUser->getIntLogins().$this->getText("user_lastlogin").timeToString($objOneUser->getIntLastLogin());
                $strReturn .= $this->objToolkit->listRow3($objOneUser->getStrUsername(). " (".$objOneUser->getStrForename() . " " . $objOneUser->getStrName().")", $strCenter, $strActions, getImageAdmin("icon_user.gif"), $intI++);
            }
            //And one row to create a new one
            if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
                $strReturn .= $this->objToolkit->listRow3("", "", $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "new", "", $this->getText("user_anlegen"), $this->getText("user_anlegen"), "icon_blank.gif")), "", $intI++);
            $strReturn .= $this->objToolkit->listFooter();
        }
        else
        $strReturn .= $this->getText("fehler_recht");
        return $strReturn;
    }


    /**
     * Negates the status of an existing user
     *
     * @return string "" in case of success
     */
    private function actionStatus() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            $objUser = new class_modul_user_user($this->getParam("userid"));
            if($objUser->getIntActive() == 1)
                $objUser->setIntActive(0);
            else
                $objUser->setIntActive(1);
            $objUser->setStrPass("");
            if($objUser->updateObjectToDb())
                return "";
            else
                throw new class_exception("Error updating user ".$this->getParam("userid"), class_exception::$level_ERROR);
        }
        else
            $strReturn .= $this->getText("fehler_recht");

        return $strReturn;
    }

    /**
	 * Creates a new user or edits a already existing one
	 *
	 * @return string
	 */
    private function actionNew($strAction) {
        $strReturn = "";
        $bitSelf = false;
        //Right: Right Edit or edit your own profile
        $bitRight = false;
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
            $bitRight = true;
        //Own profile?
        if(!$bitRight && $this->getParam("userid")!= "") {
            if($this->getParam("userid") == $this->objSession->getUserID() && _user_selfedit_ == "true") {
                $bitRight = true;
                $bitSelf = true;
            }
        }

        if($bitRight) {
            //Collecting all skins to offer them
            include_once(_systempath_."/class_filesystem.php");
            $objFilesystem = new class_filesystem();
            $arrSkins = $objFilesystem->getCompleteList(_skinpath_, array(), array(), array(".", ".."), true, false);
            $arrSkinsTemp = $arrSkins["folders"];
            $arrSkins = array();
            foreach ($arrSkinsTemp as $strSkin)
                $arrSkins[$strSkin]	= $strSkin;

            //Fetching languages
            $arrLang = array();
            foreach ($this->arrLanguages as $strLanguage)
                $arrLang[$strLanguage] = $this->getText("lang_".$strLanguage);
            //Start the form
            if($strAction == "new")
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "save"));
            else
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveedit"));

            if($this->getParam("userid") != "") {
                $objUser = new class_modul_user_user($this->getParam("userid"));

                //Form filled with the data
                $strReturn .= $this->objToolkit->getValidationErrors($this);
                $strReturn .= $this->objToolkit->formHeadline($this->getText("user_personaldata"));
                if(!$bitSelf)
                    $strReturn .= $this->objToolkit->formInputText("username", $this->getText("username"), $objUser->getStrUsername());
                $strReturn .= $this->objToolkit->formInputPassword("passwort", $this->getText("passwort"));
                $strReturn .= $this->objToolkit->formInputPassword("passwort2", $this->getText("passwort2"));
                $strReturn .= $this->objToolkit->formInputText("email", $this->getText("email"), $objUser->getStrEmail());
                $strReturn .= $this->objToolkit->formInputText("vorname", $this->getText("vorname") , $objUser->getStrForename());
                $strReturn .= $this->objToolkit->formInputText("nachname", $this->getText("nachname"), $objUser->getStrName());
                $strReturn .= $this->objToolkit->formInputText("strasse", $this->getText("strasse"), $objUser->getStrStreet());
                $strReturn .= $this->objToolkit->formInputText("plz", $this->getText("plz"), $objUser->getStrPostal());
                $strReturn .= $this->objToolkit->formInputText("ort", $this->getText("ort"), $objUser->getStrCity());
                $strReturn .= $this->objToolkit->formInputText("tel", $this->getText("tel"), $objUser->getStrTel());
                $strReturn .= $this->objToolkit->formInputText("handy", $this->getText("handy"), $objUser->getStrMobile());
                $strReturn .= $this->objToolkit->formInputText("gebdatum", $this->getText("gebdatum"), $objUser->getStrDate());
                $strReturn .= $this->objToolkit->formHeadline($this->getText("user_system"));
                $strReturn .= $this->objToolkit->formInputDropdown("skin", $arrSkins, $this->getText("skin"), ($objUser->getStrAdminskin() != "" ? $objUser->getStrAdminskin() : _admin_skin_default_));
                $strReturn .= $this->objToolkit->formInputDropdown("language", $arrLang, $this->getText("language"), $objUser->getStrAdminlanguage());
                if(!$bitSelf) {
                    $strReturn .= $this->objToolkit->formInputCheckbox("admin", $this->getText("admin"), $objUser->getIntAdmin());
                    $strReturn .= $this->objToolkit->formInputCheckbox("portal", $this->getText("portal"), $objUser->getIntPortal());
                    $strReturn .= $this->objToolkit->formInputCheckbox("aktiv", $this->getText("aktiv"), $objUser->getIntActive());
                }
                $strReturn .= $this->objToolkit->formInputHidden("userid", $objUser->getSystemid());
                if($bitSelf)
                $strReturn .= $this->objToolkit->formInputHidden("modus", "selfedit");
            }
            else {
                //Blank form
                $strReturn .= $this->objToolkit->getValidationErrors($this);
                $strReturn .= $this->objToolkit->formHeadline($this->getText("user_personaldata"));
                $strReturn .= $this->objToolkit->formInputText("username", $this->getText("username"), $this->getParam("username"));
                $strReturn .= $this->objToolkit->formInputPassword("passwort", $this->getText("passwort"));
                $strReturn .= $this->objToolkit->formInputPassword("passwort2", $this->getText("passwort2"));
                $strReturn .= $this->objToolkit->formInputText("email", $this->getText("email"), $this->getParam("email"));
                $strReturn .= $this->objToolkit->formInputText("vorname", $this->getText("vorname"), $this->getParam("vorname"));
                $strReturn .= $this->objToolkit->formInputText("nachname", $this->getText("nachname"), $this->getParam("nachname"));
                $strReturn .= $this->objToolkit->formInputText("strasse", $this->getText("strasse"), $this->getParam("strasse"));
                $strReturn .= $this->objToolkit->formInputText("plz", $this->getText("plz"), $this->getParam("plz"));
                $strReturn .= $this->objToolkit->formInputText("ort", $this->getText("ort"), $this->getParam("ort"));
                $strReturn .= $this->objToolkit->formInputText("tel", $this->getText("tel"), $this->getParam("tel"));
                $strReturn .= $this->objToolkit->formInputText("handy", $this->getText("handy"), $this->getParam("handy"));
                $strReturn .= $this->objToolkit->formInputText("gebdatum", $this->getText("gebdatum"), $this->getParam("gebdatum"));
                $strReturn .= $this->objToolkit->formHeadline($this->getText("user_system"));
                $strReturn .= $this->objToolkit->formInputDropdown("skin", $arrSkins, $this->getText("skin"), ($this->getParam("skin") != "" ? $this->getParam("skin") : _admin_skin_default_));
                $strReturn .= $this->objToolkit->formInputDropdown("language", $arrLang, $this->getText("language"), $this->getParam("language"));
                $strReturn .= $this->objToolkit->formInputCheckbox("admin", $this->getText("admin"));
                $strReturn .= $this->objToolkit->formInputCheckbox("portal", $this->getText("portal"));
                $strReturn .= $this->objToolkit->formInputCheckbox("aktiv", $this->getText("aktiv"));
                $strReturn .= $this->objToolkit->formInputHidden("userid");

            }
            //End the form
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
            $strReturn .= $this->objToolkit->formClose();
        }
        else
            $strReturn .= $this->getText("fehler_recht");

        return $strReturn;
    }

    /**
	 * Creates a new User in the database, values in $arrParams
	 *
	 * @return string
	 */
    private function actionSave() {
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))	{
            if($this->getParam("username") != "" && $this->getParam("username") != " ") {
                if($this->checkUsernameNotExisting($this->getParam("username")) ) {
                    //Passwoerter gleich?
                    if($this->checkPasswords($this->getParam("passwort"), $this->getParam("passwort2"))) {
                        //valid Email address specified?
                        if(checkEmailaddress($this->getParam("email"))) {
                            //Collecting remaining data
                            $intActive = ($this->getParam("aktiv") != "" && $this->getParam("aktiv") == "checked") ?  1 :  0;
                            $intAdmin = ($this->getParam("admin") != "" && $this->getParam("admin") == "checked") ?  1 :  0;
                            $intPortal = ($this->getParam("portal") != "" && $this->getParam("portal") == "checked") ?  1 :  0;

                            $objUser = new class_modul_user_user("");
                            $objUser->setStrUsername($this->getParam("username"));
                            $objUser->setStrPass($this->getParam("passwort"));
                            $objUser->setStrEmail($this->getParam("email"));
                            $objUser->setStrForename($this->getParam("vorname"));
                            $objUser->setStrName($this->getParam("nachname"));
                            $objUser->setStrStreet($this->getParam("strasse"));
                            $objUser->setStrPostal($this->getParam("plz"));
                            $objUser->setStrCity($this->getParam("ort"));
                            $objUser->setStrTel($this->getParam("tel"));
                            $objUser->setStrMobile($this->getParam("handy"));
                            $objUser->setStrDate($this->getParam("gebdatum"));
                            $objUser->setStrAdminlanguage($this->getParam("language"));
                            $objUser->setIntActive($intActive);
                            $objUser->setIntAdmin($intAdmin);
                            $objUser->setIntPortal($intPortal);
                            $objUser->setStrAdminskin($this->getParam("skin"));

                            if(!$objUser->saveObjectToDb()) {
                                throw new class_exception($this->getText("fehler_speichern"), class_exception::$level_ERROR);
                            }
                            else {
                                //try to create a default-dashboard
                                include_once(_systempath_."/class_modul_dashboard_widget.php");
                                $objDashboard = new class_modul_dashboard_widget();
                                $objDashboard->createInitialWidgetsForUser($objUser->getSystemid());
                                return "";
                            }

                        }
                        else {
                            return $this->objToolkit->warningBox($this->getText( "user_fehler_mail"));
                        }
                    }
                    else {
                        return $this->objToolkit->warningBox($this->getText( "user_fehler_pass"));
                    }
                }
                else {
                    return $this->objToolkit->warningBox($this->getText( "user_fehler_namedoppelt"));
                }
            }
            else
            return $this->objToolkit->warningBox($this->getText( "user_fehler_name"));
        }
        else
        return $this->getText( "fehler_recht");

    } //aktion_speichern()

    /**
	 * saves a modified user in db, values passed in arrParam
	 *
	 * @return string "" if successfull
	 */
    private function actionEdit() {
        //Rights: Own profile or have the needed rights
        $bitRight = false;
        $bitSelfedit = false;
        if($this->getParam("username") == "")
        $this->setParam("username",  "");
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
            $bitRight = true;
        //Own Profile?
        if(!$bitRight && $this->getParam("userid") != ""  && $this->getParam("modus") == "selfedit") {
            if($this->getParam("userid") == $this->objSession->getUserID() && _user_selfedit_ == "true") {
                $bitRight = true;
                $bitSelfedit = true;
            }
        }

        if($bitRight) {
            if(($this->getParam("username") != "" && $this->getParam("username") != " ") || $bitSelfedit) {
                if($this->getParam("passwort") == $this->getParam("passwort2")) {
                    //Email-Vorhanden?
                    if(checkEmailaddress($this->getParam("email"))) {
                        //Saving to database
                        $intActive = (($this->getParam("aktiv")) != "" && $this->getParam("aktiv") == "checked") ?  1 :  0;
                        $intAdmin = (($this->getParam("admin")) != "" && $this->getParam("admin") == "checked") ?  1 :  0;
                        $intPortal = (($this->getParam("portal")) != "" && $this->getParam("portal") == "checked") ?  1 :  0;
                        $objUser = new class_modul_user_user($this->getParam("userid"));
                        if($this->getParam("passwort") == "" || $this->getParam("passwort") == " ") {

                            if(!$bitSelfedit) {
                                $objUser->setStrUsername($this->getParam("username"));
                                $objUser->setStrEmail($this->getParam("email"));
                                $objUser->setStrForename($this->getParam("vorname"));
                                $objUser->setStrName($this->getParam("nachname"));
                                $objUser->setStrStreet($this->getParam("strasse"));
                                $objUser->setStrPostal($this->getParam("plz"));
                                $objUser->setStrCity($this->getParam("ort"));
                                $objUser->setStrTel($this->getParam("tel"));
                                $objUser->setStrMobile($this->getParam("handy"));
                                $objUser->setStrDate($this->getParam("gebdatum"));
                                $objUser->setIntActive($intActive);
                                $objUser->setIntAdmin($intAdmin);
                                $objUser->setIntPortal($intPortal);
                                $objUser->setStrAdminskin($this->getParam("skin"));
                                $objUser->setStrAdminlanguage($this->getParam("language"));

                            }
                            else {
                                $objUser->setStrEmail($this->getParam("email"));
                                $objUser->setStrForename($this->getParam("vorname"));
                                $objUser->setStrName($this->getParam("nachname"));
                                $objUser->setStrStreet($this->getParam("strasse"));
                                $objUser->setStrPostal($this->getParam("plz"));
                                $objUser->setStrCity($this->getParam("ort"));
                                $objUser->setStrTel($this->getParam("tel"));
                                $objUser->setStrMobile($this->getParam("handy"));
                                $objUser->setStrDate($this->getParam("gebdatum"));
                                $objUser->setStrAdminskin($this->getParam("skin"));
                                $objUser->setStrAdminlanguage($this->getParam("language"));
                            }
                        }
                        else {
                            if(!$bitSelfedit) {

                                $objUser->setStrUsername($this->getParam("username"));
                                $objUser->setStrPass($this->getParam("passwort"));
                                $objUser->setStrEmail($this->getParam("email"));
                                $objUser->setStrForename($this->getParam("vorname"));
                                $objUser->setStrName($this->getParam("nachname"));
                                $objUser->setStrStreet($this->getParam("strasse"));
                                $objUser->setStrPostal($this->getParam("plz"));
                                $objUser->setStrCity($this->getParam("ort"));
                                $objUser->setStrTel($this->getParam("tel"));
                                $objUser->setStrMobile($this->getParam("handy"));
                                $objUser->setStrDate($this->getParam("gebdatum"));
                                $objUser->setIntActive($intActive);
                                $objUser->setIntAdmin($intAdmin);
                                $objUser->setIntPortal($intPortal);
                                $objUser->setStrAdminskin($this->getParam("skin"));
                                $objUser->setStrAdminlanguage($this->getParam("language"));
                            }
                            else {
                                $objUser->setStrPass($this->getParam("passwort"));
                                $objUser->setStrEmail($this->getParam("email"));
                                $objUser->setStrForename($this->getParam("vorname"));
                                $objUser->setStrName($this->getParam("nachname"));
                                $objUser->setStrStreet($this->getParam("strasse"));
                                $objUser->setStrPostal($this->getParam("plz"));
                                $objUser->setStrCity($this->getParam("ort"));
                                $objUser->setStrTel($this->getParam("tel"));
                                $objUser->setStrMobile($this->getParam("handy"));
                                $objUser->setStrDate($this->getParam("gebdatum"));
                                $objUser->setStrAdminskin($this->getParam("skin"));
                                $objUser->setStrAdminlanguage($this->getParam("language"));
                            }
                        }

                        if($objUser->updateObjectToDb()) {
                        	//Reset the admin-skin cookie to force the new skin
                        	require_once(_systempath_."/class_cookie.php");
				    	    $objCookie = new class_cookie();
				    	    //flush the db-cache
				    	    $this->objDB->flushQueryCache();
				    	    $this->objSession->resetUser();
				    	    //and update the cookie
				    		$objCookie->setCookie("adminskin", $this->objSession->getAdminSkin(false));
				    		//update language set before
                            $objCookie->setCookie("adminlanguage", $this->objSession->getAdminLanguage(false));

                            return "";
                        }
                        else
                            throw new class_exception($this->getText("user_fehler"), class_exception::$level_ERROR);
                    }
                    else {
                        return $this->objToolkit->warningBox($this->getText( "user_fehler_mail"));
                    }
                }
                else {
                    return $this->objToolkit->warningBox($this->getText( "user_fehler_pass"));
                }
            }
            else {
                return $this->objToolkit->warningBox($this->getText( "user_fehler_name"));
            }
        }
        else
            return $this->getText( "fehler_recht");
    }


    /**
	 * Deltes a user from the database
	 *
	 * @return bool
	 */
    private function actionDeleteFinal() {
        if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strUserid = $this->getParam("userid");
            //The user itself
            $objUser = new class_modul_user_user($strUserid);
            $objUser->deleteUser();
            //Relationships
            class_modul_user_group::deleteAllUserMemberships($objUser);
            return true;
        }
        return false;
    }

//*"*****************************************************************************************************
//--group-managment--------------------------------------------------------------------------------------

    /**
	 * Returns the list of all current groups
	 *
	 * @return string
	 */
    private function actionGroupList() {
        $strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strReturn = $this->objToolkit->listHeader();
            $arrGroups = class_modul_user_group::getAllGroups();
            $intI = 0;
            foreach($arrGroups as $objSingleGroup) {
                $strAction = "";
                if($objSingleGroup->getSystemid() != _guests_group_id_  && $objSingleGroup->getSystemid() != _admin_gruppe_id_) {
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("user", "groupedit", "&groupid=".$objSingleGroup->getSystemid(), "", $this->getText("gruppe_bearbeiten"), "icon_pencil.gif"));
                    $strAction .= $this->objToolkit->listDeleteButton($objSingleGroup->getStrName(), $this->getText("gruppe_loeschen_frage"),
                                  getLinkAdminHref($this->arrModule["modul"], "groupdeletefinal", "&groupid=".$objSingleGroup->getSystemid()));
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("user", "groupmember", "&groupid=".$objSingleGroup->getSystemid(), "", $this->getText("gruppe_mitglieder"), "icon_group.gif"));
                }
                else {
                    $strAction .= $this->objToolkit->listButton(getImageAdmin("icon_pencilDisabled.gif", $this->getText("gruppe_bearbeiten_x")));
                    $strAction .= $this->objToolkit->listButton(getImageAdmin("icon_tonDisabled.gif", $this->getText("gruppe_loeschen_x")));
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("user", "groupmember", "&groupid=".$objSingleGroup->getSystemid(), "", $this->getText("gruppe_mitglieder"), "icon_group.gif"));
                }
                
                //get the number of users per group
                $intNrOfUsers = count(class_modul_user_group::getGroupMembers($objSingleGroup->getSystemid()));
                $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_group.gif"), $objSingleGroup->getStrName()." (".$intNrOfUsers.")", $strAction, $intI++);
            }
            if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
            $strReturn .= $this->objToolkit->listRow2Image("","" , getLinkAdmin($this->arrModule["modul"], "groupnew", "", $this->getText("gruppen_anlegen"), $this->getText("gruppen_anlegen"), "icon_blank.gif"), $intI++);
            $strReturn .= $this->objToolkit->listFooter();
        }
        else
        $strReturn .= $this->getText("fehler_recht");

        return $strReturn;
    }

    /**
	 * Edits or creates a group (displays formular)
	 *
	 * @return string
	 */
    private function actionGroupNew() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strTemplateID = $this->objTemplate->readTemplate("/module/modul_user/admin_newgruppe.tpl");

            if($this->getParam("groupid") != "" || $this->getParam("gruppeid") != "") {
                if($this->getParam("groupid") == "" && $this->getParam("gruppeid") != "")
                $this->setParam("groupid", $this->getParam("gruppeid"));
                $strReturn .= $this->objToolkit->getValidationErrors($this);
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "groupsaveedit"));
                $objGroup = new class_modul_user_group($this->getParam("groupid"));
                $strReturn .= $this->objToolkit->formInputText("gruppename", $this->getText("gruppe"), $objGroup->getStrName());
                $strReturn .= $this->objToolkit->formInputHidden("gruppeid", $objGroup->getSystemid());
            }
            else {
                $strReturn .= $this->objToolkit->getValidationErrors($this);
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "groupsave"));
                $strReturn .= $this->objToolkit->formInputText("gruppename", $this->getText("gruppe"), "");
                $strReturn .= $this->objToolkit->formInputHidden("gruppeid");
            }
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
            $strReturn .= $this->objToolkit->formClose();
        }
        else
        $strReturn .= $this->getText("fehler_recht");

        return $strReturn;
    }

    /**
	 * Saves a new group to databse
	 *
	 * @return string "" in case of success
	 */
    private function actionGroupSave() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($this->getParam("gruppename") != "" && $this->getParam("gruppename") != " ") {
                $strName = $this->getParam("gruppename");
                $objGroup = new class_modul_user_group("");
                $objGroup->setStrName($strName);
                if($objGroup->saveObjectToDb())
                    $strReturn .= "";
                else
                    throw new class_exception($this->getText("gruppe_anlegen_fehler"), class_exception::$level_ERROR);
            }
        }
        else
            $strReturn .= $this->getText("fehler_recht");
        return $strReturn;
    }

    /**
	 * Saves an group edited
	 *
	 * @return string "" in case of success
	 */
    private function actionGroupSaveEdit() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($this->getParam("gruppename") != "" && $this->getParam("gruppename") != " ") {
                $objGroup = new class_modul_user_group($this->getParam("gruppeid"));
                $objGroup->setStrName($this->getParam("gruppename"));
                if($objGroup->updateObjectToDb()) {
                    return "";
                }
                else {
                    throw new class_exception($this->getText("gruppe_anlegen_fehler"), class_exception::$level_ERROR);
                }
            }
            else {
                throw new class_exception($this->getText("gruppe_anlegen_fehler_name"), class_exception::$level_ERROR);
            }
        }
        else
            $strReturn .= $this->getText("fehler_recht");
        return $strReturn;
    }

    /**
	 * Returns a list of users beloning to a specified group
	 *
	 * @return string
	 */
    private function actionGroupMember() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($this->getParam("groupid") != "") {
            	$objGroup = new class_modul_user_group($this->getParam("groupid"));
            	$strReturn .= $this->objToolkit->formHeadline($this->getText("group_memberlist")."\"".$objGroup->getStrName()."\"");
            	
                $arrMembers = class_modul_user_group::getGroupMembers($this->getParam("groupid"));
                $strReturn .= $this->objToolkit->listHeader();
                $intI = 0;
                foreach ($arrMembers as $objSingleMember) {
                    $strAction = $this->objToolkit->listDeleteButton($objSingleMember->getStrUsername()." (".$objSingleMember->getStrForename() ." ". $objSingleMember->getStrName() .")"
                                 ,$this->getText("mitglied_loeschen_frage_1")." ".$objGroup->getStrName().$this->getText("mitglied_loeschen_frage_2")
                                 ,getLinkAdminHref($this->arrModule["modul"], "groupmemberdeletefinal", "&groupid=".$objGroup->getSystemid()."&userid=".$objSingleMember->getSystemid()));
                    $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_user.gif"), $objSingleMember->getStrUsername(), $strAction, $intI++);
                }
                $strReturn .= $this->objToolkit->listFooter();
            }
        }
        else
            $strReturn .= $this->getText("fehler_recht");
        return $strReturn;
    }


    /**
	 * Deletes a membership
	 *
	 * @return string "" in case of success
	 */
    private function actionGroupMemberDeleteFinal() {
        $strReturn = "";
        if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"])))	{
            $objGroup = new class_modul_user_group($this->getParam("groupid"));
            if($objGroup->deleteUserFromCurrentGroup(new class_modul_user_user($this->getParam("userid"))))
                return "";
            else
                throw new class_exception($this->getText("mitglied_loeschen_fehler"), class_exception::$level_ERROR);
        }
        else
            $strReturn .= $this->getText("fehler_recht");

        return $strReturn;
    }


    /**
	 * Deletes a group and all memberships
	 *
	 * @return string "" in case of success
	 */
    private function actionGroupDeleteFinal() {
        $strReturn = "";
        if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"]))) {
            //Delete memberships
            $objGroup = new class_modul_user_group($this->getParam("groupid"));
            if($objGroup->deleteAllUsersFromCurrentGroup()) {
                //delete group
                if(class_modul_user_group::deleteGroup($this->getParam("groupid"))) {
                    return "";
                }
                else
                    throw new class_exception($this->getText("gruppe_loeschen_fehler"), class_exception::$level_ERROR);
            }
            else
                $strReturn .= $this->getText("gruppe_loeschen_fehler");
        }
        else
            $strReturn .= $this->getText("fehler_recht");
        return $strReturn;
    }

    /**
	 * Shows a form to manage memberships of a user in groups
	 *
	 * @return unknown
	 */
    private function actionMembership() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            //open the form
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "membershipsave"));
            //Create a list of checkboxes
            $objUser = new class_modul_user_user($this->getParam("userid"));

            $strReturn .= $this->objToolkit->formInputHidden("userid", $this->getParam("userid"));
            $strReturn .= $this->objToolkit->formHeadline($this->getText("user_memberships")."\"".$objUser->getStrUsername()."\"");

            //Collect groups
            $arrGroups = class_modul_user_group::getAllGroups();
            foreach($arrGroups as $objSingleGroup) {
                if($objSingleGroup->isUserMemberInGroup($objUser)) {
                    //user in group, checkbox checked
                    $strReturn .= $this->objToolkit->formInputCheckbox($objSingleGroup->getSystemid(), $objSingleGroup->getStrName(), true);
                }
                else {
                    //User not yet in group, checkbox unchecked
                    $strReturn .= $this->objToolkit->formInputCheckbox($objSingleGroup->getSystemid(), $objSingleGroup->getStrName());
                }
            }
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
            $strReturn .= $this->objToolkit->formClose();
        }
        else
        $strReturn .= $this->getText("fehler_recht");
        return $strReturn;
    }


    /**
	 * Saves the memberships passed by param
	 *
	 * @return string "" in case of success
	 */
    private function actionSaveMembership() {
        $strReturn = "";
        $bitError = false;
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            //Get all Groups
            $arrGroups = class_modul_user_group::getAllGroups();
            //In general, we have the case that a user is in one or two groups, so its ok to delete all memberships and save the new memberships
            //So: Delete old memberships
            class_modul_user_group::deleteAllUserMemberships(new class_modul_user_user($this->getParam("userid")));

            //Searching for groups to enter
            $arrGroupsWanted = array();
            foreach ($arrGroups as $objSingleGroup) {
                if($this->getParam($objSingleGroup->getSystemid()) != "") {
                    $arrGroupsWanted[] = $objSingleGroup->getSystemid();
                }
            }
            class_modul_user_group::addUserToGroups(new class_modul_user_user($this->getParam("userid")), $arrGroupsWanted);
            return "";
        }
        else
            return $this->getText("fehler_recht");
    }


//--Log-Funktion-----------------------------------------------------------------------------------------
    /**
     * returns a list of the last logins
     *
     * @return string
     */
    private function actionLoginLog() {
        $strReturn = "";
        if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
            //fetch log-rows
            include_once(_systempath_."/class_array_section_iterator.php");
		    $objLogbook = new class_modul_user_log();
		    $objArraySectionIterator = new class_array_section_iterator($objLogbook->getLoginLogsCount());
		    $objArraySectionIterator->setIntElementsPerPage(_user_log_nrofrecords_);
		    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
		    $objArraySectionIterator->setArraySection($objLogbook->getLoginLogsSection($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

		    $arrLogs = $objArraySectionIterator->getArrayExtended();

            $strRows = "";
            $arrPageViews = $this->objToolkit->getPageview($arrLogs, (int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1), "user", "loginlog", "", _user_log_nrofrecords_);
            $arrLogs = $arrPageViews["elements"];


            for($intI = 0; $intI < count($arrLogs); $intI++) {
                $arrRows[$intI] = array();
                $arrRows[$intI][]	= $arrLogs[$intI]["user_log_id"];
                $arrRows[$intI][]	= ($arrLogs[$intI]["user_username"] != "" ? $arrLogs[$intI]["user_username"] : $arrLogs[$intI]["user_log_userid"]);
                $arrRows[$intI][]	= timeToString($arrLogs[$intI]["user_log_date"]);
                $arrRows[$intI][]	= ($arrLogs[$intI]["user_log_status"] == 0 ? $this->getText("login_status_0") : $this->getText("login_status_1"));
                $arrRows[$intI][]	= $arrLogs[$intI]["user_log_ip"];
            }

            //Bulding the sourrounding table
            $arrHeader = array();
            $arrHeader[]	= $this->getText("login_nr");
            $arrHeader[]	= $this->getText("login_user");
            $arrHeader[]	= $this->getText("login_datum");
            $arrHeader[]	= $this->getText("login_status");
            $arrHeader[]	= $this->getText("login_ip");
            //and fetch the table
            $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrRows);
            $strReturn .= $arrPageViews["pageview"];
        }
        else
            $strReturn .= $this->getText("fehler_recht");

        return $strReturn;
    }


//--- helpers--------------------------------------------------------------------------------------------

    /**
     * Checks, if two passwords are equal
     *
     * @param string $strPass1
     * @param string $strPass2
     * @return bool
     */
    private function checkPasswords($strPass1, $strPass2) {
        return ($strPass1 == $strPass2 && uniStrlen($strPass1) > 2);
    }

    /**
     * Checks, if a username is existing or not
     *
     * @param string $strName
     * @return bool
     */
    private function checkUsernameNotExisting($strName) {
        $arrUsers = class_modul_user_user::getAllUsersByName($strName);
        return (count($arrUsers) == 0);
    }

    private function checkAdditionalNewData() {
        $bitPass = $this->checkPasswords($this->getParam("passwort"), $this->getParam("passwort2"));
        if(!$bitPass)
            $this->addValidationError("passwort", $this->getText("required_password_equal"));
        $bitUsername = $this->checkUsernameNotExisting($this->getParam("username"));
        if(!$bitUsername)
            $this->addValidationError("username", $this->getText("required_user_existing"));

        return $bitPass && $bitUsername;
    }

    private function checkAdditionalEditData() {
        $bitPass = ($this->getParam("passwort") == $this->getParam("passwort2"));
        if(!$bitPass)
            $this->addValidationError("passwort", $this->getText("required_password_equal"));

        return $bitPass;
    }

} //class_modul_user_admin()
?>