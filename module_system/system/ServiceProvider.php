<?php

namespace Kajona\System\System;

use Kajona\System\Portal\ToolkitPortal;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * ServiceProvider
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 4.6
 */
class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $objContainer)
    {
        $objContainer['db'] = function ($c) {
            return Database::getInstance();
        };

        $objContainer['rights'] = function ($c) {
            return Rights::getInstance();
        };

        $objContainer['config'] = function ($c) {
            return Config::getInstance();
        };

        $objContainer['session'] = function ($c) {
            return Session::getInstance();
        };

        $objContainer['admintoolkit'] = function ($c) {
            // decide which class to load
            $strAdminToolkitClass = $c["config"]->getConfig("admintoolkit");
            if ($strAdminToolkitClass == "") {
                $strAdminToolkitClass = "ToolkitAdmin";
            }

            $strPath = Resourceloader::getInstance()->getPathForFile("/admin/".$strAdminToolkitClass.".php");
            return Classloader::getInstance()->getInstanceFromFilename($strPath);
        };

        $objContainer['portaltoolkit'] = function ($c) {
            $strPath = Resourceloader::getInstance()->getPathForFile("/portal/ToolkitPortal.php");
            include_once $strPath;

            return new ToolkitPortal();
        };

        $objContainer['resource_loader'] = function ($c) {
            return Resourceloader::getInstance();
        };

        $objContainer['class_loader'] = function ($c) {
            return Classloader::getInstance();
        };

        $objContainer['template'] = function ($c) {
            return Template::getInstance();
        };

        $objContainer['lang'] = function ($c) {
            return Lang::getInstance();
        };

        $objContainer['object_factory'] = function ($c) {
            return Objectfactory::getInstance();
        };

        $objContainer['object_builder'] = function ($c) {
            return new ObjectBuilder($c);
        };

        $objContainer['logger'] = function ($c) {
            return Logger::getInstance();
        };

        $objContainer['cache_manager'] = function($c){
            return new CacheManager();
        };
    }
}
