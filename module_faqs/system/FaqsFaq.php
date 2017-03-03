<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

namespace Kajona\Faqs\System;

use Kajona\Pages\System\PagesPage;
use Kajona\Search\System\SearchResult;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\FilterBase;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmRowcache;
use Kajona\System\System\SearchPortalobjectInterface;
use Kajona\System\System\SortableRatingInterface;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemModule;
use Kajona\System\System\VersionableInterface;


/**
 * Model for a faq itself
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 * @targetTable faqs.faqs_id
 *
 * @module faqs
 * @moduleId _faqs_module_id_
 *
 * @formGenerator Kajona\Faqs\Admin\FaqsFormgenerator
 */
class FaqsFaq extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, SortableRatingInterface, AdminListableInterface, VersionableInterface, SearchPortalobjectInterface
{

    /**
     * @var string
     * @tableColumn faqs.faqs_question
     * @tableColumnDatatype text
     * @versionable
     * @addSearchIndex
     * @listOrder
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     * @templateExport
     */
    private $strQuestion = "";

    /**
     * @var string
     * @tableColumn faqs.faqs_answer
     * @tableColumnDatatype text
     * @blockEscaping
     * @versionable
     * @addSearchIndex
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryWysiwyg
     * @wysiwygConfig minimalimage
     * @fieldMandatory
     * @templateExport
     */
    private $strAnswer = "";

    /**
     * @var FaqsCategory[]
     * @objectList faqs_member (source="faqsmem_faq", target="faqsmem_category")
     * @fieldType Kajona\System\Admin\Formentries\FormentryCheckboxarrayObjectList
     * @versionable
     */
    private $arrCats = array();

    /**
     * Returns a human readable name of the action stored with the changeset.
     *
     * @param string $strAction the technical actionname
     *
     * @return string the human readable name
     */
    public function getVersionActionName($strAction)
    {
        return $strAction;
    }

    /**
     * Returns a human readable name of the record / object stored with the changeset.
     *
     * @return string the human readable name
     */
    public function getVersionRecordName()
    {
        return "faq";
    }

    /**
     * Returns a human readable name of the property-name stored with the changeset.
     *
     * @param string $strProperty the technical property-name
     *
     * @return string the human readable name
     */
    public function getVersionPropertyName($strProperty)
    {
        return $strProperty;
    }

    /**
     * Renders a stored value. Allows the class to modify the value to display, e.g. to
     * replace a timestamp by a readable string.
     *
     * @param string $strProperty
     * @param string $strValue
     *
     * @return string
     */
    public function renderVersionValue($strProperty, $strValue)
    {
        return $strValue;
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
        return "icon_question";
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
        return StringUtil::substring($this->getStrQuestion(), 0, 200);
    }


    /**
     * Loads all faqs from the database
     * if passed, the filter is used to load the faqs of the given category
     *
     * @param FilterBase $objFilter
     * @param string $strFilter
     * @param null $intStart
     * @param null $intEnd
     *
     * @return mixed
     * @static
     */
    public static function getObjectListFiltered(FilterBase $objFilter = null, $strFilter = "", $intStart = null, $intEnd = null)
    {
        if ($strFilter != "") {

            $objORM = new OrmObjectlist();

            $strQuery = "SELECT *
							FROM " . _dbprefix_ . "faqs,
							     " . _dbprefix_ . "faqs_member,
							     " . _dbprefix_ . "system
					   LEFT JOIN " . _dbprefix_ . "system_date
                               ON system_id = system_date_id
							WHERE system_id = faqs_id
							  AND faqs_id = faqsmem_faq
							  AND faqsmem_category = ?
							  " . $objORM->getDeletedWhereRestriction() . "
							ORDER BY faqs_question ASC";

            $arrIds = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strFilter), $intStart, $intEnd);

            $arrReturn = array();
            foreach ($arrIds as $arrOneId) {
                OrmRowcache::addSingleInitRow($arrOneId);
                $arrReturn[] = Objectfactory::getInstance()->getObject($arrOneId["system_id"]);
            }

            return $arrReturn;
        } else {
            return parent::getObjectListFiltered($objFilter, "", $intStart, $intEnd);
        }

    }

    /**
     * Loads all faqs from the database
     * if passed, the filter is used to load the faqs of the given category
     *
     * @param FilterBase $objFilter
     * @param string $strFilter
     *
     * @return mixed
     * @static
     */
    public static function getObjectCountFiltered(FilterBase $objFilter = null, $strFilter = "")
    {
        if ($strFilter != "") {
            $objORM = new OrmObjectlist();

            $strQuery = "SELECT COUNT(*)
							FROM " . _dbprefix_ . "faqs,
							     " . _dbprefix_ . "system,
							     " . _dbprefix_ . "faqs_member
							WHERE system_id = faqs_id
							  AND faqs_id = faqsmem_faq
							  " . $objORM->getDeletedWhereRestriction() . "
							  AND faqsmem_category = ?";
            $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strFilter));
            return $arrRow["COUNT(*)"];
        } else {
            return parent::getObjectCountFiltered($objFilter);
        }

    }


    /**
     * Loads all faqs from the db assigned to the passed cat
     *
     * @param string $strCat
     *
     * @return FaqsFaq[]
     * @static
     */
    public static function loadListFaqsPortal($strCat)
    {
        $arrParams = array();
        $objORM = new OrmObjectlist();
        if ($strCat == 1) {
            $strQuery = "SELECT *
    						FROM " . _dbprefix_ . "faqs,
    		                     " . _dbprefix_ . "system
    		             LEFT JOIN " . _dbprefix_ . "system_date
                               ON system_id = system_date_id
    		                WHERE system_id = faqs_id
    		                  AND system_status = 1
    		                  " . $objORM->getDeletedWhereRestriction() . "
    						ORDER BY faqs_question ASC";
        } else {
            $strQuery = "SELECT *
    						FROM " . _dbprefix_ . "faqs,
    						     " . _dbprefix_ . "faqs_member,
    		                     " . _dbprefix_ . "system
    		           LEFT JOIN " . _dbprefix_ . "system_date
                               ON system_id = system_date_id
    		                WHERE system_id = faqs_id
    		                  AND faqs_id = faqsmem_faq
    		                  AND faqsmem_category = ?
    		                  AND system_status = 1
    		                  " . $objORM->getDeletedWhereRestriction() . "
    						ORDER BY faqs_question ASC";
            $arrParams[] = $strCat;
        }
        $arrIds = Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        foreach ($arrIds as $arrOneId) {
            OrmRowcache::addSingleInitRow($arrOneId);
            $arrReturn[] = Objectfactory::getInstance()->getObject($arrOneId["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Return an on-lick link for the passed object.
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
        $objORM = new OrmObjectlist();
        //search for matching pages
        $strQuery = "SELECT system_id
                       FROM " . _dbprefix_ . "element_faqs,
                            " . _dbprefix_ . "faqs
                  LEFT JOIN " . _dbprefix_ . "faqs_member
                         ON (faqsmem_faq = faqs_id),
                            " . _dbprefix_ . "page_element,
                            " . _dbprefix_ . "system
                      WHERE faqs_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND (
                            faqs_category IS NULL OR (
                                faqs_category = '0' OR faqs_category = faqsmem_category
                            )
                        )
                        AND system_status = 1
                        " . $objORM->getDeletedWhereRestriction() . "
                        AND page_element_ph_language = ? ";

        $arrRows = $this->objDB->getPArray($strQuery, array($this->getSystemid(), $objResult->getObjSearch()->getStrPortalLangFilter()));

        $arrReturn = array();

        foreach ($arrRows as $arrOneElement) {

            $objCur = Objectfactory::getInstance()->getObject($arrOneElement["system_id"]);
            while($objCur != null && !$objCur instanceof PagesPage && !$objCur instanceof SystemModule) {
                $objCur = Objectfactory::getInstance()->getObject($objCur->getStrPrevId());
            }

            if ($objCur instanceof PagesPage && $objCur->getStrName() != 'master') {
                $objCurResult = clone($objResult);
                $objCurResult->setStrPagelink(Link::getLinkPortal($objCur->getStrName(), "", "_self", $objCur->getStrBrowsername(), "", "&highlight=".urlencode(html_entity_decode($objResult->getObjSearch()->getStrQuery(), ENT_QUOTES, "UTF-8"))));
                $objCurResult->setStrPagename($objCur->getStrName());
                $objCurResult->setStrDescription($this->getStrQuestion());
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
        //the default, plz
        return "";
    }


    public function getStrQuestion()
    {
        return $this->strQuestion;
    }

    public function getStrAnswer()
    {
        return $this->strAnswer;
    }

    public function getArrCats()
    {
        return $this->arrCats;
    }

    public function setStrAnswer($strAnswer)
    {
        $this->strAnswer = $strAnswer;
    }

    public function setStrQuestion($strQuestion)
    {
        $this->strQuestion = $strQuestion;
    }

    public function setArrCats($arrCats)
    {
        $this->arrCats = $arrCats;
    }

}
