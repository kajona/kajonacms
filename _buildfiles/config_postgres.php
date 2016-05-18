<?php 

//database-settings
define("DB_HOST",				                "localhost");
define("DB_USER",                               "kajona_v4");
define("DB_PASS",                               "kajona_v4");
define("DB_DB",                                 "kajona_v4");
//define("DB_DRIVER",                             "oci8");
//define("DB_DRIVER",                             "mysqli");
define("DB_DRIVER",                             "postgres");




ini_set("session.save_path", sys_get_temp_dir());
ini_set("session.use_cookies", "Off");
