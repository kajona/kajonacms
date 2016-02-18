<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
use Kajona\System\System\SystemModule;
use Kajona\Tags\System\TagsTag;

/**
 * Loads the tags currently available in the system and renders them
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementTagsPortal extends ElementPortal implements PortalElementInterface {


    /**
     * Looks up the list of tags and renders the list.
     *
     * @return string the prepared html-output
     */
    public function loadData() {
        $strReturn = "";


        $arrTags = TagsTag::getTagsWithAssignments();

        //load the template
        $strTemplateWrapperID = $this->objTemplate->readTemplate("/element_tags/".$this->arrElementData["char1"], "tags");
        $strTemplateTagID = $this->objTemplate->readTemplate("/element_tags/".$this->arrElementData["char1"], "tagname");
        $strTemplateTaglinkID = $this->objTemplate->readTemplate("/element_tags/".$this->arrElementData["char1"], "taglink");


        $strTags = "";
        foreach($arrTags as $objTag) {
            if($objTag->rightView()) {

                $arrAssignments = $objTag->getListOfAssignments();


                $strLinks = "";
                //render the links - if possible
                foreach($arrAssignments as $arrOneAssignment) {
                    $objRecord = Objectfactory::getInstance()->getObject($arrOneAssignment["tags_systemid"]);

                    if($objRecord == null) {
                        continue;
                    }

                    if($objRecord instanceof PagesPage) {
                        $strLink = Link::getLinkPortal($objRecord->getStrName(), "", "_self", $objRecord->getStrBrowsername(), "", "&highlight=".urlencode($objTag->getStrName()), "", "", $arrOneAssignment["tags_attribute"]);
                        $strLinks .= $this->fillTemplate(array("taglink" => $strLink), $strTemplateTaglinkID);
                    }

                    if(SystemModule::getModuleByName("news") != null && $objRecord->getIntModuleNr() == _news_module_id_) {
                        //TODO: fix after news integration, move to search link target interface handler
                        $objNews = new NewsNews($objRecord->getSystemid());
                        $strLink = Link::getLinkPortal("newsdetails", "", "_self", $objNews->getStrTitle(), "newsDetail", "&highlight=".urlencode($objTag->getStrName()), $objRecord->getSystemid(), "", "", $objNews->getStrTitle());
                        $strLinks .= $this->fillTemplate(array("taglink" => $strLink), $strTemplateTaglinkID);
                    }

                }

                $arrTemplate = array();
                $arrTemplate["tagname"] = $objTag->getStrName();
                $arrTemplate["linkcount"] = count($arrAssignments);
                $arrTemplate["taglinks"] = $strLinks;
                $arrTemplate["tagid"] = $objTag->getSystemid();
                $strTags .= $this->fillTemplate($arrTemplate, $strTemplateTagID);
            }
        }

        $strReturn = $this->fillTemplate(array("tags" => $strTags), $strTemplateWrapperID);

        return $strReturn;
    }

}
