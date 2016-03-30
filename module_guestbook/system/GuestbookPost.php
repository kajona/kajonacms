<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Guestbook\System;

use Kajona\Pages\System\PagesPage;
use Kajona\Search\System\SearchResult;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmComparatorEnum;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistOrderby;
use Kajona\System\System\OrmObjectlistSystemstatusRestriction;
use Kajona\System\System\SearchPortalobjectInterface;
use Kajona\System\System\SystemModule;


/**
 * Class to represent a guestbook post
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 * @targetTable guestbook_post.guestbook_post_id
 *
 * @module guestbook
 * @moduleId _guestbook_module_id_
 */
class GuestbookPost extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, AdminListableInterface, SearchPortalobjectInterface
{

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_name
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     *
     * @addSearchIndex
     */
    private $strGuestbookPostName = "";

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_email
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldValidator Kajona\System\System\Validators\EmailValidator
     *
     * @addSearchIndex
     */
    private $strGuestbookPostEmail = "";

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_page
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     *
     * @addSearchIndex
     */
    private $strGuestbookPostPage = "";

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_text
     * @tableColumnDatatype text
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryTextarea
     *
     * @addSearchIndex
     */
    private $strGuestbookPostText = "";

    /**
     * @var int
     * @tableColumn guestbook_post.guestbook_post_date
     * @tableColumnDatatype int
     */
    private $intGuestbookPostDate = 0;


    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_book";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return timeToString($this->getIntGuestbookPostDate(), false)." ".$this->getStrGuestbookPostEmail();
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return uniStrTrim($this->getStrGuestbookPostText(), 70);
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrGuestbookPostName();
    }


    /**
     * Disables new posts if the guestbook itself is moderated.
     *
     * @return bool
     */
    protected function onInsertToDb()
    {
        $objGuestbook = new GuestbookGuestbook($this->getPrevId());
        if ($objGuestbook->getIntGuestbookModerated() == "1") {
            $this->setIntRecordStatus(0);
        }

        return true;
    }


    /**
     * Loads all posts belonging to the given systemid (in most cases a guestbook)
     *
     * @param string $strPrevId
     * @param bool $bitJustActive
     * @param null $intStart
     * @param null $intEnd
     *
     * @return GuestbookPost[]
     * @static
     */
    public static function getPosts($strPrevId = "", $bitJustActive = false, $intStart = null, $intEnd = null)
    {
        $objORM = new OrmObjectlist();
        if ($bitJustActive) {
            $objORM->addWhereRestriction(new OrmObjectlistSystemstatusRestriction(OrmComparatorEnum::Equal(), 1));
        }
        $objORM->addOrderBy(new OrmObjectlistOrderby("guestbook_post_date DESC"));
        return $objORM->getObjectList(get_called_class(), $strPrevId, $intStart, $intEnd);
    }

    /**
     * Looks up the posts available
     *
     * @param string $strPrevID
     * @param bool $bitJustActive
     *
     * @return int
     * @static
     */
    public static function getPostsCount($strPrevID = "", $bitJustActive = false)
    {
        $objORM = new OrmObjectlist();
        if ($bitJustActive) {
            $objORM->addWhereRestriction(new OrmObjectlistSystemstatusRestriction(OrmComparatorEnum::Equal(), 1));
        }
        return $objORM->getObjectCount(get_called_class(), $strPrevID);
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
        $objORM = new OrmObjectlist();
        $strQuery = "SELECT system_id, guestbook_amount
                       FROM "._dbprefix_."element_guestbook,
                            "._dbprefix_."page_element,
                            "._dbprefix_."system
                      WHERE guestbook_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND system_status = 1
                        ".$objORM->getDeletedWhereRestriction()."
                        AND page_element_ph_language = ? ";

        $arrRows = $this->objDB->getPArray($strQuery, array($this->getPrevId(), $objResult->getObjSearch()->getStrPortalLangFilter()));
        $arrReturn = array();
        foreach ($arrRows as $arrOneElement) {

            $objCur = Objectfactory::getInstance()->getObject($arrOneElement["system_id"]);
            while($objCur != null && !$objCur instanceof PagesPage && !$objCur instanceof SystemModule) {
                $objCur = Objectfactory::getInstance()->getObject($objCur->getStrPrevId());
            }




            if ($objCur instanceof PagesPage && $objCur->getStrName() != 'master') {


                //search pv position
                $intAmount = $arrOneElement["guestbook_amount"];
                $arrPostsInGB = GuestbookPost::getPosts($this->getPrevId(), true);
                $intCounter = 0;
                foreach ($arrPostsInGB as $objOnePostInGb) {
                    $intCounter++;
                    if ($objOnePostInGb->getSystemid() == $this->getSystemid()) {
                        break;
                    }
                }
                //calculate pv
                $intPvPos = ceil($intCounter / $intAmount);

                $objCurResult = clone($objResult);
                $objCurResult->setStrPagelink(Link::getLinkPortal($objCur->getStrName(), "", "_self", $objCur->getStrBrowsername(), "", "&highlight=".urlencode(html_entity_decode($objResult->getObjSearch()->getStrQuery(), ENT_QUOTES, "UTF-8"))."&pv=".$intPvPos));
                $objCurResult->setStrPagename($objCur->getStrName());
                $objCurResult->setStrDescription($this->getStrGuestbookPostText());
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


    public function setIntGuestbookPostDate($intGuestbookPostDate)
    {
        $this->intGuestbookPostDate = $intGuestbookPostDate;
    }

    public function getIntGuestbookPostDate()
    {
        return $this->intGuestbookPostDate;
    }

    public function setStrGuestbookPostEmail($strGuestbookPostEmail)
    {
        $this->strGuestbookPostEmail = $strGuestbookPostEmail;
    }

    public function getStrGuestbookPostEmail()
    {
        return $this->strGuestbookPostEmail;
    }

    public function setStrGuestbookPostName($strGuestbookPostName)
    {
        $this->strGuestbookPostName = $strGuestbookPostName;
    }

    public function getStrGuestbookPostName()
    {
        return $this->strGuestbookPostName;
    }

    public function setStrGuestbookPostPage($strGuestbookPostPage)
    {
        //Remove protocol-prefixes
        $strGuestbookPostPage = str_replace("http://", "", $strGuestbookPostPage);
        $strGuestbookPostPage = str_replace("https://", "", $strGuestbookPostPage);
        $this->strGuestbookPostPage = $strGuestbookPostPage;
    }

    public function getStrGuestbookPostPage()
    {
        return $this->strGuestbookPostPage;
    }

    public function setStrGuestbookPostText($strGuestbookPostText)
    {
        $this->strGuestbookPostText = $strGuestbookPostText;
    }

    public function getStrGuestbookPostText()
    {
        return $this->strGuestbookPostText;
    }

}
