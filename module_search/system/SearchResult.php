<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\System;

use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\SearchPortalobjectInterface;
use Kajona\System\System\SearchResultobjectInterface;


/**
 * Wrapper for a single search-result.
 * Basically only a value-holder
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class SearchResult
{

    private $strResultId;
    private $intHits = 1;
    private $intScore = 0;
    private $strSystemid;
    private $strPagelink;
    private $strPagename;
    private $strDescription;
    private $objObject = null;

    /** @var SearchSearch */
    private $objSearch = null;


    /**
     * @return string
     */
    public function getStrSortHash()
    {
        return sha1($this->strSystemid.$this->strPagename.$this->strPagelink);
    }

    /**
     * @param int $intScore
     *
     * @return void
     */
    public function setIntScore($intScore)
    {
        $this->intScore = $intScore;
    }

    /**
     * @return int
     */
    public function getIntScore()
    {
        return $this->intScore;
    }

    /**
     * @param int $intHits
     *
     * @return void
     */
    public function setIntHits($intHits)
    {
        $this->intHits = $intHits;
    }

    /**
     * @return int
     */
    public function getIntHits()
    {
        return $this->intHits;
    }

    /**
     * @param string $strDescription
     *
     * @return void
     */
    public function setStrDescription($strDescription)
    {
        $this->strDescription = $strDescription;
    }

    /**
     * @return mixed
     */
    public function getStrDescription()
    {
        return $this->strDescription;
    }

    /**
     * @param string $strPagelink
     *
     * @return void
     */
    public function setStrPagelink($strPagelink)
    {
        $this->strPagelink = $strPagelink;
    }

    /**
     * @return mixed
     */
    public function getStrPagelink()
    {
        return $this->strPagelink;
    }

    /**
     * @param string $strPagename
     *
     * @return void
     */
    public function setStrPagename($strPagename)
    {
        $this->strPagename = $strPagename;
    }

    /**
     * @return mixed
     */
    public function getStrPagename()
    {
        return $this->strPagename;
    }

    /**
     * @param string $strResultId
     *
     * @return void
     */
    public function setStrResultId($strResultId)
    {
        $this->strResultId = $strResultId;
    }

    /**
     * @return mixed
     */
    public function getStrResultId()
    {
        return $this->strResultId;
    }

    /**
     * @param string $strSystemid
     *
     * @return void
     */
    public function setStrSystemid($strSystemid)
    {
        $this->strSystemid = $strSystemid;
    }

    /**
     * @return mixed
     */
    public function getStrSystemid()
    {
        return $this->strSystemid;
    }

    /**
     * @param \Kajona\System\System\Model $objObject
     *
     * @return void
     */
    public function setObjObject($objObject)
    {
        $this->objObject = $objObject;

        if ($objObject instanceof \Kajona\System\System\Model) {
            if ($this->strSystemid == "") {
                $this->strSystemid = $objObject->getSystemid();
            }

            if ($this->strResultId == "") {
                $this->strResultId = $objObject->getSystemid();
            }
        }

    }

    /**
     * @return Model|ModelInterface|SearchResultobjectInterface|SearchPortalobjectInterface
     */
    public function getObjObject()
    {
        return $this->objObject;
    }

    /**
     * @param SearchSearch $objSearch
     *
     * @return void
     */
    public function setObjSearch($objSearch)
    {
        $this->objSearch = $objSearch;
    }

    /**
     * @return SearchSearch
     */
    public function getObjSearch()
    {
        return $this->objSearch;
    }


}
