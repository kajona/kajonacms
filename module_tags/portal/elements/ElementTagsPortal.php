<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Tags\Portal\Elements;

use Kajona\News\System\NewsNews;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPage;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\Tags\System\TagsTag;

/**
 * Loads the tags currently available in the system and renders them
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementTagsPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * Looks up the list of tags and renders the list.
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {
        $arrTags = TagsTag::getTagsWithAssignments();

        $strTags = "";
        foreach ($arrTags as $objTag) {
            if ($objTag->rightView()) {

                $arrAssignments = $objTag->getListOfAssignments();


                $strLinks = "";
                //render the links - if possible
                foreach ($arrAssignments as $arrOneAssignment) {
                    $objRecord = Objectfactory::getInstance()->getObject($arrOneAssignment["tags_systemid"]);

                    if ($objRecord == null) {
                        continue;
                    }

                    if ($objRecord instanceof PagesPage) {
                        $strLink = Link::getLinkPortal($objRecord->getStrName(), "", "_self", $objRecord->getStrBrowsername(), "", "&highlight=".urlencode($objTag->getStrName()), "", "", $arrOneAssignment["tags_attribute"]);
                        $strLinks .= $this->objTemplate->fillTemplateFile(array("taglink" => $strLink), "/element_tags/".$this->arrElementData["char1"], "taglink");
                    }

                    if (get_class($objRecord) == 'Kajona\News\System\NewsNews') {
                        //TODO: move to search link target interface handler
                        $objNews = new NewsNews($objRecord->getSystemid());
                        $strLink = Link::getLinkPortal("newsdetails", "", "_self", $objNews->getStrTitle(), "newsDetail", "&highlight=".urlencode($objTag->getStrName()), $objRecord->getSystemid(), "", "", $objNews->getStrTitle());
                        $strLinks .= $this->objTemplate->fillTemplateFile(array("taglink" => $strLink), "/element_tags/".$this->arrElementData["char1"], "taglink");
                    }

                }

                $arrTemplate = array();
                $arrTemplate["tagname"] = $objTag->getStrName();
                $arrTemplate["linkcount"] = count($arrAssignments);
                $arrTemplate["taglinks"] = $strLinks;
                $arrTemplate["tagid"] = $objTag->getSystemid();
                $strTags .= $this->objTemplate->fillTemplateFile($arrTemplate, "/element_tags/".$this->arrElementData["char1"], "tagname");
            }
        }

        $strReturn = $this->objTemplate->fillTemplateFile(array("tags" => $strTags), "/element_tags/".$this->arrElementData["char1"], "tags");

        return $strReturn;
    }

}
