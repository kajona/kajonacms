<?php


if (is_dir(__DIR__."/core/module_system/")) {
    require_once __DIR__.'/core/module_system/bootstrap.php';
} else {
    require_once 'phar://'.__DIR__.'/core/module_system.phar/bootstrap.php';
}

\Kajona\System\System\PharModuleExtractor::bootstrapPharContent();

