<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 398


//editable entries
$lang["_search_deferred_indexer_"]       = "Deferred index building";
$lang["_search_deferred_indexer_hint"]   = "The indexing of objects is deferred in order to save time during editing and updating records. This requires the module workflows to be installed and configured properly.";
$lang["action_execute_search"]           = "Execute search";
$lang["action_new"]                      = "Create search";
$lang["workflow_deferredindexer_title"]                 = "search index update";
$lang["delete_question"]                 = "Do you really want to delete the search &quot;<b>%%element_name%%</b>&quot;?";
$lang["form_additionalheader"]           = "Additional filter";
$lang["form_search_changeenddate"]    = "Change to";
$lang["form_search_changestartdate"]  = "Change from";
$lang["form_search_query"]               = "Search query";
$lang["header_amount"]                   = "Number";
$lang["header_query"]                    = "Search query";
$lang["hitlist_text1"]                   = "The search for";
$lang["hitlist_text2"]                   = "found";
$lang["hitlist_text3"]                   = "results";
$lang["modul_titel"]                     = "Search";
$lang["search_details"]                  = "View detailed search results";
$lang["search_modules"]                  = "Module";
$lang["search_users"]                    = "User";
$lang["search_search"]                   = "Search";
$lang["searchterm_label"]                = "Search query";
$lang["select_all"]                      = "All modules";
$lang["stats_title"]                     = "Search queries";
$lang["submit_label"]                    = "Search";
$lang["systemtask_search_indexrebuild"]              = "Rebuild search index";
$lang["worker_indexrebuild_end"]              = "Index was rebuilt successfully.<br /><br />Number of documents in index: {0}<br />Number of entries in index: {1}";
$lang["worker_indexrebuild"]              = "Index rebuilding...<br /><br />Number of documents in index: {0}<br />Number of entries in index: {1}";
$lang["quickhelp_list"]                  = "This list encompasses search queries created and saved by the user. These search queries can be further detailed / parametrized or started from here.";
$lang["quickhelp_search"]               = "The search module provides the ability to find a content across the entire system. Therefore the desired search query is entered. The respective results are displayed in list form and can be edited according to user rights.";
$lang["quickhelp_new"]                  = "By using this function it is possible to create regular/continuous search queries. By clicking the corresponding button information will be saved to the search list and the query can be specified, started or changed.";

$lang["workflow_deferredindexer_cfg_val1"] = "Seconds between workflow runs";
$lang["workflow_deferredindexer_cfg_val2"] = "Max number of object to index per run";

$lang["search_reduce_hits_link"] = "The query returns a lot of hits. Please refine the query by using additional filters or conditions:<br /><ul><li>Multiple terms are seperated by a blank space: term1 term2</li><li>If a term must be included, the term may be marked with a + character: term1 +term2</li><li>If a term should be excluded, the term may be marked with a - symbol: term1 -term2</li></ul>";