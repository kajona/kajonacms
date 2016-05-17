<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Markdown\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\Remoteloader;
use Kajona\System\System\StringUtil;
use Parsedown;


/**
 * Loads the markdown specified in the element-settings and prepares the output
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementMarkdownPortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {

        require_once __DIR__."/../../vendor/autoload.php";

        $arrUrl = parse_url($this->arrElementData["char2"]);

        $objLoader = new Remoteloader();
        $objLoader->setStrProtocolHeader($arrUrl["scheme"]."://");
        $objLoader->setStrHost($arrUrl["host"]);
        $objLoader->setStrQueryParams($arrUrl["path"]);
        $objLoader->setIntPort(null);

        $strFile = $objLoader->getRemoteContent();
        
        $strFile = StringUtil::replace("%%", "\\%\\%", $strFile);

        $objMarkdown = new Parsedown();
        $strParsed = $objMarkdown->text($strFile);

        return $this->objTemplate->fillTemplateFile(
            array("markdown_content" => $strParsed, "markdown_url" => $this->arrElementData["char2"]),
            "/module_markdown/".$this->arrElementData["char1"], "markdown"
        );
    }

}
