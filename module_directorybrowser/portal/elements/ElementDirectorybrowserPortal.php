<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Directorybrowser\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\Filesystem;


/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementDirectorybrowserPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {
        $strReturn = "";

        //Load all files in the folder
        $objFilesystem = new Filesystem();
        $arrFiles = $objFilesystem->getFilelist($this->arrElementData["char2"]);

        $strContent = "";
        foreach ($arrFiles as $strOneFile) {
            $arrDetails = $objFilesystem->getFileDetails($this->arrElementData["char2"]."/".$strOneFile);

            $arrTemplate = array();
            $arrTemplate["file_name"] = $arrDetails["filename"];
            $arrTemplate["file_href"] = _webpath_.$this->arrElementData["char2"]."/".$strOneFile;
            $arrTemplate["file_date"] = timeToString($arrDetails["filechange"]);
            $arrTemplate["file_size"] = bytesToString($arrDetails["filesize"]);

            $strContent .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_directorybrowser/".$this->arrElementData["char1"], "directorybrowser_entry");
        }


        $strReturn .= $this->objTemplate->fillTemplateFile(array("files" => $strContent), "/module_directorybrowser/".$this->arrElementData["char1"], "directorybrowser_wrapper");

        return $strReturn;
    }

}
