<?php

$arrFiles = array('download.php', 'image.php', 'index.php', 'xml.php', 'installer.php', 'setupSeleniumConfig.php');
$bitFound = false;

chdir(__DIR__ . "/seleniumproject");

foreach ($arrFiles as $strFile) {
    if (substr($_SERVER['REQUEST_URI'], 1, strlen($strFile)) == $strFile) {
        require_once $strFile;
        $bitFound = true;
        break;
    }
}

if (!$bitFound) {
    // if we return false the PHP webserver tries to load the file from the file path
    return false;
}
