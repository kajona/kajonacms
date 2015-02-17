<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                *
********************************************************************************************************/

/**
 * Class representing the search element on the admin side
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @targetTable element_search.content_id
 *
 */
class class_element_search_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_search.search_template
     * @tableColumnDatatype char254
     *
     * @fieldType template
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
     * @fieldType text
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
     * @fieldType dropdown
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

        $arrRawQueries = class_module_search_search::getObjectList();

        $arrQueries = array();
        foreach ($arrRawQueries as $objOneQuery) {
            $arrQueries[$objOneQuery->getSystemid()] = $objOneQuery->getStrDisplayName();
        }
        $objForm->getField("query")->setArrKeyValues($arrQueries);

        return $objForm;
    }

}
