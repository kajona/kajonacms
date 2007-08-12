<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	module.php																							*
* 	Modulesnumbers       																				*
*	Assigns numbers to each module. Don't change them after installation, otherwise the db-schema     	*
*	gets invalid!																						*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                           *
********************************************************************************************************/

//Modul-id des Systems
	define(	"_system_modul_id_", 						0);

//Modul-id des Login-Moduls
	define(	"_login_modul_id_", 						0);

//Modul-id der Dateisystem-Verwaltung
	define( "_filesystem_modul_id_",					0);

//ID der User-Verwaltung
	define(	"_user_modul_id_",							15);

//ID der Rechte-Verwaltung
	define(	"_rechte_modul_id_",						20);

//ID des Gaestebuch-Moduls
	define( "_filemanager_modul_id_",					40);

?>