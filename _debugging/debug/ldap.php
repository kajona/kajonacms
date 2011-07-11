<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: gdinfo.php 3530 2011-01-06 12:30:26Z sidler $                                     *
********************************************************************************************************/

header("Content-Type: text/html; charset=utf-8");
require_once("../system/includes.php");


echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| LDAP Connection Tester                                                        |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";

        $objCarrier = class_carrier::getInstance();

echo "|loaded.                                                                        |\n";
echo "+-------------------------------------------------------------------------------+\n\n";


echo "creating connection to ldap...\n";
$objLdap = class_ldap::getInstance();

echo "\nloading members of group CN=Management,OU=Groups,DC=testad1,DC=local...\n";
$arrUsers = $objLdap->getMembersOfGroup("CN=Management,OU=Groups,DC=testad1,DC=local");

echo "  loading details of user...\n";
foreach($arrUsers as $strOneUserDn)
    var_dump($objLdap->getUserDetailsByDN($strOneUserDn));


echo "\nloading members of group CN=Non-Management,OU=Groups,DC=testad1,DC=local...\n";
$arrUsers = $objLdap->getMembersOfGroup("CN=Non-Management,OU=Groups,DC=testad1,DC=local");
var_dump($arrUsers);

echo "\nauthenticating false dummy-user...\n";
var_dump($objLdap->authenticateUser("oo@testad1.local", "oo1"));
echo "authenticating real dummy-user...\n";
var_dump($objLdap->authenticateUser("oo@testad1.local", "oo"));

echo "\nsearching details of given user...\n";
var_dump($objLdap->getUserdetailsByName("pp@testad1.local"));
var_dump($objLdap->getUserdetailsByName("pap@testad1.local"));

/*

$strServerIp = "192.168.60.206";
$strServerPort = 389;

$strUsername = "ff@testad1.local";
$strPwd = "ff";


$strUserBaseDN = "OU=accounts,DC=testad1,DC=local";
$strUserFilter = "(&(objectClass=user)(objectCategory=person)(cn=*))";

$strUserAttributes = "displayName,name,sAMAccountName,userPrincipalName,memberOf,distinguishedName,mail";

$strGroupIdentifier1 = "CN=Management,OU=Groups,DC=testad1,DC=local";
$strGroupIdentifier2 = "CN=Non-Management,OU=Groups,DC=testad1,DC=local";

$strGroupAttribute = "distinguishedName,member";
$strGroupFilter = "(objectClass=group)";
#------------------------------------------------------------------------------

echo "connecting to ldap...\n";
    $objDS = ldap_connect($strServerIp, $strServerPort );
echo "connection result: ".$objDS."\n\n";


echo "anonymous binding to ldap...\n";
$bitBind = ldap_bind($objDS);
echo "bind result: ".($bitBind ? " successfull" : " error" )."\n\n";

echo "identified binding to ldap...\n";
$bitBind = ldap_bind($objDS, $strUsername, $strPwd);
echo "bind result: ".($bitBind ? " successfull" : " error" )."\n\n";


echo "searching for users...\n";


$objResult = ldap_search($objDS, $strUserBaseDN, $strUserFilter);

if($objResult !== false) {
    echo "search found ".ldap_count_entries($objDS, $objResult)." entries...\n";

    $arrResult = ldap_first_entry($objDS, $objResult);
    while($arrResult !== false) {
        
        foreach(explode(",", $strUserAttributes) as $strOneAttribute) {
            echo "   attribute: ".$strOneAttribute." values: \n";
            $arrValues = @ldap_get_values($objDS, $arrResult, $strOneAttribute);
            if($arrValues !== false) {
                foreach($arrValues as $strKey => $strSingleValue) {
                    if($strKey !== "count") {
                        echo "        ".$strSingleValue."\n";
                    }
                }
            }
        }
        
        echo "--------------------\n";
        $arrResult = ldap_next_entry($objDS, $arrResult);
    }
}
else {
    echo "error: ".ldap_error($objDS)."\n";
}


echo "loading group-attributes...\n";
$objResult = ldap_search($objDS, $strGroupIdentifier1, $strGroupFilter);

if($objResult !== false) {
    echo "search found ".ldap_count_entries($objDS, $objResult)." entries...\n";

    $arrResult = ldap_first_entry($objDS, $objResult);
    while($arrResult !== false) {
        
        foreach(explode(",", $strGroupAttribute) as $strOneAttribute) {
            echo "   attribute: ".$strOneAttribute." values: \n";
            $arrValues = ldap_get_values($objDS, $arrResult, $strOneAttribute);
            foreach($arrValues as $strSingleValue)
                echo "        ".$strSingleValue."\n";
        }
        
        echo "--------------------\n";
        $arrResult = ldap_next_entry($objDS, $arrResult);
    }
}
else {
    echo "error: ".ldap_errno($objDS)." ".ldap_error($objDS)."\n";
}

$objResult = ldap_search($objDS, $strGroupIdentifier2, $strGroupFilter);

if($objResult !== false) {
    echo "search found ".ldap_count_entries($objDS, $objResult)." entries...\n";

    $arrResult = ldap_first_entry($objDS, $objResult);
    while($arrResult !== false) {
        
        foreach(explode(",", $strGroupAttribute) as $strOneAttribute) {
            echo "   attribute: ".$strOneAttribute." values: \n";
            $arrValues = ldap_get_values($objDS, $arrResult, $strOneAttribute);
            foreach($arrValues as $strSingleValue)
                echo "        ".$strSingleValue."\n";
        }
        
        echo "--------------------\n";
        $arrResult = ldap_next_entry($objDS, $arrResult);
    }
}
else {
    echo "error: ".ldap_errno($objDS)." ".ldap_error($objDS)."\n";
}

echo "disconnect...\n";
ldap_unbind($objDS);
 * 
 */

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


?>