<?php

$arrFiles = scandir(".");

foreach ($arrFiles as $strFile) {

    if (is_dir($strFile) && substr($strFile, 0, 7) == 'module_') {

        $moduleName = substr($strFile, 7);
        $pharName = "module_" . $moduleName . ".phar";

        $phar = new Phar(__DIR__ . "/" . $pharName,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
            $pharName);
        $phar->buildFromDirectory(__DIR__ . "/module_" . $moduleName);
//$phar["index.php"] = file_get_contents($srcRoot . "/index.php");
//$phar["common.php"] = file_get_contents($srcRoot . "/common.php");
        $phar->setStub($phar->createDefaultStub());

//copy($srcRoot . "/config.ini", $buildRoot . "/config.ini");

        echo 'Generated phar ' . $pharName . "\n";
    }

}

