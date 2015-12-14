<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * @package module_system
 * @author christoph.kappestein@gmail.com
 *
 * @targetTable user_pwchangehistory.id
 * @module system
 * @moduleId _system_modul_id_
 */
class class_module_system_pwchangehistory extends class_model implements interface_model, interface_admin_listable
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
     * @param mixed $strUser
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
        $objUser = new class_module_user_user($this->getStrOwner());
        return $objUser->getStrDisplayName() . " (" . dateToString($this->getStrChangeDate()) . ")";
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
        $objORM = new class_orm_objectlist();
        $objORM->addWhereRestriction(new class_orm_objectlist_property_restriction("strTargetUser", class_orm_comparator_enum::Equal(), $strTargetUser));
        $objORM->addOrderBy(new class_orm_objectlist_orderby("history_changedate DESC"));

        return $objORM->getObjectList(get_called_class(), "", 0, 10);
    }
}
