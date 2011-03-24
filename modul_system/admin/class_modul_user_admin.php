<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * This class provides the user and groupmanagement
 *
 * @package modul_system
 */
class class_modul_user_admin extends class_admin implements interface_admin {

    //languages, the admin area could display (texts)
    protected $arrLanguages = array();

    /**
	 * Constructor
	 *
	 */
    public function __construct() {
        $arrModul = array();
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
            else if($strAction == "saveedit") {
                if($this->validateForm() & $this->checkAdditionalEditData()) {
                    $strReturn = $this->actionSaveEdit();
                    if($strReturn == "")
                        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
                }
                else {
                    $strReturn = $this->actionNew("edit");
                }
            }
            else if($strAction == "membershipsave") {
                $strReturn = $this->actionSaveMembership();
                if($strReturn == "")
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
            }
            else
                $strReturn = parent::action($strAction);

        }
        catch (class_exception $objException) {
            $objException->processException();
            $strReturn .= "An internal error occured: ".$objException->getMessage();
        }

        $this->strOutput = $strReturn;
    }

    protected function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("modul_rechte"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("user_liste"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "new", "", $this->getText("user_anlegen"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "groupList", "", $this->getText("gruppen_liste"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "groupNew", "", $this->getText("gruppen_anlegen"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "loginLog", "", $this->getText("loginlog"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    public function getRequiredFields() {
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
    protected function actionList() {
        $strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

            $objArraySectionIterator = new class_array_section_iterator(class_modul_user_user::getNumberOfUsers());
            $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_modul_user_user::getAllUsers($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

    		$arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "user", "list");
            $arrUsers = $arrPageViews["elements"];

            $strReturn = $this->objToolkit->listHeader();


            $intI = 0;
            foreach ($arrUsers as $objOneUser) 	{
                $strActions = "";
                if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
                    $strActions .= $this->objToolkit->listButton(getLinkAdmin("user", "edit", "&userid=".$objOneUser->getSystemid(), "", $this->getText("user_bearbeiten"), "icon_pencil.gif"));
                if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
                    $strActions .= $this->objToolkit->listButton(getLinkAdmin("user", "membership", "&userid=".$objOneUser->getSystemid(), "", $this->getText("user_zugehoerigkeit"), "icon_group.gif"));

                if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])) && checkEmailaddress($objOneUser->getStrEmail()))
                    $strActions .= $this->objToolkit->listButton(getLinkAdmin("user", "sendPassword", "&userid=".$objOneUser->getSystemid(), "", $this->getText("user_password_resend"), "icon_mail.gif"));

                if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"])))
                    $strActions .= $this->objToolkit->listDeleteButton($objOneUser->getStrUsername(). " (".$objOneUser->getStrForename()." ".$objOneUser->getStrName() .")", $this->getText("user_loeschen_frage"),
                                   getLinkAdminHref($this->arrModule["modul"], "deleteFinal", "&userid=".$objOneUser->getSystemid()));
                //new 2.1: the status icon
                if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
                    if($objOneUser->getIntActive() == 1)
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("user", "status", "&userid=".$objOneUser->getSystemid(), "", $this->getText("user_active"), "icon_enabled.gif"));
                    else
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("user", "status", "&userid=".$objOneUser->getSystemid(), "", $this->getText("user_inactive"), "icon_disabled.gif"));
                }
                if($this->objRights->rightRight($this->getModuleSystemid($this->arrModule["modul"])))
                    $strCenter = $this->getText("user_logins")." ".$objOneUser->getIntLogins()." ".$this->getText("user_lastlogin")." ".timeToString($objOneUser->getIntLastLogin());
                else
                    $strCenter = "";
                
                $strReturn .= $this->objToolkit->listRow3($objOneUser->getStrUsername(). " (".$objOneUser->getStrForename() . " " . $objOneUser->getStrName().")", $strCenter, $strActions, getImageAdmin("icon_user.gif"), $intI++);
            }
            //And one row to create a new one
            if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
                $strReturn .= $this->objToolkit->listRow3("", "", $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "new", "", $this->getText("user_anlegen"), $this->getText("user_anlegen"), "icon_blank.gif")), "", $intI++);
            $strReturn .= $this->objToolkit->listFooter().$arrPageViews["pageview"];
        }
        else
            $strReturn .= $this->getText("fehler_recht");
        return $strReturn;
    }

    /**
     * Shows a form in order to start the process of resetting a users password.
     * The step wil be completed by an email, containing a temporary password and a confirmation link.
     * 
     * @return string
     */
    protected function actionSendPassword() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {

            $objUser = new class_modul_user_user($this->getParam("userid"));

            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "sendPasswordFinal"));
            $strReturn .= $this->objToolkit->getTextRow($this->getText("user_resend_password_hint"));
            $strReturn .= $this->objToolkit->formTextRow($this->getText("username")." ".$objUser->getStrUsername());
            $strReturn .= $this->objToolkit->formTextRow($this->getText("email")." ".$objUser->getStrEmail());
            $strReturn .= $this->objToolkit->formInputHidden("userid", $this->getParam("userid"));
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
            $strReturn .= $this->objToolkit->formClose();
        }
        else
            $strReturn .= $this->getText("fehler_recht");
        return $strReturn;
    }

    protected function actionSendPasswordFinal() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            $objUser = new class_modul_user_user($this->getParam("userid"));

            //add a one-time token and reset the password
            $strToken = generateSystemid();

            $objUser->setStrPass("");
            $objUser->setStrAuthcode($strToken);
            $objUser->updateObjectToDb();

            $strActivationLink = getLinkAdminHref("login", "pwdReset", "&systemid=".$objUser->getSystemid()."&authcode=".$strToken, false);

            $objMail = new class_mail();
            $objMail->addTo($objUser->getStrEmail());
            $objMail->setSubject($this->getText("user_password_resend_subj"));
            $objMail->setText($this->getText("user_password_resend_body").$strActivationLink);

            $objMail->sendMail();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
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
    protected function actionStatus() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            $objUser = new class_modul_user_user($this->getParam("userid"));
            if($objUser->getIntActive() == 1)
                $objUser->setIntActive(0);
            else
                $objUser->setIntActive(1);
            $objUser->setStrPass("");
            if($objUser->updateObjectToDb())
                $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
            else
                throw new class_exception("Error updating user ".$this->getParam("userid"), class_exception::$level_ERROR);

            
        }
        else
            $strReturn .= $this->getText("fehler_recht");

        return $strReturn;
    }

    protected function actionEdit() {
        return $this->actionNew("edit");
    }
    /**
	 * Creates a new user or edits a already existing one
	 *
	 * @return string
	 */
    protected function actionNew($strAction = "new") {
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
            if($strAction == "new") {
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "save"));
                $strReturn .= $this->objToolkit->getValidationErrors($this, "save");
            }
            else {
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveedit"));
                $strReturn .= $this->objToolkit->getValidationErrors($this, "saveedit");
            }

            if($this->getParam("userid") != "") {
                $objUser = new class_modul_user_user($this->getParam("userid"));

                //Form filled with the data
                $strReturn .= $this->objToolkit->formHeadline($this->getText("user_personaldata"));
                if(!$bitSelf)
                    $strReturn .= $this->objToolkit->formInputText("username", $this->getText("username"), ($this->getParam("username") != "" ? $this->getParam("username") : $objUser->getStrUsername()) );
                $strReturn .= $this->objToolkit->formInputPassword("passwort", $this->getText("passwort"));
                $strReturn .= $this->objToolkit->formInputPassword("passwort2", $this->getText("passwort2"));
                $strReturn .= $this->objToolkit->formInputText("email", $this->getText("email"), ($this->getParam("email") != "" ? $this->getParam("email") : $objUser->getStrEmail() ));
                $strReturn .= $this->objToolkit->formInputText("vorname", $this->getText("vorname") , ($this->getParam("vorname") != "" ? $this->getParam("vorname") : $objUser->getStrForename() ));
                $strReturn .= $this->objToolkit->formInputText("nachname", $this->getText("nachname"), ($this->getParam("nachname") != "" ? $this->getParam("nachname") : $objUser->getStrName() ));
                $strReturn .= $this->objToolkit->formInputText("strasse", $this->getText("strasse"), ($this->getParam("strasse") != "" ? $this->getParam("strasse") : $objUser->getStrStreet() ));
                $strReturn .= $this->objToolkit->formInputText("plz", $this->getText("plz"), ($this->getParam("plz") != "" ? $this->getParam("plz") : $objUser->getStrPostal()));
                $strReturn .= $this->objToolkit->formInputText("ort", $this->getText("ort"), ($this->getParam("ort") != "" ? $this->getParam("ort") : $objUser->getStrCity()));
                $strReturn .= $this->objToolkit->formInputText("tel", $this->getText("tel"), ($this->getParam("tel") != "" ? $this->getParam("tel") : $objUser->getStrTel()));
                $strReturn .= $this->objToolkit->formInputText("handy", $this->getText("handy"), ($this->getParam("handy") != "" ? $this->getParam("handy") : $objUser->getStrMobile() ));

                //Create the matching date
                $objDate = null;
                if($objUser->getLongDate() > 0)
                    $objDate = new class_date($objUser->getLongDate());

                $strReturn .= $this->objToolkit->formDateSingle("gebdatum", $this->getText("gebdatum"), $objDate);//("gebdatum", $this->getText("gebdatum"), ($this->getParam("gebdatum") != "" ? $this->getParam("gebdatum") : $objUser->getStrDate() ));
                $strReturn .= $this->objToolkit->formHeadline($this->getText("user_system"));
                $strReturn .= $this->objToolkit->formInputDropdown("skin", $arrSkins, $this->getText("skin"),   ($this->getParam("skin") != "" ? $this->getParam("skin") :     ($objUser->getStrAdminskin() != "" ? $objUser->getStrAdminskin() : _admin_skin_default_)   )  );
                $strReturn .= $this->objToolkit->formInputDropdown("language", $arrLang, $this->getText("language"), ($this->getParam("language") != "" ? $this->getParam("language") : $objUser->getStrAdminlanguage() ));
                if(!$bitSelf) {
                    $strReturn .= $this->objToolkit->formInputCheckbox("adminlogin", $this->getText("admin"), ( issetPost("skin") ? ($this->getParam("adminlogin") != "" ? true : false ) :  $objUser->getIntAdmin() ));
                    $strReturn .= $this->objToolkit->formInputCheckbox("portal", $this->getText("portal"), ( issetPost("skin") ? ($this->getParam("portal") != "" ? true : false) : $objUser->getIntPortal() ));
                    $strReturn .= $this->objToolkit->formInputCheckbox("aktiv", $this->getText("aktiv"), ( issetPost("skin") ?  ($this->getParam("aktiv") != "" ? true : false ) : $objUser->getIntActive() ));
                }
                $strReturn .= $this->objToolkit->formInputHidden("userid", $objUser->getSystemid());
                if($bitSelf)
                    $strReturn .= $this->objToolkit->formInputHidden("modus", "selfedit");
            }
            else {
                //Blank form
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
                $strReturn .= $this->objToolkit->formDateSingle("gebdatum", $this->getText("gebdatum"), null);
                $strReturn .= $this->objToolkit->formHeadline($this->getText("user_system"));
                $strReturn .= $this->objToolkit->formInputDropdown("skin", $arrSkins, $this->getText("skin"), ($this->getParam("skin") != "" ? $this->getParam("skin") : _admin_skin_default_));
                $strReturn .= $this->objToolkit->formInputDropdown("language", $arrLang, $this->getText("language"), $this->getParam("language"));
                $strReturn .= $this->objToolkit->formInputCheckbox("adminlogin", $this->getText("admin"), ($this->getParam("adminlogin") != "" ? true : false ));
                $strReturn .= $this->objToolkit->formInputCheckbox("portal", $this->getText("portal"), ($this->getParam("portal") != "" ? true : false ));
                $strReturn .= $this->objToolkit->formInputCheckbox("aktiv", $this->getText("aktiv"), ($this->getParam("aktiv") != "" ? true : false ));
                $strReturn .= $this->objToolkit->formInputHidden("userid");

            }
            //End the form
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("username");
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
    protected function actionSave() {
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))	{
            if($this->getParam("username") != "" && $this->getParam("username") != " ") {
                if($this->checkUsernameNotExisting($this->getParam("username")) ) {
                    //Passwoerter gleich?
                    if($this->checkPasswords($this->getParam("passwort"), $this->getParam("passwort2"))) {
                        //valid Email address specified?
                        if(checkEmailaddress($this->getParam("email"))) {
                            //Collecting remaining data
                            $intActive = ($this->getParam("aktiv") != "" && $this->getParam("aktiv") == "checked") ?  1 :  0;
                            $intAdmin = ($this->getParam("adminlogin") != "" && $this->getParam("adminlogin") == "checked") ?  1 :  0;
                            $intPortal = ($this->getParam("portal") != "" && $this->getParam("portal") == "checked") ?  1 :  0;


                            //build date
                            $objDate = new class_date();
                            if($this->getParam("gebdatum_year") != "" || $this->getParam("gebdatum_month") != "" || $this->getParam("gebdatum_day") != "" )
                                $objDate->generateDateFromParams("gebdatum", $this->getAllParams());
                            else
                                $objDate->setLongTimestamp("00000000000000");

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
                            $objUser->setLongDate($objDate->getLongTimestamp());
                            $objUser->setStrAdminlanguage($this->getParam("language"));
                            $objUser->setIntActive($intActive);
                            $objUser->setIntAdmin($intAdmin);
                            $objUser->setIntPortal($intPortal);
                            $objUser->setStrAdminskin($this->getParam("skin"));

                            if(!$objUser->updateObjectToDb()) {
                                throw new class_exception($this->getText("fehler_speichern"), class_exception::$level_ERROR);
                            }
                            else {
                                //try to create a default-dashboard
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

    }

    /**
	 * saves a modified user in db, values passed in arrParam
	 *
	 * @return string "" if successfull
	 */
    protected function actionSaveEdit() {
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
                        //build date
                        $objDate = new class_date();
                        if($this->getParam("gebdatum_year") != "" || $this->getParam("gebdatum_month") != "" || $this->getParam("gebdatum_day") != "" )
                                $objDate->generateDateFromParams("gebdatum", $this->getAllParams());
                            else
                                $objDate->setLongTimestamp("00000000000000");

                        $intActive = (($this->getParam("aktiv")) != "" && $this->getParam("aktiv") == "checked") ?  1 :  0;
                        $intAdmin = (($this->getParam("adminlogin")) != "" && $this->getParam("adminlogin") == "checked") ?  1 :  0;
                        $intPortal = (($this->getParam("portal")) != "" && $this->getParam("portal") == "checked") ?  1 :  0;
                        $objUser = new class_modul_user_user($this->getParam("userid"));

                        //init with values independent from states
                        $objUser->setStrEmail($this->getParam("email"));
                        $objUser->setStrForename($this->getParam("vorname"));
                        $objUser->setStrName($this->getParam("nachname"));
                        $objUser->setStrStreet($this->getParam("strasse"));
                        $objUser->setStrPostal($this->getParam("plz"));
                        $objUser->setStrCity($this->getParam("ort"));
                        $objUser->setStrTel($this->getParam("tel"));
                        $objUser->setStrMobile($this->getParam("handy"));
                        $objUser->setLongDate($objDate->getLongTimestamp());
                        $objUser->setStrAdminskin($this->getParam("skin"));
                        $objUser->setStrAdminlanguage($this->getParam("language"));


                        if(trim($this->getParam("passwort")) != "" && $this->getParam("passwort") != " ") {
                            $objUser->setStrPass($this->getParam("passwort"));
                        }


                        if(!$bitSelfedit) {
                            $objUser->setStrUsername($this->getParam("username"));
                            $objUser->setIntActive($intActive);
                            $objUser->setIntAdmin($intAdmin);
                            $objUser->setIntPortal($intPortal);

                        }

                        if($objUser->updateObjectToDb()) {
                        	//Reset the admin-skin cookie to force the new skin
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
                        return $this->objToolkit->warningBox($this->getText("user_fehler_mail"));
                    }
                }
                else {
                    return $this->objToolkit->warningBox($this->getText("user_fehler_pass"));
                }
            }
            else {
                return $this->objToolkit->warningBox($this->getText("user_fehler_name"));
            }
        }
        else
            return $this->getText("fehler_recht");
    }


    /**
	 * Deltes a user from the database
	 *
	 * @return string
	 */
    protected function actionDeleteFinal() {
        if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strUserid = $this->getParam("userid");
            //The user itself
            $objUser = new class_modul_user_user($strUserid);
            $objUser->deleteUser();
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
        }
        return $this->getText("user_loeschen_fehler");
    }

//*"*****************************************************************************************************
//--group-managment--------------------------------------------------------------------------------------

    /**
	 * Returns the list of all current groups
	 *
	 * @return string
	 */
    protected function actionGroupList() {
        $strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

            $objArraySectionIterator = new class_array_section_iterator(class_modul_user_group::getNumberOfGroups());
            $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_modul_user_group::getAllGroups($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

    		$arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "user", "groupList");
            $arrGroups = $arrPageViews["elements"];

            $strReturn = $this->objToolkit->listHeader();

            $intI = 0;
            foreach($arrGroups as $objSingleGroup) {
                $strAction = "";
                if($objSingleGroup->getSystemid() != _guests_group_id_  && $objSingleGroup->getSystemid() != _admins_group_id_) {
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("user", "groupEdit", "&groupid=".$objSingleGroup->getSystemid(), "", $this->getText("gruppe_bearbeiten"), "icon_pencil.gif"));
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("user", "groupMember", "&groupid=".$objSingleGroup->getSystemid(), "", $this->getText("gruppe_mitglieder"), "icon_group.gif"));
                    $strAction .= $this->objToolkit->listDeleteButton($objSingleGroup->getStrName(), $this->getText("gruppe_loeschen_frage"),
                                  getLinkAdminHref($this->arrModule["modul"], "groupdeletefinal", "&groupid=".$objSingleGroup->getSystemid()));
                }
                else {
                    $strAction .= $this->objToolkit->listButton(getImageAdmin("icon_pencilDisabled.gif", $this->getText("gruppe_bearbeiten_x")));
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("user", "groupMember", "&groupid=".$objSingleGroup->getSystemid(), "", $this->getText("gruppe_mitglieder"), "icon_group.gif"));
                    $strAction .= $this->objToolkit->listButton(getImageAdmin("icon_tonDisabled.gif", $this->getText("gruppe_loeschen_x")));
                }

                //get the number of users per group
                $intNrOfUsers = count(class_modul_user_group::getGroupMembers($objSingleGroup->getSystemid()));
                $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_group.gif"), $objSingleGroup->getStrName()." (".$intNrOfUsers.")", $strAction, $intI++);
            }
            if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
            $strReturn .= $this->objToolkit->listRow2Image("","" , getLinkAdmin($this->arrModule["modul"], "groupNew", "", $this->getText("gruppen_anlegen"), $this->getText("gruppen_anlegen"), "icon_blank.gif"), $intI++);
            $strReturn .= $this->objToolkit->listFooter().$arrPageViews["pageview"];
        }
        else
            $strReturn .= $this->getText("fehler_recht");

        return $strReturn;
    }


    protected function actionGroupEdit() {
        return $this->actionGroupNew();
    }

    /**
	 * Edits or creates a group (displays formular)
	 *
	 * @return string
	 */
    protected function actionGroupNew() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {

            if($this->getParam("groupid") != "" || $this->getParam("gruppeid") != "") {
                if($this->getParam("groupid") == "" && $this->getParam("gruppeid") != "")
                $this->setParam("groupid", $this->getParam("gruppeid"));
                $strReturn .= $this->objToolkit->getValidationErrors($this, "groupsaveedit");
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "groupsaveedit"));
                $objGroup = new class_modul_user_group($this->getParam("groupid"));
                $strReturn .= $this->objToolkit->formInputText("gruppename", $this->getText("gruppe"), $objGroup->getStrName());
                $strReturn .= $this->objToolkit->formInputHidden("gruppeid", $objGroup->getSystemid());
            }
            else {
                $strReturn .= $this->objToolkit->getValidationErrors($this, "groupsave");
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "groupsave"));
                $strReturn .= $this->objToolkit->formInputText("gruppename", $this->getText("gruppe"), "");
                $strReturn .= $this->objToolkit->formInputHidden("gruppeid");
            }
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("gruppename");
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
    protected function actionGroupSave() {
        if(!$this->validateForm())
            return $this->actionGroupNew();
            
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($this->getParam("gruppename") != "" && $this->getParam("gruppename") != " ") {
                $strName = $this->getParam("gruppename");
                $objGroup = new class_modul_user_group("");
                $objGroup->setStrName($strName);
                if($objGroup->updateObjectToDb())
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "groupList"));
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
    protected function actionGroupSaveEdit() {
        if(!$this->validateForm())
            return $this->actionGroupNew();
        
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($this->getParam("gruppename") != "" && $this->getParam("gruppename") != " ") {
                $objGroup = new class_modul_user_group($this->getParam("gruppeid"));
                $objGroup->setStrName($this->getParam("gruppename"));
                if($objGroup->updateObjectToDb()) {
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "groupList"));
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
    protected function actionGroupMember() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($this->getParam("groupid") != "") {
            	$objGroup = new class_modul_user_group($this->getParam("groupid"));
            	$strReturn .= $this->objToolkit->formHeadline($this->getText("group_memberlist")."\"".$objGroup->getStrName()."\"");



                $objArraySectionIterator = new class_array_section_iterator(class_modul_user_group::getGroupMembersCount($this->getParam("groupid")));
                $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
                $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
                $objArraySectionIterator->setArraySection(class_modul_user_group::getGroupMembers($this->getParam("groupid"), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

                $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "user", "groupMember", "groupid=".$this->getParam("groupid"));
                $arrMembers = $arrPageViews["elements"];

                $strReturn .= $this->objToolkit->listHeader();
                $intI = 0;
                foreach ($arrMembers as $objSingleMember) {
                    $strAction = $this->objToolkit->listDeleteButton($objSingleMember->getStrUsername()." (".$objSingleMember->getStrForename() ." ". $objSingleMember->getStrName() .")"
                                 ,$this->getText("mitglied_loeschen_frage")
                                 ,getLinkAdminHref($this->arrModule["modul"], "groupmemberdeletefinal", "&groupid=".$objGroup->getSystemid()."&userid=".$objSingleMember->getSystemid()));
                    $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_user.gif"), $objSingleMember->getStrUsername(), $strAction, $intI++);
                }
                $strReturn .= $this->objToolkit->listFooter().$arrPageViews["pageview"];
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
    protected function actionGroupMemberDeleteFinal() {
        $strReturn = "";
        if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"])))	{
            $objGroup = new class_modul_user_group($this->getParam("groupid"));
            if($objGroup->deleteUserFromCurrentGroup(new class_modul_user_user($this->getParam("userid"))))
                $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "groupMember", "groupid=".$this->getParam("groupid")));
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
    protected function actionGroupDeleteFinal() {
        $strReturn = "";
        if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"]))) {
            //Delete memberships
            $objGroup = new class_modul_user_group($this->getParam("groupid"));
            if($objGroup->deleteAllUsersFromCurrentGroup()) {
                //delete group
                if($objGroup->deleteGroup()) {
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "groupList"));
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
    protected function actionMembership() {
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
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            //Get all Groups
            $arrGroups = class_modul_user_group::getAllGroups();
            //In general, we have the case that a user is in one or two groups, so its ok to delete all memberships and save the new memberships
            //So: Delete old memberships
            $objCurUser = new class_modul_user_user($this->getParam("userid"));
            $objCurUser->deleteAllUserMemberships();

            //Searching for groups to enter
            $arrGroupsWanted = array();
            foreach ($arrGroups as $objSingleGroup) {
                if($this->getParam($objSingleGroup->getSystemid()) != "") {
                    $arrGroupsWanted[] = $objSingleGroup->getSystemid();
                }
            }
            class_modul_user_group::addUserToGroups($objCurUser, $arrGroupsWanted);
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
    protected function actionLoginLog() {
        $strReturn = "";
        if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
            //fetch log-rows
		    $objLogbook = new class_modul_user_log();
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


    /**
     * Creates a browser-like view of the users available
     * @return string
     */
    protected function actionUserBrowser() {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        $strReturn = "";
        $strFormElement = $this->getParam("form_element");
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($this->getSystemid() == "") {
                //show groups
                $arrUsers = class_modul_user_group::getAllGroups();
                $strReturn .= $this->objToolkit->listHeader();
                $intI = 0;
                foreach($arrUsers as $objSingleGroup) {
                    $strAction = "";
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("user", "userBrowser", "&form_element=".$this->getParam("form_element")."&systemid=".$objSingleGroup->getSystemid()."&filter=".$this->getParam("filter"), $this->getText("user_browser_show"), $this->getText("user_browser_show"), "icon_folderActionOpen.gif"));

                    if($this->getParam("allowGroup") == "1")
                        $strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("group_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strFormElement."', '".addslashes($objSingleGroup->getStrName())."'], ['".$strFormElement."_id', '".$objSingleGroup->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif"));
                    
                    $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_group.gif"), $objSingleGroup->getStrName(), $strAction, $intI++);

                }
            }
            else {
                //show members of group
                $arrUsers = class_modul_user_group::getGroupMembers($this->getSystemid());
                $strReturn .= $this->objToolkit->listHeader();
                $intI = 0;

                $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "userBrowser", "&form_element=".$this->getParam("form_element")."&filter=".$this->getParam("filter")."&allowGroup=".$this->getParam("allowGroup"), $this->getText("user_list_parent"), $this->getText("user_list_parent"), "icon_folderActionLevelup.gif"), $intI++);
                foreach($arrUsers as $objSingleUser) {

                    $strAction = "";
                    if($this->getParam("filter") == "current" && $objSingleUser->getSystemid() == $this->objSession->getUserID())
                        $strAction .= $this->objToolkit->listButton(getImageAdmin("icon_acceptDisabled.gif"));
                    else
                        $strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("user_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strFormElement."', '".addslashes($objSingleUser->getStrUsername())."'], ['".$strFormElement."_id', '".$objSingleUser->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif"));
                    $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_group.gif"), $objSingleUser->getStrUsername(). "(".$objSingleUser->getStrForename()." ".$objSingleUser->getStrName().")", $strAction, $intI++);

                }
            }

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
    protected function checkPasswords($strPass1, $strPass2) {
        return ($strPass1 == $strPass2 && uniStrlen($strPass1) > 2);
    }

    /**
     * Checks, if a username is existing or not
     *
     * @param string $strName
     * @return bool
     */
    protected function checkUsernameNotExisting($strName) {
        $arrUsers = class_modul_user_user::getAllUsersByName($strName);
        return (count($arrUsers) == 0);
    }

    protected function checkAdditionalNewData() {
        $bitPass = $this->checkPasswords($this->getParam("passwort"), $this->getParam("passwort2"));
        if(!$bitPass)
            $this->addValidationError("passwort", $this->getText("required_password_equal"));
        $bitUsername = $this->checkUsernameNotExisting($this->getParam("username"));
        if(!$bitUsername)
            $this->addValidationError("username", $this->getText("required_user_existing"));

        return $bitPass && $bitUsername;
    }

    protected function checkAdditionalEditData() {
        $bitPass = ($this->getParam("passwort") == $this->getParam("passwort2"));
        if(!$bitPass)
            $this->addValidationError("passwort", $this->getText("required_password_equal"));

        return $bitPass;
    }

}
?>