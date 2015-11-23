<?php

namespace Kajona\Pages\System;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        define("_pages_modul_id_", 10);

        // ID der Inhalts-Verwaltung
        /**
         * @deprected migrate to _pages_content_modul_id_ instead.
         */
        define("_pages_inhalte_modul_id_", 11);
        define("_pages_content_modul_id_", 11);

        // ID der Element-Verwaltung
        define("_pages_elemente_modul_id_", 12);

        // ID der Ordnerverwaltung
        define("_pages_folder_id_", 14);
    }
}
