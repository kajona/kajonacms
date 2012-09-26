<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 385

//editable entries
$lang["_admin_nr_of_rows_"]              = "Antal dataposter per sida";
$lang["_admin_nr_of_rows_hint"]          = "Antal dataposter i admin-lists, om understödda av modulen. Kan skrivas över av en modul.";
$lang["_admin_only_https_"]              = "Administreras endast via HTTPS:";
$lang["_admin_only_https_hint"]          = "Prioriterar användning av HTTPS i administrationsläge. Webservern måste för detta stödja HTTPS.";
$lang["_remoteloader_max_cachetime_"]    = "Cachetid för externa källor:";
$lang["_remoteloader_max_cachetime_hint"] = "Cache tid i sekunder för externt laddat innehåll (t.ex. RSS-feeds)";
$lang["_system_admin_email_"]            = "Administratörs e-mail:";
$lang["_system_admin_email_hint"]        = "Om ifyllt, kommer ett e-mail att skickas till denna adress om ett svårt fel uppstår.";
$lang["_system_browser_cachebuster_"]    = "Webläsare-Cachebuster";
$lang["_system_browser_cachebuster_hint"] = "Detta värde läggs till som en GET parameter i alla referenser till JS/CSS filer. Genom att öka detta värde tvingas webläsaren att återladda filer från servern, oberoende av webläsarens cache inställningar och de från servern skickade HTTP-huvudena. Värdet ökas på automatiskt av systemåtgärd 'töm cacheminne'.";
$lang["_system_changehistory_enabled_"]  = "Ändringshistorik aktiverad:";
$lang["_system_dbdump_amount_"]          = "Antal DB dumpningar";
$lang["_system_dbdump_amount_hint"]      = "Definieras hur många DB-dumpningar som skall behållas.";
$lang["_system_graph_type_"]             = "Använda diagrambibliotek:";
$lang["_system_graph_type_hint"]         = "Giltiga värden: pchart, ezc, flot. pChart måste laddas ner och installeras, ezc behöver PHP-modul 'cairo'.<br />Se även <a href=\"http://www.kajona.de/nicecharts.html\" taget=\"_blank\">http://www.kajona.de/nicecharts.html</a>";
$lang["_system_lock_maxtime_"]           = "Maximal spärrtid:";
$lang["_system_lock_maxtime_hint"]       = "Efter den angivna tiden i sekunder kommer den spärrade dataposten automatisk att frigöras.";
$lang["_system_mod_rewrite_"]            = "URL-omskrivning";
$lang["_system_mod_rewrite_hint"]        = "Aktiverar/deaktiverar URL-omskrivning för Nice-URL. Apachemodulen \"mod_rewrite\ måste för detta vara installerad i .htaccess-filen och vara aktiverad!";
$lang["_system_output_gzip_"]            = "GZIP-komprimering av utfilen.";
$lang["_system_output_gzip_hint"]        = "Aktiverar GPZ-komprimering av utfilen, innan denna skicka till webläsaren. Bättre: Aktiverar kompressionsinställningen i  .htaccess-filen.";
$lang["_system_portal_disable_"]         = "Deaktivera portal:";
$lang["_system_portal_disable_hint"]     = "Denna inställning aktiverar/deaktiverar hela portalen.";
$lang["_system_portal_disablepage_"]     = "Temporär sida:";
$lang["_system_portal_disablepage_hint"] = "Denna sida visas när portalen är deaktiverad.";
$lang["_system_release_time_"]           = "Sessions varaktighet:";
$lang["_system_release_time_hint"]       = "Efter denna tid i sekunder blir en session ogiltig.";
$lang["_system_use_dbcache_"]            = "Databanks cacheminne:";
$lang["_system_use_dbcache_hint"]        = "Aktiverar/deaktiverar det interna cacheminnet för databanksförfrågningar. (database query cache)";
$lang["about_part1"]                     = "<h2>Kajona V3 - Öppen kod Content Management System</h2>Kajona V 3.4.0, Kodnamn \"berlin\"<br /><br /><a href=\"http://www.kajona.de\" target=\"_blank\">www.kajona.de</a><br /><a href=\"mailto:info@kajona.de\" target=\"_blank\">info@kajona.de</a><br /><br />För ytterligare information, support eller förslag besök vår hemsida.<br />Support kan också fås i vårt forum <a href=\"http://board.kajona.de/\" target=\"_blank\">Forum</a>.";
$lang["about_part2"]                     = "<h2>Utvecklingsledning</h2><ul><li><a href=\"https://www.xing.com/profile/Stefan_Idler\" target=\"_blank\">Stefan Idler</a>, <a href=\"mailto:sidler@kajona.de\">sidler@kajona.de</a> (Projektledning, Teknisk administration, Utveckling)</li></ul><h2>Bidragsgivare / Utvecklare</h2><ul><li>Stefan Bongartz</li><li><a href=\"https://www.xing.com/profile/Florian_Feigenbutz\" target=\"_blank\">Florian Feigenbutz</a></li><li>Thomas Hertwig</li><li><a href=\"mailto:tim.kiefer@kojikui.de\" target=\"_blank\">Tim Kiefer</a></li><li>Mario Lange</li><li>Stefan Meyer</li><li><a href=\"https://www.xing.com/profile/Jakob_Schroeter\" target=\"_blank\">Jakob Schröter</a>, <a href=\"mailto:jschroeter@kajona.de\">jschroeter@kajona.de</a></li><li><a href=\"mailto:ph.wolfer@googlemail.com\" target=\"_blank\">Philipp Wolfer</a></li></ul><h2>Översättningar</h2><ul><li>Bulgariska: <a href=\"mailto:contact@rudee.info\">Rumen Emilov</a></li><li>Portugisiska: <a href=\"http://www.nunocruz.com\" target=\"_blank\">Nuno Cruz</a></li><li>Ryska: <a href=\"https://www.xing.com/profile/Ksenia_KramVinogradova\" target=\"_blank\">Ksenia Kram</a>, <a href=\"https://www.xing.com/profile/Michael_Kram\" target=\"_blank\">Michael Kram</a></li><li>Svenska: <a href=\"mailto:villa.carlberg@telia.com\">Per Gunnarsson</a></li></ul>";
$lang["about_part3"]                     = "<h2>Tack till</h2><ul><li>Icons:<br />Everaldo Coelho (Crystal Clear, Crystal SVG), <a href=\"http://everaldo.com/\" target=\"_blank\">http://everaldo.com/</a><br />Oxygen Icons, <a href=\"http://www.oxygen-icons.org/\" target=\"blank\">http://www.oxygen-icons.org/</a></li><li>browscap.ini: Gary Keith, <a href=\"http://browsers.garykeith.com/downloads.asp\" target=\"_blank\">http://browsers.garykeith.com/downloads.asp</a></li><li>CKEditor: Frederico Caldeira Knabben, <a href=\"http://www.ckeditor.com/\" target=\"_blank\">http://www.ckeditor.com/</a></li><li>ez components (charts): <a href=\"http://ezcomponents.org\" target=\"_blank\">http://ezcomponents.org</a></li><li>DejaVu Fonts:<br />DejaVu Team, <a href=\"http://dejavu.sourceforge.net\" target=\"_blank\">http://dejavu.sourceforge.net</a></li><li>Bootstrap, <a href=\"http://twitter.github.com/bootstrap/\" target=\"_blank\">http://twitter.github.com/bootstrap/</a></li><li>Hallo Editor, <a href=\"http://hallojs.org/\" target=\"_blank\">http://hallojs.org/</a></li><li>Fine Uploader, <a href=\"http://fineuploader.com/\" target=\"_blank\">http://fineuploader.com/</a></li></ul>";
$lang["about_part4"]                     = "<h2>Donera</h2><p>Om du gillar att använda med Kajona och vill ge stöd till prjektet, kan hjälpa till med en donation här  : </p> <form method=\"post\" action=\"https://www.paypal.com/cgi-bin/webscr\" target=\"_blank\"><input type=\"hidden\" value=\"_donations\" name=\"cmd\" /> <input type=\"hidden\" value=\"donate@kajona.de\" name=\"business\" /> <input type=\"hidden\" value=\"Kajona Development\" name=\"item_name\" /> <input type=\"hidden\" value=\"0\" name=\"no_shipping\" /> <input type=\"hidden\" value=\"1\" name=\"no_note\" /> <input type=\"hidden\" value=\"EUR\" name=\"currency_code\" /> <input type=\"hidden\" value=\"0\" name=\"tax\" /> <input type=\"hidden\" value=\"PP-DonationsBF\" name=\"bn\" /> <input type=\"submit\" name=\"submit\" value=\"Spenden via PayPal\" class=\"inputSubmit\" /></form>";
$lang["actionAbout"]                     = "Om Kajona";
$lang["actionAspects"]                   = "Aspekter";
$lang["actionChangelog"]                 = "Ändringshistorik";
$lang["actionList"]                      = "Installerade moduler";
$lang["actionSystemInfo"]                = "Systeminformation";
$lang["actionSystemSessions"]            = "Sessioner";
$lang["actionSystemSettings"]            = "Systeminställningar";
$lang["actionSystemTasks"]               = "Systemuppgifter";
$lang["actionSystemlog"]                 = "Systemlogg";
$lang["anzahltabellen"]                  = "Antal tabeller";
$lang["aspect_create"]                   = "Ny aspekt";
$lang["aspect_default"]                  = "Standardaspekt";
$lang["aspect_delete_question"]          = "Vill du verkligen radera aspekten &quot;<b>%%element_name%%</b>&quot;?";
$lang["aspect_edit"]                     = "Redigera aspekt";
$lang["aspect_isDefault"]                = "Standardaspekt";
$lang["aspect_isdefault"]                = "Ja";
$lang["aspect_list_empty"]               = "Ingen aspekt skapad";
$lang["aspect_nodefault"]                = "Nej";
$lang["aspect_permissions"]              = "Redigera rättigheter";
$lang["backlink"]                        = "Tillbaka";
$lang["browser"]                         = "Sidobläddrare";
$lang["cache_entry_size"]                = "Storlek";
$lang["cache_hash1"]                     = "Hash 1";
$lang["cache_hash2"]                     = "Hash 2";
$lang["cache_hits"]                      = "Träffar";
$lang["cache_language"]                  = "Språk";
$lang["cache_leasetime"]                 = "Giltig till";
$lang["cache_source"]                    = "Källa";
$lang["change_action"]                   = "Handling";
$lang["change_date"]                     = "Datum";
$lang["change_module"]                   = "Modul";
$lang["change_newvalue"]                 = "Nytt värde";
$lang["change_oldvalue"]                 = "Gammalt värde";
$lang["change_property"]                 = "Egenskap";
$lang["change_record"]                   = "Objekt";
$lang["change_type_setting"]             = "Inställning";
$lang["change_user"]                     = "Användare";
$lang["dateStyleLong"]                   = "Y-m-d H:i:s";
$lang["dateStyleShort"]                  = "Y-m-d";
$lang["datenbankclient"]                 = "Databasklient";
$lang["datenbankserver"]                 = "Databasserver";
$lang["datenbanktreiber"]                = "Drivrutin för databas";
$lang["datenbankverbindung"]             = "Databasförbindelse";
$lang["db"]                              = "Databas";
$lang["deleteButton"]                    = "Radera";
$lang["desc"]                            = "Ändra rättigher till:";
$lang["dialog_cancelButton"]             = "avbryt";
$lang["dialog_deleteButton"]             = "Ja, radera";
$lang["dialog_deleteHeader"]             = "Belkräfta radering";
$lang["dialog_loadingHeader"]            = "Vänligen vänta";
$lang["diskspace_free"]                  = "(fri/totalt)";
$lang["errorintro"]                      = "Vänligen fyll i alla obligatoriska fält!";
$lang["errorlevel"]                      = "Felnivå";
$lang["executiontimeout"]                = "Exekution timeout";
$lang["fehler_recht"]                    = "Inte tillräckliga rättigheter för att utföra denna handling";
$lang["fehler_setzen"]                   = "Fel vid sparandet av rättigheter";
$lang["filebrowser"]                     = "Välj fil";
$lang["form_aspect_name"]                = "Namn:";
$lang["gd"]                              = "GD-Lib";
$lang["geladeneerweiterungen"]           = "Laddade utökningar";
$lang["gifread"]                         = "Lässtöd GIF";
$lang["gifwrite"]                        = "Skrivstöd GIF";
$lang["groessedaten"]                    = "Datas storlek";
$lang["groessegesamt"]                   = "Sammanlagd storlek";
$lang["inputtimeout"]                    = "Input Timeout";
$lang["installer_config_dbdriver"]       = "Drivruting till databas";
$lang["installer_config_dbdriverinfo"]   = "Moduler tillgängliga i systemet:";
$lang["installer_config_dbhostname"]     = "Databasserver:";
$lang["installer_config_dbname"]         = "Databasnamn";
$lang["installer_config_dbpassword"]     = "Databaslösenord:";
$lang["installer_config_dbport"]         = "Databasport:";
$lang["installer_config_dbportinfo"]     = "För att använda en standardport lämna tom";
$lang["installer_config_dbprefix"]       = "Tabellprefix";
$lang["installer_config_dbusername"]     = "Databasanvändare";
$lang["installer_config_intro"]          = "<b>Inställningar av databasen</b><br /><br />Observer: Webservern behöver skrivrättigheter i filen /system/config/config.php.<br />Tomma värden för databasserver, -användare, -lösenord och -namn är inte tillåtna.<br /><br />Om du vill lämna något av dessa värden tomt, configurera datafilen  /system/config/config.php manuellt i en textredigerare. Mer om detta hittar du i Handbuch (ännu ej på svenska).<br /><br />";
$lang["installer_config_write"]          = "Spara till config.php";
$lang["installer_elements_found"]        = "<b>Installation av sidoelement</b><br />Vänligen välj det sidoelement som du vill installera:<br /><br />";
$lang["installer_finish_closer"]         = "<br />Ha så roligt med Kajona!";
$lang["installer_finish_hints"]          = "Du bör nu ta bort skrivrättigheterna till filen /project/system/config/config.php.<br />Dessutom bör du av säkerhetsskäl helt ta bort filen  /installer.php <br /><br /><br />Administrationsmiljön kan nu nås under:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/admin\">"._webpath_."/admin</a><br /><br />Portalen kan nås under: <br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/\">"._webpath_."</a><br /><br />";
$lang["installer_finish_intro"]          = "<b>Installationen är avslutad</b><br /><br />";
$lang["installer_given"]                 = "tillgänglig";
$lang["installer_install"]               = "installlera";
$lang["installer_installpe"]             = "Installera sidoelement";
$lang["installer_loaded"]                = "uppladdad";
$lang["installer_login_email"]           = "e-mail:";
$lang["installer_login_installed"]       = "<br />Systemet är installerat och ett administratörskonto finns redan<br />";
$lang["installer_login_intro"]           = "<b>Konfigurera administratör</b><br /><br />Vänligen ange här ett användarnman och ett lösenord.<br />Dessa behövs senare för att logga in som administratör.<br />Av säkerhetsskäl bör namn som \"admin\" eller \"administratör\ undvikas.<br /><br />";
$lang["installer_login_password"]        = "Lösenord:";
$lang["installer_login_save"]            = "Skapa användare";
$lang["installer_login_username"]        = "Användarnamn:";
$lang["installer_missing"]               = "saknas";
$lang["installer_module_notinstalled"]   = "Modul är inte installerad";
$lang["installer_modules_found"]         = "<b>Installation/uppdatering av modul</b><br /><br />Vänligen välj modul, som du vill installera:<br /><br />";
$lang["installer_modules_needed"]        = "Moduler som måste installeras:";
$lang["installer_next"]                  = "Nästa steg >";
$lang["installer_nloaded"]               = "saknas";
$lang["installer_phpcheck_folder"]       = "Skrivrättigheter på";
$lang["installer_phpcheck_intro"]        = "<b>Hjärtligt välkommen</b><br /><br />";
$lang["installer_phpcheck_intro2"]       = "<br />Installationen går i flera steg: <br />Kontroll av rättigheter, konfigurering av databas, referensere för att ha tillgång till administrationen, modulinstallation, elementinstallation och installation av exempel.<br /><br />Beroende på val av moduler kan antalet steg variera.<br /><br /> <b>Före en uppdatering av system vänligen läs<br /><a href=\"http://www.kajona.de/update_311_to_320.html\" target=\"_blank\">Updatehinweise von 3.1.x auf 3.2.0</a><br /><a href=\"http://www.kajona.de/update_32x_to_330.html\" target=\"_blank\">Updatehinweise von 3.2.x auf 3.3.0</a><br /><a href=\"http://www.kajona.de/update_33x_to_340.html\" target=\"_blank\">Updatehinweise von 3.3.x auf 3.4.0</a>.</b><br /><br /><br />Skrivrättigheter till enskilda filer och kataloger liksom<br />tillgänglighet av nödvändiga PHP-moduler  kontrolleras:<br />";
$lang["installer_phpcheck_lang"]         = "För att ladda upp installer i ett annat språk, använd en av följande länkar:<br /><br />";
$lang["installer_phpcheck_module"]       = "PHP-Modul ";
$lang["installer_prev"]                  = "< Föregående steg";
$lang["installer_samplecontent"]         = "<b>Installation av innehåll till exempel</b><br /><br />Modulen Samplecontent skapar ett antal standardsidor och menyer.<br />och efter att modulerna har installerats kommer olika exempelinnehåll att installeras.<br /><br /><br />";
$lang["installer_step_adminsettings"]    = "Administratörstillgång";
$lang["installer_step_dbsettings"]       = "Databasinställningar";
$lang["installer_step_finish"]           = "Avsluta";
$lang["installer_step_modules"]          = "Moduler";
$lang["installer_step_phpsettings"]      = "PHP-konfiguration";
$lang["installer_step_samplecontent"]    = "Exempelinnehåll";
$lang["installer_systemlog"]             = "Systemlogg";
$lang["installer_systemversion_needed"]  = "Min. nödvändig systemversion:";
$lang["installer_update"]                = "Uppdatera till";
$lang["installer_versioninstalled"]      = "Installerad version:";
$lang["jpg"]                             = "JPG stöd";
$lang["keinegd"]                         = "Ingen GD-Lib installerad";
$lang["log_empty"]                       = "Inga poster i systemloggfilen";
$lang["login_xml_error"]                 = "Inloggning mislyckades";
$lang["login_xml_succeess"]              = "Inloggning lyckades";
$lang["logout_xml"]                      = "Utloggning lyckades";
$lang["mail_body"]                       = "Innehåll:";
$lang["mail_cc"]                         = "Mottagare kopia:";
$lang["mail_recipient"]                  = "Mottagare:";
$lang["mail_send_error"]                 = "Fel vid sändninga av e-mail. Vänligen försök på nytt.";
$lang["mail_send_success"]               = "Skickande av e-mail har lyckats.";
$lang["mail_subject"]                    = "Ämne:";
$lang["memorylimit"]                     = "Begränsning av minne";
$lang["modul_aspectedit"]                = "Redigera aspekt";
$lang["modul_rechte"]                    = "Modulrättigheter";
$lang["modul_rechte_root"]               = "Root rättigheter";
$lang["modul_sortdown"]                  = "Flytta nedåt";
$lang["modul_sortup"]                    = "Flytta uppåt";
$lang["modul_status_disabled"]           = "Aktivera modul (är inaktiverad)";
$lang["modul_status_enabled"]            = "Inaktivera modul (är aktiverad)";
$lang["modul_status_system"]             = "Hmmm. Vill du deaktivera system-kernel? Innan du gör det formatera hårddisken, format c, i stället :).";
$lang["modul_titel"]                     = "System";
$lang["moduleRightsTitle"]               = "Rättigheter";
$lang["numberStyleDecimal"]              = ",";
$lang["numberStyleThousands"]            = " ";
$lang["operatingsystem"]                 = "Operativsystem";
$lang["pageview_backward"]               = "Tillbaka";
$lang["pageview_forward"]                = "Framåt";
$lang["pageview_total"]                  = "Totalt:";
$lang["php"]                             = "PHP";
$lang["png"]                             = "PNG stöd";
$lang["postmaxsize"]                     = "Posts max storlek";
$lang["quickhelp_change"]                = "I detta formulär kan rättigheterna till en datapost anpassas.<br />Beroende på vilken modul posten tillhör kan antalet möjliga konfigueringar varieras.";
$lang["quickhelp_list"]                  = "Listan av moduler ger en snabb överblick över vilka moduler som för närvarande är installerade.<br />I tillägg anges den aktuella versionen  liksom installationsdatum för modulen.<br />Modulens rättigheter kan redigeras  i modulrättighetsregistret, från vilket inställningarna ärvs om om registret är aktiverat.<br />Orningsföljden mellan modulerna kan ändras genom att flytta moduleran i listan.";
$lang["quickhelp_moduleList"]            = "Listan av moduler ger en snabb överblick över vilka moduler som för närvarande är installerade.<br />I tillägg anges den aktuella versionen versionen likdom installationsdatum för modulen.<br />Med hjälp av modulens rättigheter-knapp kan varje moduls rot-rättigheter redigeras som i sin tur ärvs av enstaka dataposter i modulen (så länge dataposters arvsrättighet är aktiverad).<br />Ordningsföljden mellan modulerna kan ändras genom att flytta moduleran i listan.";
$lang["quickhelp_systemInfo"]            = "Kajona försöker här ta reda på en del information om systemet, i vilket installaltionen är gjord.";
$lang["quickhelp_systemSettings"]        = "Här kan grundläggande inställningar göras i systemet. För detta kan varje modul erbjuda några inställningsmöjligheter.Här företagna ändringar skall göras med försiktighet, felaktiga inställningar kan i värsta fall leda till att systemet blir obrukbart. <br /><br />Anmärkning: Ändras ett värde i en modul, så måste för VARJE modul spara-knappen tryckas in. Ändringare i andra moduler påverkas ej vid aktivering av spara-knappen. Endast värden tillhörande den modul vars knapp trycks ner sparas.";
$lang["quickhelp_systemTasks"]           = "Systemuppgifter är små program, som utför dagliga underhållsarbeten i systemet.<br />Till dessa hör backup av databasen och återställning en tidigare gjord backup.";
$lang["quickhelp_systemlog"]             = "Systemloggboken visar inlägg gjorda i loggboken, i vilken modulen nyheter kan skriva.<br />Detaljeringsgrad av logginläggen kan ställas in i config-filen  (/system/config/config.php).";
$lang["quickhelp_title"]                 = "Snabbhjälp";
$lang["quickhelp_updateCheck"]           = "Med användning av updatering-kontroll jämförs versionsnummer på i systemet installerade moduler med  moduler on-line. Om det existerar en nyare version, lämnar Kajone en anmärkning om detta vid berörd moduls rad.";
$lang["send"]                            = "Skicka";
$lang["server"]                          = "Webserver";
$lang["session_activity"]                = "Aktivitet";
$lang["session_admin"]                   = "Administrationsmodul:";
$lang["session_loggedin"]                = "Inloggad";
$lang["session_loggedout"]               = "Gäst";
$lang["session_logout"]                  = "Session avslutad";
$lang["session_portal"]                  = "Portalsida:";
$lang["session_portal_imagegeneration"]  = "Bildgenerering";
$lang["session_status"]                  = "Status";
$lang["session_username"]                = "Änvändare";
$lang["session_valid"]                   = "Giltig till";
$lang["setAbsolutePosOk"]                = "Sparande av position har lyckats";
$lang["setStatusError"]                  = "Fel vid ändring av status";
$lang["setStatusOk"]                     = "Ändring av status har lyckats";
$lang["settings_false"]                  = "Nej";
$lang["settings_true"]                   = "Ja";
$lang["settings_updated"]                = "Inställningar har ändrats.";
$lang["setzen_erfolg"]                   = "Sparande av rättigheter har lyckats";
$lang["speichern"]                       = "Spara";
$lang["speicherplatz"]                   = "Plats för sparande";
$lang["status_active"]                   = "Ändring av status (aktiverad)";
$lang["status_inactive"]                 = "Ändring av status (inaktiverad)";
$lang["submit"]                          = "Spara";
$lang["system_cache"]                    = "Cacheminne";
$lang["systeminfo_no"]                   = "Nej";
$lang["systeminfo_php_regglobal"]        = "Register globals";
$lang["systeminfo_php_safemode"]         = "Safe mode";
$lang["systeminfo_php_urlfopen"]         = "Tillåt url fopen";
$lang["systeminfo_webserver_modules"]    = "Laddade moduler";
$lang["systeminfo_webserver_version"]    = "Webserver";
$lang["systeminfo_yes"]                  = "Ja";
$lang["systemtask_cacheSource_source"]   = "Cache-typer";
$lang["systemtask_cancel_execution"]     = "Exekvering utförd";
$lang["systemtask_close_dialog"]         = "OK";
$lang["systemtask_compresspicuploads_done"] = "Skalning och komprimering av bild har avslutats.";
$lang["systemtask_compresspicuploads_found"] = "Hittade bilder";
$lang["systemtask_compresspicuploads_height"] = "Max höjd (pixel)";
$lang["systemtask_compresspicuploads_hint"] = "För att spara diskutrymme kan uppladdade bilder i mappen  \"/portal/pics/upload\" skalas och komprimeras till givna maximal dimensioner.<br />Observera, att denna procedur inte är reversibel och kan leda till kvalitetsförsämringar på bilderna.<br />Processen kan ta några minuter.";
$lang["systemtask_compresspicuploads_name"] = "Komprimera uppladdade bilder";
$lang["systemtask_compresspicuploads_processed"] = "Bearbeta bilder";
$lang["systemtask_compresspicuploads_width"] = "Max bredd (pixel)";
$lang["systemtask_dbconsistency_curprev_error"] = "Följande förälder-barn förhållande är fel (fattas förälderdel):";
$lang["systemtask_dbconsistency_curprev_ok"] = "Alla förälder-barn förhållanden är korrekta";
$lang["systemtask_dbconsistency_date_error"] = "Följande dataposter är felaktiga (System-poster fattas)";
$lang["systemtask_dbconsistency_date_ok"] = "Alla datumposter har en motsvarande systempost";
$lang["systemtask_dbconsistency_firstlevel_error"] = "Inte alla första-nivå-noder hör till en modul";
$lang["systemtask_dbconsistency_firstlevel_ok"] = "Alla första-nivå-noder hör till en modul";
$lang["systemtask_dbconsistency_name"]   = "Kontrollra databasens samstämmighet";
$lang["systemtask_dbconsistency_right_error"] = "Följande rättighetsposter är felaktiga (fattas systempost)";
$lang["systemtask_dbconsistency_right_ok"] = "Alla rättighetsposter har en motsvarande systempost";
$lang["systemtask_dbexport_error"]       = "Fel vid backup av databas";
$lang["systemtask_dbexport_exclude"]     = "Ja";
$lang["systemtask_dbexport_exclude_intro"] = "Om aktiverad kommer såväl statistiktabeller som cachetabeller att utelämnas.";
$lang["systemtask_dbexport_excludetitle"] = "Uteslut tabeller:";
$lang["systemtask_dbexport_include"]     = "Nej";
$lang["systemtask_dbexport_name"]        = "Backa up databas";
$lang["systemtask_dbexport_success"]     = "Backup har lyckats";
$lang["systemtask_dbimport_error"]       = "Fel vid återställning av säkerhetskopia";
$lang["systemtask_dbimport_file"]        = "Backup:";
$lang["systemtask_dbimport_name"]        = "Importera databas";
$lang["systemtask_dbimport_success"]     = "Återställninga av säkerhetskopia har lyckats";
$lang["systemtask_dialog_title"]         = "Systemuppgift pågår";
$lang["systemtask_dialog_title_done"]    = "Systemuppgift avslutad";
$lang["systemtask_filedump_error"]       = "Ett fel uppstod vid backup";
$lang["systemtask_filedump_name"]        = "Skapa backup av filsystemet";
$lang["systemtask_filedump_success"]     = "Backukp lyckades.<br/>Av säkerhetsskäl bör säkerhetskopian fortast möjligt avlägsnas från servern.<br />Säkerhetsfilens namn:&nbsp;";
$lang["systemtask_flushcache_all"]       = "Alla poster";
$lang["systemtask_flushcache_error"]     = "Ett fel har uppstått.";
$lang["systemtask_flushcache_name"]      = "Töm globalt cacheminne";
$lang["systemtask_flushcache_success"]   = "Cacheminnet har tömts";
$lang["systemtask_flushpiccache_deleted"] = "<br />Antal raderade bilder:";
$lang["systemtask_flushpiccache_done"]   = "Tömning har avslutats.";
$lang["systemtask_flushpiccache_name"]   = "Töm cacheminne för bilder";
$lang["systemtask_flushpiccache_skipped"] = "<br />Antal överhoppade bilder:";
$lang["systemtask_group_cache"]          = "Cacheminne";
$lang["systemtask_group_database"]       = "Databas";
$lang["systemtask_group_default"]        = "Diverse";
$lang["systemtask_group_pages"]          = "Sidor";
$lang["systemtask_group_stats"]          = "Statistik";
$lang["systemtask_progress"]             = "Framsteg:";
$lang["systemtask_run"]                  = "Genomför";
$lang["systemtask_runningtask"]          = "Uppgift:";
$lang["systemtask_status_error"]         = "Fel vid angivande av status";
$lang["systemtask_status_success"]       = "Angivande av status har lyckats";
$lang["systemtask_systemstatus_active"]  = "Aktiverad";
$lang["systemtask_systemstatus_inactive"] = "Inaktiverad";
$lang["systemtask_systemstatus_name"]    = "Ange status för en datapost";
$lang["systemtask_systemstatus_status"]  = "Status:";
$lang["systemtask_systemstatus_systemid"] = "Systemid:";
$lang["titel_erben"]                     = "Ärv rättigheter:";
$lang["titel_leer"]                      = "<em>Ingen titel insatt</em>";
$lang["titel_root"]                      = "Roträttigheter ";
$lang["titleTime"]                       = "Klockan";
$lang["toolsetCalendarMonth"]            = "\"Januari\", \"februari\", \"mars\", \"april\", \"maj\", \"juni\", \"juli\", \"august\", \"september\", \"oktober\", \"november\", \"december\"";
$lang["toolsetCalendarWeekday"]          = "\"sö\", \"mo\", \"ti\", \"on\", \"to\", \"fr\", \"lö\"";
$lang["update_available"]                = "Vänligen uppdatera!";
$lang["update_invalidXML"]               = "Svaret från servern var tyvärr ej korrekt. Vänligen försök på nytt.";
$lang["update_module_localversion"]      = "Denna installation";
$lang["update_module_name"]              = "Modul";
$lang["update_module_remoteversion"]     = "Disponivel";
$lang["update_nodom"]                    = "Denna PHP-installation understöder inte XML-DOM. Detta är nödvändigt för att uppdatera-kontrollera skall fungera.";
$lang["update_nofilefound"]              = "LIstan på uppdateringar kunde inte laddas..<br />Detta kan bero på: att systemet PHP-konfig-värde 'tillåt_url_fopen' har satts till 'av', eller system inte stödjer sockets.";
$lang["update_nourlfopen"]               = "För denna funktion måste värdet &apos;allow_url_fopen&apos;i PHP-konfigurationen sättas till &apos;on&apos;";
$lang["uploadmaxsize"]                   = "Max storlek för uplladdning";
$lang["uploads"]                         = "Uppladdningar";
$lang["version"]                         = "Version";
$lang["warnung_settings"]                = "!! VARNING !!<br />Felaktiga värden i följande inställningar kan göra system obrukbart!";
