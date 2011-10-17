<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                  *
********************************************************************************************************/

/*
    Config-file for the ldap-connector. 
    The sample-file is created to match the structure of an ms active directory.
*/


    $config = array();
    $config["ldap_server"]                          = "192.168.60.206";
    $config["ldap_port"]                            = 389;
    
    //access configuration for the kernel in order to access the directory.
    //could be anonymous or read only. e.g. used in order to find new users.
    $config["ldap_bind_anonymous"]                  = false;
    $config["ldap_bind_username"]                   = "ff@testad1.local";
    $config["ldap_bind_userpwd"]                    = "ff";
    
    //the common identifier, used as a direct link to the object in the directory.
    //in most cases, this is the combination of cn+objectCategory
    $config["ldap_common_identifier"]               = "distinguishedName";
    
    //the common name is used as the mapper during login. when searching for logins using
    //a full path, this attribute is being compared
    $config["ldap_common_name"]                     = "cn";
    
    //the search-base for users unknown to the system 
    $config["ldap_user_base_dn"]                    = "OU=accounts,DC=testad1,DC=local";
    //filter to reduce the list of results to the matching object-types
    $config["ldap_user_filter"]                     = "(&(objectClass=user)(objectCategory=person)(cn=*))";
    
    //query to be used when searching a single person. the ?-character will be replaced by the searchterm
    $config["ldap_user_search_filter"]              = "(&(objectClass=user)(objectCategory=person)(userPrincipalName=?))";
    
    //mapping of ldap-attributes to system-internal attributes.
    $config["ldap_user_attribute_username"]         = "userPrincipalName";
    $config["ldap_user_attribute_mail_fallback"]    = "userPrincipalName";
    $config["ldap_user_attribute_mail"]             = "mail";
    $config["ldap_user_attribute_familyname"]       = "sn";
    $config["ldap_user_attribute_givenname"]        = "givenName";
    
    //restriction to filter groups out of a result-set
    $config["ldap_group_filter"]                    = "(objectClass=group)";
    
    //query to check if a given user DN is member of a group. the ? char will be replaced by the users' DN
    $config["ldap_group_isUserMemberOf"]            = "(&(objectClass=group)(member=?))";
    
    //the attribute mapping to the groups' members
    $config["ldap_group_attribute_member"]          = "member";

?>