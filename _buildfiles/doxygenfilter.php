#!/usr/bin/php
<?php

//extract the package tag to a different file
$strContent = file_get_contents($argv[1]);

$offset = strpos($strContent, "* @package");
if($offset !== false) {
    $offset += 2;
    $strTag = substr($strContent, $offset, strpos($strContent, "\n", $offset)-$offset);
    $strContent = str_replace($strTag, "", $strContent);
    $strTag = str_replace("@package ", "", $strTag);
//    $strContent = str_replace("@package", "@addtogroup", $strContent);
    $strContent = str_replace("<?php", "<?php\n/**\n* @package ".$strTag."\n*/\n", $strContent);
}
echo $strContent;

