<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

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
class class_module_news_category extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, interface_admin_listable, interface_search_resultobject {

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
        return class_link::getLinkAdminHref("news", "listNewsAndCategories", "&filterid=".$this->getSystemid());
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
     * @return class_module_news_category[]
     * @static
     */
    public static function getNewsMember($strSystemid) {
        $strQuery = "SELECT newsmem_category as system_id
                       FROM " . _dbprefix_."news_member
	                   WHERE newsmem_news = ? ";
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid));
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_news_category($arrOneId["system_id"]);
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
