<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Filters;

use Kajona\System\System\FilterBase;
use Kajona\System\System\StringUtil;

/**
 * Filter for a user group
 *
 * @author stefan.meyer1@yahoo.de
 *
 * @module user
 * @moduleId _user_modul_id_
 */
class UserGroupFilter extends FilterBase
{
    /**
     * @var string
     * @tableColumn group_name
     * @filterCompareOperator LIKE
     */
    private $strName = null;

    /**
     * array of group system ids
     *
     * @var string
     * @tableColumn system_id
     * @filterCompareOperator NOTIN
     */
    private $arrExcludedGroups = null;

    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    /**
     * @param string $strName
     */
    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @return array
     */
    public function getArrExcludedGroups()
    {
        return StringUtil::toArray($this->arrExcludedGroups);
    }

    /**
     * @param array $arrExcludedGroups
     */
    public function setArrExcludedGroups($arrExcludedGroups)
    {
        $this->arrExcludedGroups = $arrExcludedGroups;
    }


}