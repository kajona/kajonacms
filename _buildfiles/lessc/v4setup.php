#!/usr/bin/php
<?php

echo "compiling skin css files...\n";

//files to compile

$arrFilesToCompile = array(
    __DIR__."/../temp/kajona/core/module_v4skin/admin/skins/kajona_v4/less/bootstrap.less" => __DIR__."/../temp/kajona/core/module_v4skin/admin/skins/kajona_v4/less/styles.css",
    __DIR__."/../temp/kajona/core/module_v4skin/admin/skins/kajona_v4/less/bootstrap_pe.less" => __DIR__."/../temp/kajona/core/module_v4skin/admin/skins/kajona_v4/less/styles_pe.css",

    __DIR__."/../temp/kajona/core/module_installer/less/bootstrap.less" => __DIR__."/../temp/kajona/core/module_installer/less/styles.css"
);

$strSkinReplacement = "";
$strSkinReplacement = <<<TXT
    <link rel="stylesheet" href="_skinwebpath_/less/styles.css?_system_browser_cachebuster_" type="text/css" />
TXT;

$strInstallerReplacement = "";
$strInstallerReplacement = <<<TXT
    <link rel="stylesheet" href="_webpath_/core/module_installer/less/styles.css?_system_browser_cachebuster_" type="text/css" />
TXT;


$strPeReplacement = "";
$strPeReplacement = <<<TXT
    <link rel="stylesheet" href="_skinwebpath_/less/styles_pe.css?_system_browser_cachebuster_" type="text/css" />
TXT;

$arrFilesToUpdate = array(
    __DIR__."/../temp/kajona/core/module_v4skin/admin/skins/kajona_v4/main.tpl" => $strSkinReplacement,
    __DIR__."/../temp/kajona/core/module_v4skin/admin/skins/kajona_v4/folderview.tpl" => $strSkinReplacement,
    __DIR__."/../temp/kajona/core/module_v4skin/admin/skins/kajona_v4/login.tpl" => $strSkinReplacement,
    __DIR__."/../temp/kajona/core/module_v4skin/admin/skins/kajona_v4/elements.tpl" => $strPeReplacement,
    __DIR__."/../temp/kajona/core/module_installer/installer.tpl" => $strInstallerReplacement
);


foreach($arrFilesToCompile as $strSourceFile => $strTargetFile) {
    //echo "trigger less compile: lessc ".escapeshellarg($strSourceFile)." ".escapeshellarg($strTargetFile)."\n";
    if (is_file($strSourceFile)) {
        $strLessBin = "node " . __DIR__ . "/../jstests/node_modules/less/bin/lessc";
        system($strLessBin . " --verbose " . escapeshellarg($strSourceFile) . " " . escapeshellarg($strTargetFile));
    } else {
        echo "Skipping ".$strSourceFile.", not existing\n";
    }
}

echo "merging into skin-files...\n";
$strStartPlaceholder = "<!-- KAJONA_BUILD_LESS_START -->";
$strEndPlaceholder = "<!-- KAJONA_BUILD_LESS_END -->";

foreach($arrFilesToUpdate as $strOneFile => $strReplacement) {

    if(!is_file($strOneFile)) {
        echo "Skipping ".$strOneFile.", not existing\n";
        continue;
    }

    $strContent = file_get_contents($strOneFile);
    $strPrologue = substr($strContent, 0, strpos($strContent, $strStartPlaceholder));
    $strEnd = substr($strContent, strpos($strContent, $strEndPlaceholder)+strlen($strEndPlaceholder));
    $strContent = $strPrologue.$strReplacement.$strEnd;
    file_put_contents($strOneFile, $strContent);
}
