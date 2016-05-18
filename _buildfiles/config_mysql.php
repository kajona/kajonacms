<?php 

//database-settings
define("DB_HOST",				                "localhost");
define("DB_USER",                               "kajona");
define("DB_PASS",                               "kajona");
define("DB_DB",                                 "kajona");
define("DB_DRIVER",                             "mysqli");

ini_set("session.save_path", sys_get_temp_dir());
ini_set("session.use_cookies", "Off");
