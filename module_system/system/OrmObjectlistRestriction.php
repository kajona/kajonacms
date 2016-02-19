<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A objectlist restriction may be used to create where restrictions for the objectList and objectCount queries.
 * Pass them using a syntax like "AND x = ?", don't add "WHERE", this is done by the mapper.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class OrmObjectlistRestriction
{

    private $strWhere = "";
    private $arrParams = array();

    private $strTargetClass = "";

    /**
     * @param string $strWhere
     * @param string|string[] $arrParams either a single value or an array of params
     */
    function __construct($strWhere, $arrParams = array())
    {

        if (!is_array($arrParams)) {
            $arrParams = array($arrParams);
        }

        $this->arrParams = $arrParams;
        $this->strWhere = " ".$strWhere." ";
    }

    /**
     * @param array $arrParams
     *
     * @return void
     */
    public function setArrParams($arrParams)
    {
        $this->arrParams = $arrParams;
    }

    /**
     * @return array
     */
    public function getArrParams()
    {
        return $this->arrParams;
    }

    /**
     * @param string $strWhere
     *
     * @return void
     */
    public function setStrWhere($strWhere)
    {
        $this->strWhere = $strWhere;
    }

    /**
     * @return string
     */
    public function getStrWhere()
    {
        return $this->strWhere;
    }

    /**
     * @return string
     */
    public function getStrTargetClass()
    {
        return $this->strTargetClass;
    }

    /**
     * @param string $strTargetClass
     */
    public function setStrTargetClass($strTargetClass)
    {
        $this->strTargetClass = $strTargetClass;
    }


}
