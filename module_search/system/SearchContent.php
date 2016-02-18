<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
class SearchContent {
    private $strFieldName;
    private $strContent;
    private $strDocumentId;
    private $intScore;

    function __construct() {
        $this->setStrId(generateSystemid());
        $this->setScore(1);
    }

    /**
     * @var string
     */
    private $strId;

    /**
     * @param string $strId
     */
    private function setStrId($strId) {
        $this->strId = $strId;
    }

    /**
     * @return string
     */
    public function getStrId() {
        return $this->strId;
    }


    /**
     * @return mixed
     */
    public function getDocumentId() {
        return $this->strDocumentId;
    }

    /**
     * @param mixed $strDocumentId
     */
    public function setDocumentId($strDocumentId) {
        $this->strDocumentId = $strDocumentId;
    }

    /**
     * @return mixed
     */
    public function getFieldName() {
        return $this->strFieldName;
    }

    /**
     * @param mixed $strFieldName
     */
    public function setFieldName($strFieldName) {
        $this->strFieldName = $strFieldName;
    }

    /**
     * @return mixed
     */
    public function getContent() {
        return $this->strContent;
    }

    /**
     * @param mixed $strContent
     */
    public function setContent($strContent) {
        $this->strContent = $strContent;
    }
    /**
     * @return int
     */
    public function getScore() {
        return $this->intScore;
    }

    /**
     * @param int $intScore
     */
    public function setScore($intScore) {
        $this->intScore = $intScore;
    }
}

