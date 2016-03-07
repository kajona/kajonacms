<?php

if (is_dir(__DIR__."/core/module_system/")) {
    require_once __DIR__.'/core/module_system/bootstrap.php';
} else {
    require_once 'phar://'.__DIR__.'/core/module_system.phar/bootstrap.php';
}

$objDb = \Kajona\System\System\Carrier::getInstance()->getObjDB();

$objDb->_pQuery("UPDATE kajona_system_config SET 'system_config_value' = 'false' WHERE system_config_name = '_admin_only_https_';", array());
$objDb->_pQuery("UPDATE kajona_system_config SET 'system_config_value' = 'false' WHERE system_config_name = '_system_mod_rewrite_';", array());

