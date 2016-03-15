<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

namespace Kajona\News\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\SearchResultobjectInterface;

/**
 * Model for a newscategory
 *
 * @package module_news
 * @author sidler@mulchprod.de
 * @targetTable news_category.news_cat_id
 *
 * @module news
 * @moduleId _news_module_id_
 */
class NewsCategory extends Model implements ModelInterface, AdminListableInterface, SearchResultobjectInterface {

    /**
     * @var string
     * @tableColumn news_category.news_cat_title
     * @tableColumnDatatype char254
     * @listOrder
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     *
     * @addSearchIndex
     * @templateExport
     */
    private $strTitle = "";

    /**
     * Return an on-lick link for the passed object.
     * This link is used by the backend-search for the autocomplete-field
     *
     * @see getLinkAdminHref()
     * @return mixed
     */
    public function getSearchAdminLinkForObject() {
        return Link::getLinkAdminHref("news", "listNewsAndCategories", "&filterid=".$this->getSystemid());
    }


    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_dot";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrTitle();
    }

    /**
     * Loads all categories, the given news is in
     *
     * @param string $strSystemid
     *
     * @return NewsCategory[]
     * @static
     */
    public static function getNewsMember($strSystemid) {
        $strQuery = "SELECT newsmem_category as system_id
                       FROM " . _dbprefix_."news_member
	                   WHERE newsmem_news = ? ";
        $arrIds = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid));
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new NewsCategory($arrOneId["system_id"]);
        }

        return $arrReturn;
    }


    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

}
