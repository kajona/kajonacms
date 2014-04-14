#!/usr/bin/php
<?php

echo "compiling skin css files...\n";

//files to compile

$arrFilesToCompile = array(
    __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/less/bootstrap.less" => __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/less/styles.css",
    __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/less/responsive.less" => __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/less/responsive.css",
    __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/less/bootstrap_pe.less" => __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/less/styles_pe.css",
);

$strSkinReplacement = "";
$strSkinReplacement = <<<TXT
    <link rel="stylesheet" href="_skinwebpath_/less/styles.css?_system_browser_cachebuster_" type="text/css" />
    <link rel="stylesheet" href="_skinwebpath_/less/responsive.css?_system_browser_cachebuster_" type="text/css" />
TXT;


$strPeReplacement = "";
$strPeReplacement = <<<TXT
    <link rel="stylesheet" href="_webpath_/core/module_v4skin/admin/skins/kajona_v4/less/styles_pe.css?_system_browser_cachebuster_" type="text/css" />
TXT;

$arrFilesToUpdate = array(
    __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/main.tpl" => $strSkinReplacement,
    __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/folderview.tpl" => $strSkinReplacement,
    __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/login.tpl" => $strSkinReplacement,
    __DIR__."/../temp/kajona/core_agp/module_agpskin/admin/skins/agp/elements.tpl" => $strPeReplacement
);


foreach($arrFilesToCompile as $strSourceFile => $strTargetFile) {
    //echo "trigger less compile: lessc ".escapeshellarg($strSourceFile)." ".escapeshellarg($strTargetFile)."\n";
    if(is_file($strSourceFile))
        system("lessc --verbose ".escapeshellarg($strSourceFile)." ".escapeshellarg($strTargetFile));
    else
        echo "Skipping ".$strSourceFile.", not existing\n";
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
