<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Wrapper for a single search-result.
 * Basically only a value-holder
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_search_result {

    private $strResultId;
    private $intHits = 1;
    private $strSystemid;
    private $strPagelink;
    private $strPagename;
    private $strDescription;
    private $objObject = null;


    public function getStrSortHash() {
        return sha1($this->strSystemid.$this->strPagename.$this->strPagelink);
    }


    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

    public function getIntHits() {
        return $this->intHits;
    }

    public function setStrDescription($strDescription) {
        $this->strDescription = $strDescription;
    }

    public function getStrDescription() {
        return $this->strDescription;
    }

    public function setStrPagelink($strPagelink) {
        $this->strPagelink = $strPagelink;
    }

    public function getStrPagelink() {
        return $this->strPagelink;
    }

    public function setStrPagename($strPagename) {
        $this->strPagename = $strPagename;
    }

    public function getStrPagename() {
        return $this->strPagename;
    }

    public function setStrResultId($strResultId) {
        $this->strResultId = $strResultId;
    }

    public function getStrResultId() {
        return $this->strResultId;
    }

    public function setStrSystemid($strSystemid) {
        $this->strSystemid = $strSystemid;
    }

    public function getStrSystemid() {
        return $this->strSystemid;
    }

    public function setObjObject($objObject) {
        $this->objObject = $objObject;

        if($objObject instanceof class_model) {
            if($this->strSystemid == "")
                $this->strSystemid = $objObject->getSystemid();

            if($this->strResultId == "")
                $this->strResultId = $objObject->getSystemid();
        }

    }

    /**
     * @return class_model|interface_model
     */
    public function getObjObject() {
        return $this->objObject;
    }

}
