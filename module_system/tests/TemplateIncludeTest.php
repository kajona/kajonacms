<?php

namespace Kajona\System\Tests;
use class_testbase;

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class TemplateIncludeTest extends class_testbase
{

    public function testTemplateIncludes()
    {

        $objFilesystem = new \class_filesystem();
        $objFilesystem->folderCreate("/templates/default/tpl/test", true);

        file_put_contents(_realpath_."/templates/default/tpl/test/test1.tpl", "
            page template

            [KajonaTemplateInclude,/test/test2.tpl]
        ");

        $this->assertFileExists(_realpath_."/templates/default/tpl/test/test1.tpl");



        file_put_contents(_realpath_."/templates/default/tpl/test/test2.tpl", "template 2");

        $this->assertFileExists(_realpath_."/templates/default/tpl/test/test2.tpl");


        $objParser = new \class_template_file_parser();
        $strContent = $objParser->readTemplate("/test/test1.tpl");

        $this->assertEquals($strContent, "
            page template

            template 2
        ");

        $objFilesystem->fileDelete("/templates/default/tpl/test/test1.tpl");
        $objFilesystem->fileDelete("/templates/default/tpl/test/test2.tpl");
    }


}

