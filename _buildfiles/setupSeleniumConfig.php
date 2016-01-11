<?php

require_once './core/module_system/bootstrap.php';


$objDb = class_carrier::getInstance()->getObjDB();

$objDb->_pQuery("UPDATE autotest_system_config SET 'system_config_value' = 'false' WHERE system_config_name = '_admin_only_https_';", array());
$objDb->_pQuery("UPDATE autotest_system_config SET 'system_config_value' = 'false' WHERE system_config_name = '_system_mod_rewrite_';", array());

