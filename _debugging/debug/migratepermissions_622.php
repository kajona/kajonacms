<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/
namespace Kajona\Debugging\Debug;

use Kajona\System\Installer\InstallerSystem;


echo "Migrating user group ids to new schema, paged".PHP_EOL;

const INT_PAGESIZE = 2500;

$objInstaller = new InstallerSystem();
$objInstaller->migrateUserData(INT_PAGESIZE, true);
