<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

namespace Kajona\Postacomment\System;

use Kajona\Pages\System\PagesPage;
use Kajona\Search\System\SearchResult;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Link;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistOrderby;
use Kajona\System\System\OrmObjectlistRestriction;
use Kajona\System\System\SearchPortalobjectInterface;
use Kajona\System\System\SortableRatingInterface;


/**
 * Model for comment itself
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @targetTable postacomment.postacomment_id
 *
 * @module postacomment
 * @moduleId _postacomment_modul_id_
 */
class PostacommentPost extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, SortableRatingInterface, AdminListableInterface, SearchPortalobjectInterface
{

    /**
     * @var string
     * @tableColumn postacomment.postacomment_title
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel commons_title
     *
     * @addSearchIndex
     */
    private $strTitle;

    /**
     * @var string
     * @tableColumn postacomment.postacomment_comment
     * @tableColumnDatatype text
     *
     * @fieldMandatory
     * @fieldType Kajona\System\Admin\Formentries\FormentryTextarea
     * @fieldLabel postacomment_comment
     *
     * @addSearchIndex
     */
    private $strComment;

    /**
     * @var string
     * @tableColumn postacomment.postacomment_username
     * @tableColumnDatatype char254
     *
     * @fieldMandatory
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel postacomment_username
     */
    private $strUsername;

    /**
     * @var int
     * @tableColumn postacomment.postacomment_date
     * @tableColumnDatatype int
     */
    private $intDate;

    /**
     * @var string
     * @tableColumn postacomment.postacomment_page
     * @tableColumnDatatype char254
     */
    private $strAssignedPage;

    /**
     * @var string
     * @tableColumn postacomment.postacomment_systemid
     * @tableColumnDatatype char20
     */
    private $strAssignedSystemid;

    /**
     * @var string
     * @tableColumn postacomment.postacomment_language
     * @tableColumnDatatype char20
     */
    private $strAssignedLanguage;


    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrTitle();
    }

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
        return "icon_comment";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return timeToString($this->intDate);
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return uniStrTrim($this->strComment, 120);
    }


    /**
     * Returns a list of posts
     *
     * @param bool $bitJustActive
     * @param string $strPagefilter
     * @param string $strSystemidfilter false to ignore the filter
     * @param string $strLanguagefilter
     * @param bool $intStart
     * @param bool $intEnd
     *
     * @return PostacommentPost[]
     */
    public static function loadPostList($bitJustActive = true, $strPagefilter = "", $strSystemidfilter = "", $strLanguagefilter = "", $intStart = null, $intEnd = null)
    {

        $objORM = new OrmObjectlist();
        if ($strPagefilter != "") {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND postacomment_page = ? ", $strPagefilter));
        }

        if ($strSystemidfilter != "") {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND postacomment_systemid = ? ", $strSystemidfilter));
        }

        if ($strLanguagefilter != "") {//check against '' to remain backwards-compatible
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND (postacomment_language = ? OR postacomment_language = '')", $strLanguagefilter));
        }
        if ($bitJustActive) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND system_status = ? ", 1));
        }

        $objORM->addOrderBy(new OrmObjectlistOrderby("postacomment_page ASC"));
        $objORM->addOrderBy(new OrmObjectlistOrderby("postacomment_language ASC"));
        $objORM->addOrderBy(new OrmObjectlistOrderby("postacomment_date DESC"));

        return $objORM->getObjectList(get_called_class(), "", $intStart, $intEnd);
    }

    /**
     * Counts the number of posts currently in the database
     *
     * @param bool $bitJustActive
     * @param string $strPageid
     * @param bool|string $strSystemidfilter false to ignore the filter
     * @param string $strLanguagefilter
     *
     * @return int
     */
    public static function getNumberOfPostsAvailable($bitJustActive = true, $strPageid = "", $strSystemidfilter = "", $strLanguagefilter = "")
    {

        $objORM = new OrmObjectlist();
        if ($strPageid != "") {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND postacomment_page = ? ", $strPageid));
        }

        if ($strSystemidfilter != "") {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND postacomment_systemid = ? ", $strSystemidfilter));
        }

        if ($strLanguagefilter != "") {//check against '' to remain backwards-compatible
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND (postacomment_language = ? OR postacomment_language = '')", $strLanguagefilter));
        }
        if ($bitJustActive) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND system_status = ? ", 1));
        }


        return $objORM->getObjectCount(get_called_class());
    }

    /**
     * Return an on-lick link for the passed object.
     * This link is rendered by the portal search result generator, so
     * make sure the link is a valid portal page.
     * If you want to suppress the entry from the result, return an empty string instead.
     * If you want to add additional entries to the result set, clone the result and modify
     * the new instance to your needs. Pack them in an array and they'll be merged
     * into the result set afterwards.
     * Make sure to return the passed result-object in this array, too.
     *
     * @param SearchResult $objResult
     *
     * @see getLinkPortalHref()
     * @return mixed
     */
    public function updateSearchResult(SearchResult $objResult)
    {
        $objPage = new PagesPage($this->getStrAssignedPage());
        $objResult->setStrPagelink(Link::getLinkPortal($objPage->getStrName(), "", "_self", $this->getStrTitle() != "" ? $this->getStrTitle() : $objPage->getStrName(), "", "&highlight=".urlencode(html_entity_decode($objResult->getObjSearch()->getStrQuery(), ENT_QUOTES, "UTF-8"))));
        $objResult->setStrPagename($objPage->getStrName());
        $objResult->setStrDescription($this->getStrComment());
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
        return $this->getStrAssignedLanguage();
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


    /**
     * @return string
     */
    public function getStrTitle()
    {
        return $this->strTitle;
    }

    /**
     * @return string
     */
    public function getStrComment()
    {
        return $this->strComment;
    }

    /**
     * @return string
     */
    public function getStrUsername()
    {
        return $this->strUsername;
    }

    /**
     * @return int
     */
    public function getIntDate()
    {
        if ($this->intDate == null || $this->intDate == "") {
            $this->intDate = time();
        }

        return $this->intDate;
    }

    /**
     * @return string
     */
    public function getStrAssignedPage()
    {
        return $this->strAssignedPage;
    }

    /**
     * @return string
     */
    public function getStrAssignedSystemid()
    {
        return $this->strAssignedSystemid;
    }

    /**
     * @return string
     */
    public function getStrAssignedLanguage()
    {
        return $this->strAssignedLanguage;
    }

    /**
     * @param string $strTitle
     *
     * @return void
     */
    public function setStrTitle($strTitle)
    {
        $this->strTitle = $strTitle;
    }

    /**
     * @param string $strComment
     *
     * @return void
     */
    public function setStrComment($strComment)
    {
        $this->strComment = $strComment;
    }

    /**
     * @param string $strUsername
     *
     * @return void
     */
    public function setStrUsername($strUsername)
    {
        $this->strUsername = $strUsername;
    }

    /**
     * @param int $intDate
     *
     * @return void
     */
    public function setIntDate($intDate)
    {
        $this->intDate = $intDate;
    }

    /**
     * @param string $strAssignedPage
     *
     * @return void
     */
    public function setStrAssignedPage($strAssignedPage)
    {
        $this->strAssignedPage = $strAssignedPage;
    }

    /**
     * @param string $strAssignedSystemid
     *
     * @return void
     */
    public function setStrAssignedSystemid($strAssignedSystemid)
    {
        $this->strAssignedSystemid = $strAssignedSystemid;
    }

    /**
     * @param string $strAssignedLanguage
     *
     * @return void
     */
    public function setStrAssignedLanguage($strAssignedLanguage)
    {
        $this->strAssignedLanguage = $strAssignedLanguage;
    }


}
