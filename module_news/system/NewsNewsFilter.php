<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


namespace Kajona\News\System;

use Kajona\System\System\Date;
use Kajona\System\System\FilterBase;
use Kajona\System\System\StringUtil;

class NewsNewsFilter extends FilterBase
{
    /**
     * @var string
     * @tableColumn news.news_title
     * @fieldType text
     */
    private $strTitle;

    /**
     * @var Date
     * @tableColumn system_date.system_date_start
     * @fieldType date
     * @filterCompareOperator GE
     */
    private $objDateStartFrom;

    /**
     * @var Date
     * @tableColumn system_date.system_date_start
     * @fieldType date
     * @filterCompareOperator LE
     */
    private $objDateStartTo;


    /**
     * @var Date
     * @tableColumn system_date.system_date_end
     * @fieldType date
     * @filterCompareOperator GE
     */
    private $objDateEndFrom;

    /**
     * @var Date
     * @tableColumn system_date.system_date_end
     * @fieldType date
     * @filterCompareOperator LE
     */
    private $objDateEndTo;


    /**
     * @var Date
     * @tableColumn system_date.system_date_special
     * @fieldType date
     * @filterCompareOperator GE
     */
    private $objDateSpecialFrom;

    /**
     * @var Date
     * @tableColumn system_date.system_date_special
     * @fieldType date
     * @filterCompareOperator LE
     */
    private $objDateSpecialTo;

    
    public function getArrModule($strKey = "")
    {
        return "news";
    }

    /**
     * @return mixed
     */
    public function getStrTitle()
    {
        return $this->strTitle;
    }

    /**
     * @param mixed $strTitle
     */
    public function setStrTitle($strTitle)
    {
        $this->strTitle = $strTitle;
    }

    /**
     * @return Date
     */
    public function getObjDateStartFrom()
    {
        return StringUtil::toDate($this->objDateStartFrom);
    }

    /**
     * @param Date $objDateStartFrom
     */
    public function setObjDateStartFrom($objDateStartFrom)
    {
        $this->objDateStartFrom = $objDateStartFrom;
    }

    /**
     * @return Date
     */
    public function getObjDateStartTo()
    {
        return StringUtil::toDate($this->objDateStartTo);
    }

    /**
     * @param Date $objDateStartTo
     */
    public function setObjDateStartTo($objDateStartTo)
    {
        $this->objDateStartTo = $objDateStartTo;
    }

    /**
     * @return Date
     */
    public function getObjDateEndFrom()
    {
        return StringUtil::toDate($this->objDateEndFrom);
    }

    /**
     * @param Date $objDateEndFrom
     */
    public function setObjDateEndFrom($objDateEndFrom)
    {
        $this->objDateEndFrom = $objDateEndFrom;
    }

    /**
     * @return Date
     */
    public function getObjDateEndTo()
    {
        return StringUtil::toDate($this->objDateEndTo);
    }

    /**
     * @param Date $objDateEndTo
     */
    public function setObjDateEndTo($objDateEndTo)
    {
        $this->objDateEndTo = $objDateEndTo;
    }

    /**
     * @return Date
     */
    public function getObjDateSpecialFrom()
    {
        return StringUtil::toDate($this->objDateSpecialFrom);
    }

    /**
     * @param Date $objDateSpecialFrom
     */
    public function setObjDateSpecialFrom($objDateSpecialFrom)
    {
        $this->objDateSpecialFrom = $objDateSpecialFrom;
    }

    /**
     * @return Date
     */
    public function getObjDateSpecialTo()
    {
        return StringUtil::toDate($this->objDateSpecialTo);
    }

    /**
     * @param Date $objDateSpecialTo
     */
    public function setObjDateSpecialTo($objDateSpecialTo)
    {
        $this->objDateSpecialTo = $objDateSpecialTo;
    }
}