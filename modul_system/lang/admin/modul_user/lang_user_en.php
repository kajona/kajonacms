<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$lang["modul_titel"]				= "Users";
$lang["modul_rechte"]				= "Module permissions";

$lang["user"]						= "User: ";
$lang["user_liste"]					= "All users";
$lang["user_anlegen"]				= "Create user";
$lang["user_bearbeiten"]			= "Edit user";
$lang["user_loeschen_frage"]		= "Do you really want to delete the user &quot;<b>%%element_name%%</b>&quot;?";
$lang["user_loeschen_erfolg"]		= "User was deleted sucessfully";
$lang["user_loeschen_fehler"]		= "An error occured while deleting user";
$lang["user_zugehoerigkeit"]		= "Group assignments";
$lang["user_erfolg"]				= "User was saved successfully";
$lang["user_fehler"]				= "An error occured while saving the user";
$lang["user_fehler_mail"]			= "Pleae enter a valid mail-address <a href=\"javascript:history.back(-1)\">back</a>";
$lang["user_fehler_pass"]			= "Passwords provided are different <a href=\"javascript:history.back(-1)\">back</a>";
$lang["user_fehler_name"]			= "Please enter an username <a href=\"javascript:history.back(-1)\">back</a>";
$lang["user_fehler_namedoppelt"]	= "The username already exits, please choose another one <a href=\"javascript:history.back(-1)\">back</a>";
$lang["fehler_speichern"]			= "An error occured while saving profile!";

$lang["user_logins"]                = "Logins:";
$lang["user_lastlogin"]             = "Last Login:";

$lang["permissions_header"]         = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Logs", 5 => "", 6 => "", 7 => "", 8 => "");

$lang["gruppen"]					= "Groups: ";
$lang["gruppen_liste"]				= "All groups";
$lang["gruppen_anlegen"]			= "Create group";
$lang["gruppe_anlegen_erfolg"]		= "Group was saved successfully";
$lang["gruppe_anlegen_fehler"]		= "An error occured while saving group";
$lang["gruppe_anlegen_fehler_name"]	= "Please provide a group name";
$lang["gruppe_bearbeiten"]			= "Edit group";
$lang["gruppe_bearbeiten_x"]		= "This group can\'t be edited";
$lang["gruppe_loeschen_frage"]		= "Do you really want to delete the group &quot;<b>%%element_name%%</b>&quot;?";
$lang["gruppe_loeschen_erfolg"]		= "The group was deleted sucessfully";
$lang["gruppe_loeschen_fehler"]		= "An error occured while deleting group";
$lang["gruppe_loeschen_x"]			= "This group can\'t be deleted";
$lang["gruppe_mitglieder"]			= "Show members of the group";

$lang["group_memberlist"]  			= "Members of group ";
$lang["mitglied_loeschen"]			= "Delete user from group";
$lang["mitglied_loeschen_frage_1"]	= " Do you really want to delete the user &quot;<b>%%element_name%%</b>&quot; from the group";
$lang["mitglied_loeschen_frage_2"]	= " ?";
$lang["mitglied_loeschen_erfolg"]	= "User was deleted successfully from group";
$lang["mitglied_loeschen_fehler"]	= "An error occured while deleting user from group";
$lang["mitglied_speichern_erfolg"] 	= "Assignments saved successfully";
$lang["mitglied_speichern_fehler"] 	= "An error occured while saving assignments";

$lang["log"]						= "Logs: ";
$lang["loginlog"]					= "Login log";
$lang["login_nr"]					= "#";
$lang["login_user"]					= "User";
$lang["login_datum"]				= "Date";
$lang["login_status"]				= "Status";
$lang["login_status_0"]				= "Login Error";
$lang["login_status_1"]				= "Login OK";
$lang["login_ip"]					= "IP-Address";

//Form-Texts
$lang["user_personaldata"]			= "Persönliche Daten";
$lang["username"]					= "Username:";
$lang["passwort"]					= "Password:";
$lang["passwort2"]					= "Password:";
$lang["email"]						= "Email:";
$lang["vorname"]					= "Forename:";
$lang["nachname"]					= "Surname:";
$lang["strasse"]					= "Street:";
$lang["plz"]						= "Postal:";
$lang["ort"]						= "City:";
$lang["tel"]						= "Phone:";
$lang["handy"]						= "Mobile:";
$lang["gebdatum"]					= "Date of birth:";
$lang["user_system"]				= "Systemeinstellungen";
$lang["aktiv"]						= "Active:";
$lang["admin"]						= "Admin login:";
$lang["portal"]						= "Portal login:";
$lang["submit"]						= "Save";
$lang["skin"]						= "Admin skin:";
$lang["gruppe"]						= "Group title:";
$lang["language"]                   = "Admin language:";

//Form-Texts Memberships
$lang["user_memberships"]			= "Group assignments of user ";

$lang["fehler_recht"]				= "Not enough permissions to perform this action";

$lang["_guests_group_id_"]         = "ID of the guest group:";
$lang["_admin_gruppe_id_"]          = "ID of the admin group:";
$lang["_user_log_nrofrecords_"]          = "Number of rows:";
$lang["_user_log_nrofrecords_hint"]      = "Defines the number of rows to be shown in the login logfile.";
$lang["_admin_skin_default_"]       = "Default admin skin:";
$lang["_user_selfedit_"]            = "Own profile:";
$lang["_user_selfedit_hint_"]       = "Defines, if the user is allowed to change its own profile.";

$lang["user_active"]                = "Change status (is active)";
$lang["user_inactive"]              = "Change statis (is inactive)";

$lang["required_username"]          = "Username";
$lang["required_email"]             = "Email";
$lang["required_passwort"]          = "Password";
$lang["required_passwort2"]         = "Password confirmation";
$lang["required_gruppename"]        = "Groupname";
$lang["required_user_existing"]     = "Username already exists";
$lang["required_password_equal"]    = "Passwords doesn't match";

$lang["lang_de"]                    = "German";
$lang["lang_en"]                    = "English";

$lang["login_statusTitle"]          = "Logged in as:";
$lang["login_profileTitle"]         = "Edit profile";
$lang["login_logoutTitle"]          = "Log out";
$lang["login_dashboard"]            = "Welcome page";

$lang["login_loginTitle"]           = "Login";
$lang["login_loginUser"]            = "Username";
$lang["login_loginPass"]            = "Password";
$lang["login_loginButton"]          = "Login";
$lang["login_loginError"]           = "Unfortunately, the provided login data was invalid. Please check your username and password.<br /><br />If you still can't log in, contact your system-administrator.";
$lang["login_loginJsInfo"]          = "Please allow JavaScript for this site an reload the current page to be able to use all functionalities.";
$lang["login_loginCookiesInfo"]     = "Please allow Cookies for this site an reload the current page to be able to use all functionalities.";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$lang["quickhelp_list"]				= "All useres created are listed in this view.<br />You can edit the users profile and the group assignments.";
$lang["quickhelp_edit"]				= "This form is used to edit or create a new user.<br />If the user is set active, the account is allowed to login in general.<br />In addtion, the area allowed to access must be set.";
$lang["quickhelp_new"]				= "This form is used to edit or create a new user.<br />If the user is set active, the account is allowed to login in general.<br />In addtion, the area allowed to access must be set.";
$lang["quickhelp_membership"]		= "Using this list, the group assignments can be edited.<br />A user can be added to a group or can be removed from a group.<br />NOTE: A user not being member of any of the groups is not allwed to log in!";
$lang["quickhelp_grouplist"]		= "Groups available are listet in this view.<br />The groups of the guests and the admins can\'t be deleted, since they are required for the system to work properly.";
$lang["quickhelp_groupnew"]			= "To create a group, you just have to specify a title for the group.";
$lang["quickhelp_groupsave"]		= "To create a group, you just have to specify a title for the group.";
$lang["quickhelp_groupmember"]		= "All members of a group are listet right here. To end the membership of a user, the membership can be deleted. If you want a user to become a member of the group, you have to use the groupdialog.";
$lang["quickhelp_loginlog"]			= "The login log shows a list of all successfull an unsuccessfull login attempts. Using this logfile, you are able to see who logged into the system.";
?>