<?php

$arrFiles = scandir(".");

foreach ($arrFiles as $strFile) {

    if (is_dir($strFile) && substr($strFile, 0, 7) == 'module_') {

        $moduleName = substr($strFile, 7);
        if ($moduleName != "system") {
          $pharName = "module_" . $moduleName . ".phar";

          $phar = new Phar(__DIR__ . "/" . $pharName,
              FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
              $pharName);
          $phar->buildFromDirectory(__DIR__ . "/module_" . $moduleName);
          $phar->setStub($phar->createDefaultStub());
          $phar->compress(Phar::GZ);
          echo 'Generated phar ' . $pharName . "\n";
        }
    }

}
