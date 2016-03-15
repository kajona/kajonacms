<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Search\System;


/**
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 */
class SearchDocument {


    /**
     * Id of the target object
     * @var string
     */
    private $strSystemId;
    /**
     * @var string
     */
    private $strDocumentId;

    /**
     * @var string
     */
    private $strContentLanguage = "";

    /**
     * @var bool
     */
    private $bitPortalObject = false;

    /**
     * @var SearchContent[]
     */
    private $arrContent = array();


    /**
     * @return SearchContent[]
     */
    public function getContent() {
        return $this->arrContent;
    }

    /**
     * @param array $content
     */
    public function setContent($content) {
        $this->arrContent = $content;
    }

    /**
     * @return string
     */
    public function getDocumentId() {
        return $this->strDocumentId;
    }

    /**
     * @param string $strDocumentId
     * @return void
     */
    public function setDocumentId($strDocumentId) {
        $this->strDocumentId = $strDocumentId;
    }

    /**
     * Id of the target object
     * @return string
     */
    public function getStrSystemId() {
        return $this->strSystemId;
    }

    /**
     * @param boolean $bitPortalObject
     * @return void
     */
    public function setBitPortalObject($bitPortalObject) {
        $this->bitPortalObject = $bitPortalObject;
    }

    /**
     * @return boolean
     */
    public function getBitPortalObject() {
        return $this->bitPortalObject;
    }

    /**
     * @param string $strLanguage
     * @return void
     */
    public function setStrContentLanguage($strLanguage) {
        $this->strContentLanguage = $strLanguage;
    }

    /**
     * @return string
     */
    public function getStrContentLanguage() {
        return $this->strContentLanguage;
    }



    /**
     * Id of the target object
     * @param string $strId
     * @return void
     */
    public function setStrSystemId($strId) {
        $this->strSystemId = $strId;
    }

    /**
     * @param SearchContent $objContent
     * @return void
     */
    public function addContentObj(SearchContent $objContent) {
        $this->arrContent[] = $objContent;
    }

    /**
     * @param string $strField
     * @param string $strContent
     */
    public function addContent($strField, $strContent) {
        $objAnalyzer = new SearchStandardAnalyzer();
        $objAnalyzer->analyze($strContent);

        foreach($objAnalyzer->getResults() as $strContent => $intScore) {

            $objSearchContent = new SearchContent();
            $objSearchContent->setFieldName($strField);
            $objSearchContent->setContent($strContent);
            $objSearchContent->setScore($intScore);
            $objSearchContent->setDocumentId($this->getDocumentId());

            $this->addContentObj($objSearchContent);
        }

    }

}
