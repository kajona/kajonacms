<?php
/**
 * Modifies the .htaccess file by adding "Allow from all" at end of file
 */
$strServerRootPath = $argv[1];

modifyHtaccess($strServerRootPath);

function modifyHtaccess($strServerRootPath)
{
    $strFileName = $strServerRootPath . ".htaccess";
    $strContent = PHP_EOL."Allow from all".PHP_EOL;
    file_put_contents($strFileName, $strContent, FILE_APPEND);
}