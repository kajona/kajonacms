<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                  *
********************************************************************************************************/

/*
    Config-file for the ldap-connector. 
    The sample-file is created to match the structure of an ms active directory.

    There may be configured multiple ldap sources, each identified by the numerical array key.
    Do not change the key as soon as the provider os being used, otherwise mapped users and groups may be wrong.
*/


$config = array();

$config[0] = array();
//a readable name to identify the server within the GUI
$config[0]["ldap_alias"]                           = "Server 1";
$config[0]["ldap_server"]                          = "192.168.60.206";
$config[0]["ldap_port"]                            = 389;

//access configuration for the kernel in order to access the directory.
//could be anonymous or read only. e.g. used in order to find new users.
$config[0]["ldap_bind_anonymous"]                  = false;
$config[0]["ldap_bind_username"]                   = "ff@testad1.local";
$config[0]["ldap_bind_userpwd"]                    = "ff";

//the common identifier, used as a direct link to the object in the directory.
//in most cases, this is the combination of cn+objectCategory
$config[0]["ldap_common_identifier"]               = "distinguishedName";

//the common name is used as the mapper during login. when searching for logins using
//a full path, this attribute is being compared
$config[0]["ldap_common_name"]                     = "cn";

//the search-base for users unknown to the system
$config[0]["ldap_user_base_dn"]                    = "OU=accounts,DC=testad1,DC=local";
//filter to reduce the list of results to the matching object-types
$config[0]["ldap_user_filter"]                     = "(&(objectClass=user)(objectCategory=person)(cn=*))";

//query to be used when searching a single person. the ?-character will be replaced by the searchterm
$config[0]["ldap_user_search_filter"]              = "(&(objectClass=user)(objectCategory=person)(userPrincipalName=?))";

//mapping of ldap-attributes to system-internal attributes.
$config[0]["ldap_user_attribute_username"]         = "userPrincipalName";
$config[0]["ldap_user_attribute_mail_fallback"]    = "userPrincipalName";
$config[0]["ldap_user_attribute_mail"]             = "mail";
$config[0]["ldap_user_attribute_familyname"]       = "sn";
$config[0]["ldap_user_attribute_givenname"]        = "givenName";

//restriction to filter groups out of a result-set
$config[0]["ldap_group_filter"]                    = "(objectClass=group)";

//query to check if a given user DN is member of a group. the ? char will be replaced by the users' DN
$config[0]["ldap_group_isUserMemberOf"]            = "(&(objectClass=group)(member=?))";

//the attribute mapping to the groups' members
$config[0]["ldap_group_attribute_member"]          = "member";




$config[1] = array();
//a readable name to identify the server within the GUI
$config[1]["ldap_alias"]                           = "Server 2";
$config[1]["ldap_server"]                          = "192.168.60.216";
$config[1]["ldap_port"]                            = 389;
$config[1]["ldap_bind_anonymous"]                  = false;
$config[1]["ldap_bind_username"]                   = "ldapbind@ad.artemeon.int";
$config[1]["ldap_bind_userpwd"]                    = "123";
$config[1]["ldap_common_identifier"]               = "distinguishedName";
$config[1]["ldap_common_name"]                     = "cn";
$config[1]["ldap_user_base_dn"]                    = "OU=Anwender,DC=ad,DC=artemeon,DC=int";
$config[1]["ldap_user_filter"]                     = "(&(objectClass=user)(objectCategory=person)(cn=*))";
$config[1]["ldap_user_search_filter"]              = "(&(objectClass=user)(objectCategory=person)(userPrincipalName=?))";
$config[1]["ldap_user_attribute_username"]         = "userPrincipalName";
$config[1]["ldap_user_attribute_mail_fallback"]    = "userPrincipalName";
$config[1]["ldap_user_attribute_mail"]             = "mail";
$config[1]["ldap_user_attribute_familyname"]       = "sn";
$config[1]["ldap_user_attribute_givenname"]        = "givenName";
$config[1]["ldap_group_filter"]                    = "(objectClass=group)";
$config[1]["ldap_group_isUserMemberOf"]            = "(&(objectClass=group)(member=?))";
$config[1]["ldap_group_attribute_member"]          = "member";