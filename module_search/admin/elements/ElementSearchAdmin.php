<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                *
********************************************************************************************************/

namespace Kajona\Search\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\Search\System\SearchSearch;


/**
 * Class representing the search element on the admin side
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @targetTable element_search.content_id
 *
 */
class ElementSearchAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * @var string
     * @tableColumn element_search.search_template
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_search
     */
    private $strTemplate;

    /**
     * @var int
     * @tableColumn element_search.search_amount
     * @tableColumnDatatype int
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel search_amount
     */
    private $intAmount;

    /**
     * @var string
     * @tableColumn element_search.search_page
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel commons_result_page
     */
    private $strPage;

    /**
     * @var string
     * @tableColumn element_search.search_query_id
     * @tableColumnDatatype char20
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldLabel search_search
     *
     */
    private $strQuery;


    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate) {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate() {
        return $this->strTemplate;
    }

    /**
     * @param string $strPage
     */
    public function setStrPage($strPage) {
        $this->strPage = $strPage;
    }

    /**
     * @return string
     */
    public function getStrPage() {
        return $this->strPage;
    }

    /**
     * @param int $intAmount
     */
    public function setIntAmount($intAmount) {
        $this->intAmount = $intAmount;
    }

    /**
     * @return int
     */
    public function getIntAmount() {
        return $this->intAmount;
    }

    /**
     * @param string $strQuery
     */
    public function setStrQuery($strQuery) {
        $this->strQuery = $strQuery;
    }

    /**
     * @return string
     */
    public function getStrQuery(){
        return $this->strQuery;
    }


    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrRawQueries = SearchSearch::getObjectList();

        $arrQueries = array();
        foreach ($arrRawQueries as $objOneQuery) {
            $arrQueries[$objOneQuery->getSystemid()] = $objOneQuery->getStrDisplayName();
        }
        $objForm->getField("query")->setArrKeyValues($arrQueries);

        return $objForm;
    }

}
