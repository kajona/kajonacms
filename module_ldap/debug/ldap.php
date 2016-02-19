<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

use Kajona\Ldap\System\Ldap;
use Kajona\System\System\Config;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Checks the connection to the configured ldap servers                          |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

$intI = 0;
foreach(Ldap::getAllInstances() as $objOneLdap) {

    $arrCfg = Config::getInstance("ldap.php")->getConfig($objOneLdap->getIntCfgNr());
    echo "Connecting to ldap at ".$arrCfg["ldap_server"]."\n";

    echo "Searching for bind-user ".$arrCfg["ldap_bind_username"]."\n";
    $arrUser = $objOneLdap->getUserdetailsByName($arrCfg["ldap_bind_username"]);
    var_dump($arrUser);

    echo "Loading user by DN\n";
    $arrUser = $objOneLdap->getUserDetailsByDN($arrUser[0]["identifier"]);
    var_dump($arrUser);


//    echo "Loading groups for user\n";
//    var_dump($objOneLdap->getMembersOfGroup("CN=Entwickler,OU=Gruppen,DC=ad,DC=artemeon,DC=int"));
//    var_dump($objOneLdap->getNumberOfGroupMembers("CN=Entwickler,OU=Gruppen,DC=ad,DC=artemeon,DC=int"));
}

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


