<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Users";
$text["modul_rechte"]				= "Module permissions";

$text["user"]						= "User: ";
$text["user_liste"]					= "All users";
$text["user_anlegen"]				= "Create user";
$text["user_bearbeiten"]			= "Edit user";
$text["user_loeschen_frage"]		= "Do you really want to delete the user &quot;<b>%%element_name%%</b>&quot;?";
$text["user_loeschen_erfolg"]		= "User was deleted sucessfully";
$text["user_loeschen_fehler"]		= "An error occured while deleting user";
$text["user_zugehoerigkeit"]		= "Group assignments";
$text["user_erfolg"]				= "User was saved successfully";
$text["user_fehler"]				= "An error occured while saving the user";
$text["user_fehler_mail"]			= "Pleae enter a valid mail-address <a href=\"javascript:history.back(-1)\">back</a>";
$text["user_fehler_pass"]			= "Passwords provided are different <a href=\"javascript:history.back(-1)\">back</a>";
$text["user_fehler_name"]			= "Please enter an username <a href=\"javascript:history.back(-1)\">back</a>";
$text["user_fehler_namedoppelt"]	= "The username already exits, please choose another one <a href=\"javascript:history.back(-1)\">back</a>";
$text["fehler_speichern"]			= "An error occured while saving profile!";

$text["user_logins"]                = "Logins:";
$text["user_lastlogin"]             = "Last Login:";

$text["permissions_header"]         = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Logs", 5 => "", 6 => "", 7 => "", 8 => "");

$text["gruppen"]					= "Groups: ";
$text["gruppen_liste"]				= "All groups";
$text["gruppen_anlegen"]			= "Create group";
$text["gruppe_anlegen_erfolg"]		= "Group was saved successfully";
$text["gruppe_anlegen_fehler"]		= "An error occured while saving group";
$text["gruppe_anlegen_fehler_name"]	= "Please provide a group name";
$text["gruppe_bearbeiten"]			= "Edit group";
$text["gruppe_bearbeiten_x"]		= "This group can\'t be edited";
$text["gruppe_loeschen_frage"]		= "Do you really want to delete the group &quot;<b>%%element_name%%</b>&quot;?";
$text["gruppe_loeschen_erfolg"]		= "The group was deleted sucessfully";
$text["gruppe_loeschen_fehler"]		= "An error occured while deleting group";
$text["gruppe_loeschen_x"]			= "This group can\'t be deleted";
$text["gruppe_mitglieder"]			= "Show members of the group";

$text["group_memberlist"]  			= "Members of group ";
$text["mitglied_loeschen"]			= "Delete user from group";
$text["mitglied_loeschen_frage_1"]	= " Do you really want to delete the user &quot;<b>%%element_name%%</b>&quot; from the group";
$text["mitglied_loeschen_frage_2"]	= " ?";
$text["mitglied_loeschen_erfolg"]	= "User was deleted successfully from group";
$text["mitglied_loeschen_fehler"]	= "An error occured while deleting user from group";
$text["mitglied_speichern_erfolg"] 	= "Assignments saved successfully";
$text["mitglied_speichern_fehler"] 	= "An error occured while saving assignments";

$text["log"]						= "Logs: ";
$text["loginlog"]					= "Login log";
$text["login_nr"]					= "#";
$text["login_user"]					= "User";
$text["login_datum"]				= "Date";
$text["login_status"]				= "Status";
$text["login_status_0"]				= "Login Error";
$text["login_status_1"]				= "Login OK";
$text["login_ip"]					= "IP-Address";

//Form-Texts
$text["user_personaldata"]			= "Persönliche Daten";
$text["username"]					= "Username:";
$text["passwort"]					= "Password:";
$text["passwort2"]					= "Password:";
$text["email"]						= "Email:";
$text["vorname"]					= "Forename:";
$text["nachname"]					= "Surname:";
$text["strasse"]					= "Street:";
$text["plz"]						= "Postal:";
$text["ort"]						= "City:";
$text["tel"]						= "Phone:";
$text["handy"]						= "Mobile:";
$text["gebdatum"]					= "Date of birth:";
$text["user_system"]				= "Systemeinstellungen";
$text["aktiv"]						= "Active:";
$text["admin"]						= "Admin login:";
$text["portal"]						= "Portal login:";
$text["submit"]						= "Save";
$text["skin"]						= "Admin skin:";
$text["gruppe"]						= "Group title:";
$text["language"]                   = "Admin language:";

//Form-Texts Memberships
$text["user_memberships"]			= "Group assignments of user ";

$text["fehler_recht"]				= "Not enough permissions to perform this action";

$text["_gaeste_gruppe_id_"]         = "ID of the guest group:";
$text["_admin_gruppe_id_"]          = "ID of the admin group:";
$text["_user_log_anzahl_"]          = "Number of rows:";
$text["_user_log_anzahl_hint"]      = "Defines the number of rows to be shown in the login logfile.";
$text["_admin_skin_default_"]       = "Default-skin when loading the admin-area:";
$text["_user_selfedit_"]            = "Own profile:";
$text["_user_selfedit_hint_"]       = "Defines, if the user is allowed to change its own profile.";

$text["user_active"]                = "Change status (is active)";
$text["user_inactive"]              = "Change statis (is inactive)";

$text["required_username"]          = "Username";
$text["required_email"]             = "Email";
$text["required_passwort"]          = "Password";
$text["required_passwort2"]         = "Password confirmation";
$text["required_gruppename"]        = "Groupname";
$text["required_user_existing"]     = "Username already exists";
$text["required_password_equal"]    = "Passwords doesn't match";

$text["lang_de"]                    = "German";
$text["lang_en"]                    = "English";

$text["login_statusTitle"]          = "Logged in as:";
$text["login_profileTitle"]         = "Edit profile";
$text["login_logoutTitle"]          = "Log out";
$text["login_dashboard"]            = "Welcome page";

$text["login_loginTitle"]           = "Login";
$text["login_loginUser"]            = "Username";
$text["login_loginPass"]            = "Password";
$text["login_loginButton"]          = "Login";
$text["login_loginError"]           = "Unfortunately, the provided login data was invalid. Please check your username and password.<br /><br />If you still can't log in, contact your system-administrator.";
$text["login_loginJsInfo"]          = "Please allow JavaScript for this site an reload the current page to be able to use all functionalities.";
$text["login_loginCookiesInfo"]     = "Please allow Cookies for this site an reload the current page to be able to use all functionalities.";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]				= "All useres created are listed in this view.<br />You can edit the users profile and the group assignments.";
$text["quickhelp_edit"]				= "This form is used to edit or create a new user.<br />If the user is set active, the account is allowed to login in general.<br />In addtion, the area allowed to access must be set.";
$text["quickhelp_new"]				= "This form is used to edit or create a new user.<br />If the user is set active, the account is allowed to login in general.<br />In addtion, the area allowed to access must be set.";
$text["quickhelp_membership"]		= "Using this list, the group assignments can be edited.<br />A user can be added to a group or can be removed from a group.<br />NOTE: A user not being member of any of the groups is not allwed to log in!";
$text["quickhelp_grouplist"]		= "Groups available are listet in this view.<br />The groups of the guests and the admins can\'t be deleted, since they are required for the system to work properly.";
$text["quickhelp_groupnew"]			= "To create a group, you just have to specify a title for the group.";
$text["quickhelp_groupsave"]		= "To create a group, you just have to specify a title for the group.";
$text["quickhelp_groupmember"]		= "All members of a group are listet right here. To end the membership of a user, the membership can be deleted. If you want a user to become a member of the group, you have to use the groupdialog.";
$text["quickhelp_loginlog"]			= "The login log shows a list of all successfull an unsuccessfull login attempts. Using this logfile, you are able to see who logged into the system.";
?>