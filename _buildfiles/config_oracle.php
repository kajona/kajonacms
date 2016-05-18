<?php 

//database-settings
define("DB_HOST",				                "192.168.60.208");
define("DB_USER",                               "kajona");
define("DB_PASS",                               "kajona");
define("DB_DB",                                 "ora10");
define("DB_DRIVER",                             "oci8");
//define("DB_DRIVER",                             "mysql");
//define("DB_DRIVER",                             "postgres");




ini_set("session.save_path", sys_get_temp_dir());
ini_set("session.use_cookies", "Off");
