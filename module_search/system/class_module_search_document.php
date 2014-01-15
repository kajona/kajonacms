<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: $                                  *
********************************************************************************************************/

/**
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 */
class class_module_search_document {


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
     * @var class_module_search_content[]
     */
    private $arrContent = array();


    /**
     * @return class_module_search_content[]
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
     * Id of the target object
     * @param string $strId
     */
    public function setStrSystemId($strId) {
        $this->strSystemId = $strId;
    }

    public function addContentObj(class_module_search_content $objContent) {
        $this->arrContent[] = $objContent;
    }

    public function addContent($strField, $strContent) {
        $objAnalyzer = new class_module_search_standard_analyzer();
        $objAnalyzer->analyze($strContent);

        foreach($objAnalyzer->getResults() as $strContent => $intScore) {

            $objSearchContent = new class_module_search_content();
            $objSearchContent->setFieldName($strField);
            $objSearchContent->setContent($strContent);
            $objSearchContent->setScore($intScore);
            $objSearchContent->setDocumentId($this->getDocumentId());

            $this->addContentObj($objSearchContent);
        }

    }

}
