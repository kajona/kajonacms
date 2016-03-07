<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Filesystem;
use Kajona\System\System\TemplateFileParser;
use Kajona\System\System\Testbase;

class TemplateIncludeTest extends Testbase
{

    public function testTemplateIncludes()
    {

        $objFilesystem = new Filesystem();
        $objFilesystem->folderCreate("/templates/default/tpl/test", true);

        file_put_contents(_realpath_ . "/templates/default/tpl/test/test1.tpl", "
            page template

            [KajonaTemplateInclude,/test/test2.tpl]
        ");

        $this->assertFileExists(_realpath_ . "/templates/default/tpl/test/test1.tpl");


        file_put_contents(_realpath_ . "/templates/default/tpl/test/test2.tpl", "template 2");

        $this->assertFileExists(_realpath_ . "/templates/default/tpl/test/test2.tpl");


        $objParser = new TemplateFileParser();
        $strContent = $objParser->readTemplate("/test/test1.tpl");

        $this->assertEquals($strContent, "
            page template

            template 2
        ");

        $objFilesystem->fileDelete("/templates/default/tpl/test/test1.tpl");
        $objFilesystem->fileDelete("/templates/default/tpl/test/test2.tpl");
    }


}

