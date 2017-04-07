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
    /**
     * @see \Kajona\System\System\Database
     */
    const STR_DB = "system_db";

    /**
     * @see \Kajona\System\System\Rights
     */
    const STR_RIGHTS = "system_rights";

    /**
     * @see \Kajona\System\System\Config
     */
    const STR_CONFIG = "system_config";

    /**
     * @see \Kajona\System\System\Session
     */
    const STR_SESSION = "system_session";

    /**
     * @see \Kajona\System\Admin\ToolkitAdmin
     */
    const STR_ADMINTOOLKIT = "system_admintoolkit";

    /**
     * @see \Kajona\System\Portal\ToolkitPortal
     */
    const STR_PORTALTOOLKIT = "system_portaltoolkit";

    /**
     * @see \Kajona\System\System\Resourceloader
     */
    const STR_RESOURCE_LOADER = "system_resource_loader";

    /**
     * @see \Kajona\System\System\Classloader
     */
    const STR_CLASS_LOADER = "system_class_loader";

    /**
     * @see \Kajona\System\System\Template
     */
    const STR_TEMPLATE = "system_template";

    /**
     * @see \Kajona\System\System\Lang
     */
    const STR_LANG = "system_lang";

    /**
     * @see \Kajona\System\System\Objectfactory
     */
    const STR_OBJECT_FACTORY = "system_object_factory";

    /**
     * @see \Kajona\System\System\ObjectBuilder
     */
    const STR_OBJECT_BUILDER = "system_object_builder";

    /**
     * @see \Psr\Log\LoggerInterface
     */
    const STR_LOGGER = "system_logger";

    /**
     * @see \Kajona\System\System\CacheManager
     */
    const STR_CACHE_MANAGER = "system_cache_manager";

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
    }
}
