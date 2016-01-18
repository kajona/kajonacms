<?php

$strCurModule = substr(__DIR__, strrpos(__DIR__, "_")+1);

echo "Starting migration on ".$strCurModule."@".__DIR__."\n";

if (!is_dir(__DIR__."/legacy")) {
    mkdir(__DIR__."/legacy");

    echo "added /legacy\n";
}


foreach (
    array(
        "/admin/elements",
        "/admin/formentries",
        "/admin/statsreports",
        "/admin/systemtasks",
        "/admin/widgets",
        "/admin",
        "/portal/elements",
        "/portal/forms",
        "/portal/templatemapper",
        "/portal",
        "/system/db",
        "/system/usersources",
        "/system/imageplugins",
        "/system/validators",
        "/system/workflows",
        "/system/messageproviders",
        "/system/scriptlets",
        "/system",
        "/event"
    )
    as $strOneDir) {

    if (!is_dir(__DIR__.$strOneDir)) {
        continue;
    }

    //echo "Checking ".__DIR__.$strOneDir."\n";


    foreach (scandir(__DIR__.$strOneDir) as $strOneFile) {

        if (substr($strOneFile, -4) != ".php") {
            continue;
        }


        $strPath = "kajona/".$strCurModule.$strOneDir;
        $arrPath = explode("/", $strPath);
        $arrPath = array_map(function($strPath) {
            return ucfirst($strPath);
        }, $arrPath);

        $strNamespace = implode("\\", $arrPath);

        if (strpos($strOneFile, "class") !== false) {
            $strClassname = substr($strOneFile, 0, -4);

            $strNewClassname = str_replace("class_module_", "", $strClassname);
            $strNewClassname = str_replace("class_", "", $strNewClassname);
            $arrNewClassname = explode("_", $strNewClassname);

            $arrNewClassname = array_map(function($strPath) {
                return ucfirst($strPath);
            }, $arrNewClassname);
            $strNewClassname = implode("", $arrNewClassname);

            echo "migrating class ".$strClassname."@".$strOneFile."\n";
            echo "namespace: ".$strNamespace."\n";
            echo "new classname: ".$strNewClassname."\n";




            $strLegacyClass = <<<PHP
<?php
/*"******************************************************************************************************
*   (c) 2016 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * @deprecated
 */
class {$strClassname} extends {$strNamespace}\\{$strNewClassname}
{

}

PHP;

            file_put_contents(__DIR__."/legacy/".$strOneFile, $strLegacyClass);
            echo "wrote legacy class to ".__DIR__."/legacy/".$strOneFile."\n";

            exec("git mv ".__DIR__.$strOneDir."/".$strOneFile."  ".__DIR__.$strOneDir."/".$strNewClassname.".php");

            $strOldClass = file_get_contents(__DIR__.$strOneDir."/".$strNewClassname.".php");

            $strOldClass = str_replace($strClassname, $strNewClassname, $strOldClass);

            $strOldClass = str_replace("*********/", "*********/\n\nnamespace {$strNamespace};\n", $strOldClass);

            file_put_contents(__DIR__.$strOneDir."/".$strNewClassname.".php", $strOldClass);
            echo "wrote new class to ".__DIR__.$strOneDir."/".$strNewClassname.".php\n";


        }
    }
}