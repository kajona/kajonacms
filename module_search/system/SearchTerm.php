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
class SearchTerm {

    /**
     * @var string
     */
    private $strText;

    private $strField;

    /**
     * @param mixed $field
     */
    public function setStrField($field) {
        $this->strField = $field;
    }

    /**
     * @return mixed
     */
    public function getStrField() {
        return $this->strField;
    }

    /**
     * @param string $text
     */
    public function setStrText($text) {
        $this->strText = $text;
    }

    /**
     * @return string
     */
    public function getStrText() {
        return $this->strText;
    }

    function __construct($strText, $strField = "") {
        $this->setStrText($strText);
        $this->setStrField($strField);
    }
}
