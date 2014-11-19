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
    private $intScore = 0;
    private $strSystemid;
    private $strDescription;

    private $objObject = null;

    private $strAdminlink;
    // Link Portal Page
    private $strLinkPagename;
    private $strLinkPageI;
    private $strLinkText;
    private $strLinkAction;
    private $strLinkParams;
    private $strLinkSystemid;
    private $strLinkModul;

    /**
     * @return mixed
     */
    public function getStrLinkModul()
    {
        return $this->strLinkModul;
    }

    /**
     * @param mixed $strLinkModul
     */
    public function setStrLinkModul($strLinkModul)
    {
        $this->strLinkModul = $strLinkModul;
    }

    /** @var class_module_search_search */
    private $objSearch = null;


    /**
     * @return mixed
     */
    public function getStrLinkPageI()
    {
        return $this->strLinkPageI;
    }

    /**
     * @param mixed $strLinkPageI
     */
    public function setStrLinkPageI($strLinkPageI)
    {
        $this->strLinkPageI = $strLinkPageI;
    }

    /**
     * @return mixed
     */
    public function getStrLinkParams()
    {
        return $this->strLinkParams;
    }

    /**
     * @param mixed $strLinkParams
     */
    public function setStrLinkParams($strLinkParams)
    {
        $this->strLinkParams = $strLinkParams;
    }

    /**
     * @return mixed
     */
    public function getStrLinkSystemid()
    {
        return $this->strLinkSystemid;
    }

    /**
     * @param mixed $strLinkSystemid
     */
    public function setStrLinkSystemid($strLinkSystemid)
    {
        $this->strLinkSystemid = $strLinkSystemid;
    }

    /**
     * @return mixed
     */
    public function getStrLinkText()
    {
        if ($this->strLinkText !== "")
            return $this->strLinkText;
        return $this->strLinkPageI;
    }

    /**
     * @param mixed $strLinkText
     */
    public function setStrLinkText($strLinkText)
    {
        $this->strLinkText = $strLinkText;
    }

    /**
     * @return string
     */
    public function getStrSortHash() {
        return sha1($this->strSystemid.$this->strLinkPagename.$this->strAdminlink);
    }

    /**
     * @param int $intScore
     * @return void
     */
    public function setIntScore($intScore) {
        $this->intScore = $intScore;
    }

    /**
     * @return int
     */
    public  function getIntScore() {
        return $this->intScore;
    }

    /**
     * @param int $intHits
     * @return void
     */
    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

    /**
     * @return int
     */
    public function getIntHits() {
        return $this->intHits;
    }

    /**
     * @param string $strDescription
     * @return void
     */
    public function setStrDescription($strDescription) {
        $this->strDescription = $strDescription;
    }

    /**
     * @return mixed
     */
    public function getStrDescription() {
        return $this->strDescription;
    }

    /**
     * @param string $strLinkAction
     * @return void
     */
    public function setStrLinkAction($strLinkAction) {
        $this->strLinkAction = $strLinkAction;
    }

    /**
     * @return mixed
     */
    public function getStrLinkAction() {
        return $this->strLinkAction;
    }

    public function setStrAdminlink($strAdminlink){
        $this->strAdminlink = $strAdminlink;
    }

    /**
     * @return mixed
     */
    public function getStrPagelink($bitAdminLink = false) {
        if ($bitAdminLink)
            return $this->strAdminlink;
        return class_link::getLinkPortal($this->strLinkPageI, "", "_self", $this->strLinkText, $this->strLinkAction, $this->strLinkParams, $this->strLinkSystemid, "", "&highlight=".urlencode(html_entity_decode($this->getObjSearch()->getStrQuery(), ENT_QUOTES, "UTF-8")));
    }

    /**
     * @param string $strPagename
     * @return void
     */
    public function setStrLinkPagename($strPagename) {
        $this->strLinkPagename = $strPagename;
    }

    /**
     * @return mixed
     */
    public function getStrLinkPagename() {
        return $this->strLinkPagename;
    }

    /**
     * @param string $strResultId
     * @return void
     */
    public function setStrResultId($strResultId) {
        $this->strResultId = $strResultId;
    }

    /**
     * @return mixed
     */
    public function getStrResultId() {
        return $this->strResultId;
    }

    /**
     * @param string $strSystemid
     * @return void
     */
    public function setStrSystemid($strSystemid) {
        $this->strSystemid = $strSystemid;
    }

    /**
     * @return mixed
     */
    public function getStrSystemid() {
        return $this->strSystemid;
    }

    /**
     * @param class_model $objObject
     * @return void
     */
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
     * @return class_model|interface_model|interface_search_resultobject|interface_search_portalobject
     */
    public function getObjObject() {
        return $this->objObject;
    }

    /**
     * @param \class_module_search_search $objSearch
     * @return void
     */
    public function setObjSearch($objSearch) {
        $this->objSearch = $objSearch;
    }

    /**
     * @return \class_module_search_search
     */
    public function getObjSearch() {
        return $this->objSearch;
    }




}
