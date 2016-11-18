<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\System\System\Filters;

use Kajona\System\System\Carrier;
use Kajona\System\System\FilterBase;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmBase;
use Kajona\System\System\OrmCompositeCondition;
use Kajona\System\System\OrmDeletedhandlingEnum;

/**
 * Class DeletedRecordsFilter
 *
 * @author stefan.idler@artemeon.de
 * @since 6.1
 *
 * @module system
 */
class DeletedRecordsFilter extends FilterBase
{
    /**
     * @var string
     * @tableColumn system.system_id
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     */
    private $strSystemid;

    /**
     * @var string
     * @tableColumn system.system_class
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     *
     * @filterCompareOperator LIKE
     */
    private $strClass;

    /**
     * @var string
     * @tableColumn system.system_comment
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     *
     * @filterCompareOperator LIKE
     */
    private $strComment;

    /**
     * @var int
     * @tableColumn system.system_deleted
     */
    private $intDeleted = 1;

    /**
     * Fetches a list of records currently marked as deleted
     *
     * @param DeletedRecordsFilter $objFilter
     * @param null $intStart
     * @param null $intEnd
     *
     * @return \Kajona\System\System\Model[]
     */
    public static function getDeletedRecords(DeletedRecordsFilter $objFilter, $intStart = null, $intEnd = null)
    {
        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);

        $objFilter->setIntDeleted(1);
        $strQuery = "SELECT system_id FROM "._dbprefix_."system AS system WHERE ";

        $objCompound = new OrmCompositeCondition($objFilter->getOrmConditions());
        $strQuery .= $objCompound->getStrWhere();
        $arrParams = $objCompound->getArrParams();

        $strQuery .= " ORDER BY system.system_id DESC";

        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);



        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
            $arrReturn[] = Objectfactory::getInstance()->getObject($arrOneRow["system_id"]);
        }

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        return $arrReturn;
    }

    /**
     * Counts the number of records currently marked as deleted
     *
     * @param DeletedRecordsFilter $objFilter
     *
     * @return int
     */
    public static function getDeletedRecordsCount(DeletedRecordsFilter $objFilter)
    {
        $objFilter->setIntDeleted(1);
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system AS system WHERE ";

        $objCompound = new OrmCompositeCondition($objFilter->getOrmConditions());
        $strQuery .= $objCompound->getStrWhere();
        $arrParams = $objCompound->getArrParams();

        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }


    /**
     * @return string
     */
    public function getStrSystemid()
    {
        return $this->strSystemid;
    }

    /**
     * @param string $strSystemid
     */
    public function setStrSystemid($strSystemid)
    {
        $this->strSystemid = $strSystemid;
    }

    /**
     * @return string
     */
    public function getStrClass()
    {
        return $this->strClass;
    }

    /**
     * @param string $strClass
     */
    public function setStrClass($strClass)
    {
        $this->strClass = $strClass;
    }

    /**
     * @return string
     */
    public function getStrComment()
    {
        return $this->strComment;
    }

    /**
     * @param string $strComment
     */
    public function setStrComment($strComment)
    {
        $this->strComment = $strComment;
    }

    /**
     * @return int
     */
    public function getIntDeleted()
    {
        return $this->intDeleted;
    }

    /**
     * @param int $intDeleted
     */
    public function setIntDeleted($intDeleted)
    {
        $this->intDeleted = $intDeleted;
    }





}