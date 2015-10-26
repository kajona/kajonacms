<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Loads the markdown specified in the element-settings and prepares the output
 *
 * @package element_markdown
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_markdown_portal extends class_element_portal implements interface_portal_element {

    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
    public function loadData() {
        
        require_once class_resourceloader::getInstance()->getCorePathForModule("element_markdown", true)."/element_markdown/system/parsedown/vendor/autoload.php";

        $arrUrl = parse_url($this->arrElementData["char2"]);

        $objLoader = new class_remoteloader();
        $objLoader->setStrProtocolHeader($arrUrl["scheme"]."://");
        $objLoader->setStrHost($arrUrl["host"]);
        $objLoader->setStrQueryParams($arrUrl["path"]);
        $objLoader->setIntPort(null);

        $strFile = $objLoader->getRemoteContent();

        $objMarkdown = new Parsedown();
        $strParsed = $objMarkdown->text($strFile);

        return $this->objTemplate->fillTemplate(
            array("markdown_content" => $strParsed, "markdown_url" => $this->arrElementData["char2"]),
            $this->objTemplate->readTemplate("/element_markdown/".$this->arrElementData["char1"], "markdown"),
            true
        );
    }

}
