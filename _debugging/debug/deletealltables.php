<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Delete all tables                                                             |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

if(issetPost("dodelete")) {
    $strUsername = getPost("username");
    $strPassword = getPost("password");

    $objUsersource = new class_module_user_sourcefactory();
    $objUser = $objUsersource->getUserByUsername($strUsername);
    echo "Authenticating user...\n";
    if($objUsersource->authenticateUser($strUsername, $strPassword)) {
        echo " ... authenticated.\n";
        $arrGroupIds = $objUser->getArrGroupIds();
        if(in_array(class_module_system_setting::getConfigValue("_admins_group_id_"), $arrGroupIds)) {
            echo "User is member of admin-group.\n";

            $arrTables = class_carrier::getInstance()->getObjDB()->getTables();
            foreach($arrTables as $strOneTable) {
                $strQuery = "DROP TABLE " . $strOneTable;
                echo " executing " . $strQuery . "\n";
                class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
            }

        }
        else {
            echo "User is not a member of the admin-group!\n";
        }
    }
    else {
        echo "Authentication failed!\n";
    }

}
else {

    $arrTables = class_carrier::getInstance()->getObjDB()->getTables();

    echo "ATTENTION: This script will delete all tables of you current installation.\n\n";
    echo "To perform this action, you have to provide the credentials of a member of the admin-group.\n\n";

    echo "<form method=\"post\">";
    echo "Username: <input type='text' name='username'><br />";
    echo "Password: <input type='password' name='password'>";
    echo "<input type=\"hidden\" name=\"dodelete\" value=\"1\" /><br /><br />";
    echo "<input type=\"submit\" value=\"Delete tables\" />";
    echo "</form>";

    echo "Currently, this will include the following tables:";
    echo "\n -" . implode("\n -", $arrTables);

}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


