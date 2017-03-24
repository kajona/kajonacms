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
    const STR_DB = "system_db";
    const STR_RIGHTS = "system_rights";
    const STR_CONFIG = "system_config";
    const STR_SESSION = "system_session";
    const STR_ADMINTOOLKIT = "system_admintoolkit";
    const STR_PORTALTOOLKIT = "system_portaltoolkit";
    const STR_RESOURCE_LOADER = "system_resource_loader";
    const STR_CLASS_LOADER = "system_class_loader";
    const STR_TEMPLATE = "system_template";
    const STR_LANG = "system_lang";
    const STR_OBJECT_FACTORY = "system_object_factory";
    const STR_OBJECT_BUILDER = "system_object_builder";
    const STR_LOGGER = "system_logger";
    const STR_CACHE_MANAGER = "system_cache_manager";
    const STR_LIFE_CYCLE_FACTORY = "system_life_cycle_factory";
    const STR_LIFE_CYCLE_DEFAULT = "system_life_cycle_default";


    public function register(Container $objContainer)
    {
        $objContainer[self::STR_DB] = function ($c) {
            return Database::getInstance();
        };

        $objContainer[self::STR_RIGHTS] = function ($c) {
            return Rights::getInstance();
        };

        $objContainer[self::STR_CONFIG] = function ($c) {
            return Config::getInstance();
        };

        $objContainer[self::STR_SESSION] = function ($c) {
            return Session::getInstance();
        };

        $objContainer[self::STR_ADMINTOOLKIT] = function ($c) {
            // decide which class to load
            $strAdminToolkitClass = $c[self::STR_CONFIG]->getConfig("admintoolkit");
            if ($strAdminToolkitClass == "") {
                $strAdminToolkitClass = "ToolkitAdmin";
            }

            $strPath = Resourceloader::getInstance()->getPathForFile("/admin/".$strAdminToolkitClass.".php");
            return Classloader::getInstance()->getInstanceFromFilename($strPath);
        };

        $objContainer[self::STR_PORTALTOOLKIT] = function ($c) {
            $strPath = Resourceloader::getInstance()->getPathForFile("/portal/ToolkitPortal.php");
            include_once $strPath;

            return new ToolkitPortal();
        };

        $objContainer[self::STR_RESOURCE_LOADER] = function ($c) {
            return Resourceloader::getInstance();
        };

        $objContainer[self::STR_CLASS_LOADER] = function ($c) {
            return Classloader::getInstance();
        };

        $objContainer[self::STR_TEMPLATE] = function ($c) {
            return new Template(
                new TemplateFileParser(),
                new TemplateSectionParser(),
                new TemplatePlaceholderParser(),
                new TemplateBlocksParser()
            );
        };

        $objContainer[self::STR_LANG] = function ($c) {
            return Lang::getInstance();
        };

        $objContainer[self::STR_OBJECT_FACTORY] = function ($c) {
            return Objectfactory::getInstance();
        };

        $objContainer[self::STR_OBJECT_BUILDER] = function ($c) {
            return new ObjectBuilder($c);
        };

        $objContainer[self::STR_LOGGER] = function ($c) {
            return Logger::getInstance();
        };

        $objContainer[self::STR_CACHE_MANAGER] = function ($c) {
            return new CacheManager();
        };

        $objContainer[self::STR_LIFE_CYCLE_FACTORY] = function ($c) {
            return new ServiceLifeCycleFactory($c);
        };

        $objContainer[self::STR_LIFE_CYCLE_DEFAULT] = function ($c) {
            return new ServiceLifeCycleImpl();
        };
    }
}
