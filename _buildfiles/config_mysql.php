<?php 

//database-settings
define("DB_HOST",				                "localhost");
define("DB_USER",                               "kajona");
define("DB_PASS",                               "kajona");
define("DB_DB",                                 "kajona");
//define("DB_DRIVER",                             "oci8");
define("DB_DRIVER",                             "mysqli");
//define("DB_DRIVER",                             "postgres");




ini_set("session.save_path", "/tmp");
ini_set("session.use_cookies", "Off");
