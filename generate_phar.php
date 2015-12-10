<?php

$arrFiles = scandir(".");

foreach ($arrFiles as $strFile) {

    if (is_dir($strFile) && substr($strFile, 0, 7) == 'module_') {

        $moduleName = substr($strFile, 7);
        $pharName = "module_".$moduleName.".phar";

        $strTargetPath = __DIR__."/".$pharName;
        if (is_dir("/Users/sidler/web/kajona_phar_only/core")) {
            $strTargetPath = "/Users/sidler/web/kajona_phar_only/core/".$pharName;
        }

        $phar = new Phar(
            $strTargetPath,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
            $pharName
        );
        $phar->buildFromDirectory(__DIR__."/module_".$moduleName);
        $phar->setStub($phar->createDefaultStub());
        // Compression with ZIP or GZ?
        //$phar->convertToExecutable(Phar::ZIP);
        //$phar->compress(Phar::GZ);
        echo 'Generated phar '.$pharName."\n";
    }

    if(is_dir($strFile) && substr($strFile, 0, 8) == 'element_')   {

        $moduleName = substr($strFile, 8);
        $pharName = "element_".$moduleName.".phar";

        $strTargetPath = __DIR__."/".$pharName;
        if (is_dir("/Users/sidler/web/kajona_phar_only/core")) {
            $strTargetPath = "/Users/sidler/web/kajona_phar_only/core/".$pharName;
        }

        $phar = new Phar(
            $strTargetPath,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
            $pharName
        );
        $phar->buildFromDirectory(__DIR__."/element_".$moduleName);
        $phar->setStub($phar->createDefaultStub());
        // Compression with ZIP or GZ?
        //$phar->convertToExecutable(Phar::ZIP);
        //$phar->compress(Phar::GZ);
        echo 'Generated phar '.$pharName."\n";
    }

}
