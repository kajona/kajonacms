<?php


$strDeployPath = "/Users/sidler/web/agp_v5_master_phar";


$arrCores = scandir("./../");

foreach($arrCores as $strOneCore) {

    if(strpos($strOneCore, "core") === false) {
        continue;
    }

    $arrFiles = scandir("./../".$strOneCore);

    foreach ($arrFiles as $strFile) {

        if (is_dir(__DIR__."/../".$strOneCore."/".$strFile) && substr($strFile, 0, 7) == 'module_') {

            $strModuleName = substr($strFile, 7);
            $strPharName = "module_".$strModuleName.".phar";

            $strTargetPath = __DIR__."/../".$strOneCore."/".$strPharName;
            if ($strDeployPath != "" && is_dir($strDeployPath."/".$strOneCore)) {
                $strTargetPath = $strDeployPath."/".$strOneCore."/".$strPharName;
            }

            $phar = new Phar(
                $strTargetPath,
                FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
                $strPharName
            );
            $phar->buildFromDirectory(__DIR__."/../".$strOneCore."/module_".$strModuleName);
            $phar->setStub($phar->createDefaultStub());
            // Compression with ZIP or GZ?
            //$phar->convertToExecutable(Phar::ZIP);
            //$phar->compress(Phar::GZ);
            echo 'Generated phar '.$strPharName."\n";
        }

        if (is_dir(__DIR__."/../".$strOneCore."/".$strFile) && substr($strFile, 0, 8) == 'element_') {

            $strModuleName = substr($strFile, 8);
            $strPharName = "element_".$strModuleName.".phar";

            $strTargetPath = __DIR__."/../".$strOneCore."/".$strPharName;
            if ($strDeployPath != "" && is_dir($strDeployPath."/".$strOneCore)) {
                $strTargetPath = $strDeployPath."/".$strOneCore."/".$strPharName;
            }

            $phar = new Phar(
                $strTargetPath,
                FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
                $strPharName
            );
            $phar->buildFromDirectory(__DIR__."/../".$strOneCore."/element_".$strModuleName);
            $phar->setStub($phar->createDefaultStub());
            // Compression with ZIP or GZ?
            //$phar->convertToExecutable(Phar::ZIP);
            //$phar->compress(Phar::GZ);
            echo 'Generated phar '.$strPharName."\n";
        }

    }
}
