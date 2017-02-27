<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * @author christoph.kappestein@gmail.com
 *
 * @targetTable user_pwchangehistory.id
 * @module system
 * @moduleId _system_modul_id_
 */
class SystemPwchangehistory extends Model implements ModelInterface, AdminListableInterface
{
    /**
     * @var string
     * @tableColumn user_pwchangehistory.history_targetuser
     * @tableColumnDatatype char20
     * @tableColumnIndex
     */
    protected $strTargetUser;

    /**
     * @var string
     * @tableColumn user_pwchangehistory.history_link
     * @tableColumnDatatype char254
     * @tableColumnIndex
     */
    protected $strActivationLink;

    /**
     * @var string
     * @tableColumn user_pwchangehistory.history_changedate
     * @tableColumnDatatype long
     * @tableColumnIndex
     */
    protected $strChangeDate;

    /**
     * @return mixed
     */
    public function getStrTargetUser()
    {
        return $this->strTargetUser;
    }

    /**
     * @param $strTargetUser
     */
    public function setStrTargetUser($strTargetUser)
    {
        $this->strTargetUser = $strTargetUser;
    }

    /**
     * @return string
     */
    public function getStrActivationLink()
    {
        return $this->strActivationLink;
    }

    /**
     * @param string $strActivationLink
     */
    public function setStrActivationLink($strActivationLink)
    {
        $this->strActivationLink = $strActivationLink;
    }

    /**
     * @return mixed
     */
    public function getStrChangeDate()
    {
        return $this->strChangeDate;
    }

    /**
     * @param mixed $strChangeDate
     */
    public function setStrChangeDate($strChangeDate)
    {
        $this->strChangeDate = $strChangeDate;
    }

    public function getStrDisplayName()
    {
        $objUser = Objectfactory::getInstance()->getObject($this->getStrOwner());
        if($objUser !== null) {
            return $objUser->getStrDisplayName() . " (" . dateToString($this->getStrChangeDate()) . ")";
        }
        return "";
    }

    public function getStrIcon()
    {
        return "icon_user";
    }

    public function getStrAdditionalInfo()
    {
        return "";
    }

    public function getStrLongDescription()
    {
        return "";
    }

    public static function getHistoryByUser($strTargetUser)
    {
        $objORM = new OrmObjectlist();
        $objORM->addWhereRestriction(new OrmPropertyCondition("strTargetUser", OrmComparatorEnum::Equal(), $strTargetUser));
        $objORM->addOrderBy(new OrmObjectlistOrderby("history_changedate DESC"));

        return $objORM->getObjectList(get_called_class(), "", 0, 10);
    }
}
