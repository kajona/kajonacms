<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: modul_system_id.php 3530 2011-01-06 12:30:26Z sidler $                                         *
********************************************************************************************************/

/*
 * This files specified a list of "excluded groups".
 * All group-ids specified are non-editable while editing group assignments.
 * This may be used to block privilege escalations in special scenarios.
 * The groups are removed from the assignemt-table unless you are a super-admin.
 *
 * Provide a comma-separated list of group-ids.
 */


$config["blockedgroups"] = "";