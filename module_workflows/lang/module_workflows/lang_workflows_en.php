<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 389

//editable entries
$lang["action_edit_handler"]             = "Edit default values";
$lang["action_instantiate_handler"]      = "Create a new workflow instance";
$lang["action_list_handlers"]            = "Workflow-Handlers";
$lang["action_show_details"]             = "Show details";
$lang["delete_question"]                 = "Do you really want to delete the workflow &quot;<b>%%element_name%%</b>&quot;?";
$lang["header_list_all"]                 = "All workflows";
$lang["header_list_my"]                  = "My workflows";
$lang["instance_responsible"]            = "Responsible person";
$lang["instance_responsible_hint"]       = "If the workflow requires the interaction with a person or if the workflow should be assigned to a single user, the user may be set up here.";
$lang["instance_systemid"]               = "Relevant systemid";
$lang["instance_systemid_hint"]          = "If the workflow should be connected with a real object, the objects system-id may be set up here.";
$lang["list_empty"]                      = "No workflows available";
$lang["modul_titel"]                     = "Workflows";
$lang["module_list_handlers"]            = "Workflow-Handlers";
$lang["module_mylist"]                   = "My workflows";
$lang["module_trigger"]                  = "Trigger workflows";
$lang["myList_empty"]                    = "No workflows to process available.";
$lang["quickhelp_list"]                  = "On the page \"all workflows\" all workflows known by the system are listed, so open, scheduled or finished ones.<br /> The workflows are listed by its due-date.";
$lang["quickhelp_my_list"]               = "The list \"my workflows\" shows all workflows relevant for the user currently being logged in.";
$lang["systemtask_runworkflows_name"]    = "Run workflows";
$lang["workflow_char1"]                  = "Char 1";
$lang["workflow_char2"]                  = "Char 2";
$lang["workflow_class"]                  = "Handler";
$lang["workflow_date1"]                  = "Date 1";
$lang["workflow_date2"]                  = "Date 2";
$lang["workflow_dbdump_val1"]            = "Interval in hours";
$lang["workflow_dbdumps_title"]          = "Periodic database backup";
$lang["workflow_general"]                = "Common values";
$lang["workflow_handler_val1"]           = "Value 1";
$lang["workflow_handler_val2"]           = "Value 2";
$lang["workflow_handler_val3"]           = "Value 3";
$lang["workflow_int1"]                   = "Number 1";
$lang["workflow_int2"]                   = "Number 2";
$lang["workflow_owner"]                  = "Originator";
$lang["workflow_params"]                 = "Technical parameters";
$lang["workflow_responsible"]            = "Responsible person";
$lang["workflow_runs"]                   = "Executions";
$lang["workflow_status"]                 = "Status";
$lang["workflow_status_1"]               = "New";
$lang["workflow_status_2"]               = "Scheduled";
$lang["workflow_status_3"]               = "Finished";
$lang["workflow_systemid"]               = "Relevant systemid";
$lang["workflow_text"]                   = "Text";
$lang["workflow_text2"]                  = "Text 2";
$lang["workflow_trigger"]                = "Next execution";
$lang["workflow_ui"]                     = "Show edit-form for the current step";
$lang["_workflows_trigger_authkey_hint"] = "Use the auth-key as a secret when triggering the workflow-engine. Only if the passed auth-key matches the saved auth-key, the workflows will be triggered. This avoids that third-party clients could trigger the workflows (DOS-attack).<br />Use the following URL to trigger the workflow engine, e.g. using a cron-job: <br />"._xmlpath_."?module=workflows&action=trigger&authkey=".(defined("_workflows_trigger_authkey_") ? _workflows_trigger_authkey_ : "");
$lang["_workflows_trigger_authkey_"]     = "Auth-Key";

//non-editable entries
$lang["permissions_header"]              = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Handlers", 5 => "", 6 => "", 7 => "", 8 => "");
