<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

namespace Kajona\Faqs\System;

use Kajona\Pages\System\PagesPage;
use Kajona\Search\System\SearchResult;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\SearchPortalobjectInterface;
use Kajona\System\System\SystemModule;


/**
 * Model for a faqs category
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 * @targetTable faqs_category.faqs_cat_id
 *
 * @module faqs
 * @moduleId _faqs_module_id_
 */
class FaqsCategory extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, AdminListableInterface, SearchPortalobjectInterface
{

    /**
     * @var string
     * @tableColumn faqs_category.faqs_cat_title
     * @tableColumnDatatype char254
     * @listOrder asc
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     *
     * @addSearchIndex
     * @templateExport
     */
    private $strTitle = "";


    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon()
    {
        return "icon_folderClosed";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrTitle();
    }


    /**
     * Return an on-click link for the passed object.
     * This link is rendered by the portal search result generator, so
     * make sure the link is a valid portal page.
     * If you want to suppress the entry from the result, return an empty string instead.
     *
     * @param SearchResult $objResult
     *
     * @see getLinkPortalHref()
     * @return mixed
     */
    public function updateSearchResult(SearchResult $objResult)
    {
        //search for matching pages
        $arrReturn = array();

        $objORM = new OrmObjectlist();

        $strQuery = "SELECT system_id
                       FROM " . _dbprefix_ . "element_faqs,
                            " . _dbprefix_ . "page_element,
                            " . _dbprefix_ . "system
                      WHERE content_id = page_element_id
                        AND content_id = system_id
                        AND (
                            faqs_category IS NULL OR (
                                faqs_category = '0' OR faqs_category = ?
                            )
                        )
                        AND system_status = 1
                        " . $objORM->getDeletedWhereRestriction() . "
                        AND page_element_ph_language = ? ";

        $arrRows = $this->objDB->getPArray($strQuery, array($this->getSystemid(), $objResult->getObjSearch()->getStrPortalLangFilter()));

        foreach ($arrRows as $arrOneElement) {

            $objCur = Objectfactory::getInstance()->getObject($arrOneElement["system_id"]);
            while($objCur != null && !$objCur instanceof PagesPage && !$objCur instanceof SystemModule) {
                $objCur = Objectfactory::getInstance()->getObject($objCur->getStrPrevId());
            }

            if ($objCur instanceof PagesPage && $objCur->getStrName() != 'master') {
                $objCurResult = clone($objResult);
                $objCurResult->setStrPagelink(Link::getLinkPortal($objCur->getStrName(), "", "_self", $objCur->getStrBrowsername(), "", "&highlight=".urlencode(html_entity_decode($objResult->getObjSearch()->getStrQuery(), ENT_QUOTES, "UTF-8"))));
                $objCurResult->setStrPagename($objCur->getStrName());
                $objCurResult->setStrDescription($this->getStrTitle());
                $arrReturn[] = $objCurResult;

            }
        }

        return $arrReturn;
    }

    /**
     * Since the portal may be split in different languages,
     * return the content lang of the current record using the common
     * abbreviation such as "de" or "en".
     * If the content is not assigned to any language, return "" instead (e.g. a single image).
     *
     * @return mixed
     */
    public function getContentLang()
    {
        return "";
    }

    /**
     * Return an on-lick link for the passed object.
     * This link is used by the backend-search for the autocomplete-field
     *
     * @see getLinkAdminHref()
     * @return mixed
     */
    public function getSearchAdminLinkForObject()
    {
        return "";
    }


    public function getStrTitle()
    {
        return $this->strTitle;
    }

    public function setStrTitle($strTitle)
    {
        $this->strTitle = $strTitle;
    }

}
