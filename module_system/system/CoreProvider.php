<?php

namespace Kajona\System\System;

use class_config;
use class_db;
use class_lang;
use class_logger;
use class_objectfactory;
use class_resourceloader;
use class_rights;
use class_session;
use class_template;
use class_toolkit_portal;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * CoreProvider
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 4.6
 */
class CoreProvider implements ServiceProviderInterface
{
    public function register(Container $objContainer)
    {
        $objContainer['db'] = function($c){
            return class_db::getInstance();
        };

        $objContainer['rights'] = function($c){
            return class_rights::getInstance();
        };

        $objContainer['config'] = function($c){
            return class_config::getInstance();
        };

        $objContainer['session'] = function($c){
            return class_session::getInstance();
        };

        $objContainer['admintoolkit'] = function($c){
            // decide which class to load
            $strAdminToolkitClass = $c['config']->getConfig("admintoolkit");
            if ($strAdminToolkitClass == "") {
                $strAdminToolkitClass = "class_toolkit_admin";
            }

            $strPath = $c['resource_loader']->getPathForFile("/admin/".$strAdminToolkitClass.".php");
            include_once _realpath_.$strPath;

            return new $strAdminToolkitClass();
        };

        $objContainer['portaltoolkit'] = function($c){
            $strPath = $c['resource_loader']->getPathForFile("/portal/class_toolkit_portal.php");
            include_once _realpath_.$strPath;

            return new class_toolkit_portal();
        };

        $objContainer['resource_loader'] = function($c){
            return class_resourceloader::getInstance();
        };

        $objContainer['class_loader'] = function($c){
            return \class_classloader::getInstance();
        };

        $objContainer['template'] = function($c){
            return class_template::getInstance();
        };

        $objContainer['lang'] = function($c){
            return class_lang::getInstance();
        };

        $objContainer['object_factory'] = function($c){
            return class_objectfactory::getInstance();
        };

        $objContainer['object_builder'] = function($c){
            return new ObjectBuilder($c);
        };

        $objContainer['logger'] = function($c){
            return class_logger::getInstance();
        };
    }
}
