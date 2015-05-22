<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

/*
 * This files specified a list of "excluded groups".
 * All group-ids specified are non-editable while editing group assignments.
 * This may be used to block privilege escalations in special scenarios.
 * The groups are removed from the assignment-table unless you are a super-admin.
 *
 * Provide a comma-separated list of group-ids.
 */


$config["blockedgroups"] = "".class_module_system_setting::getConfigValue("_admins_group_id_");