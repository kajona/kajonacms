<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Base filter class
 *
 * @package module_system
 * @author stefan.meyer@artemeon.de
 * @author christoph.kappestein@artemeon.de
 */
abstract class class_filter_base
{
    /**
     * Returns the ID of the filter
     *
     * @return string
     */
    abstract public function getFilterId();

    /**
     * Returns the module name
     *
     * @return string
     */
    abstract public function getArrModule();

    /**
     * @return string
     */
    public function getSystemid()
    {
        return generateSystemid();
    }

    /**
     * @return array
     */
    public function getOrmRestrictions()
    {
        $objReflection = new class_reflection(get_class($this));
        $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_TABLECOLUMN);
        $arrRestriction = array();
        $arrValues = get_object_vars($this);

        foreach($arrProperties as $strAttributeName => $strAttributeValue) {
            if (isset($arrValues[$strAttributeName])) {
                $strTableColumn = $strAttributeValue;
                $strGetter = $objReflection->getGetter($strAttributeName);

                if ($strGetter !== null) {
                    $strValue = $this->$strGetter();
                    if ($strValue !== null && $strValue !== "") {
                        if (is_string($strValue)) {
                            if (validateSystemid($strValue)) {
                                $arrRestriction[] = new class_orm_objectlist_restriction(" AND " . $strTableColumn . " LIKE ? ", $strValue);
                            } else {
                                $arrRestriction[] = new class_orm_objectlist_restriction(" AND " . $strTableColumn . " LIKE ? ", "%" . $strValue . "%");
                            }
                        } elseif (is_int($strValue) || is_float($strValue)) {
                            $arrRestriction[] = new class_orm_objectlist_restriction(" AND " . $strTableColumn . " = ? ", $strValue);
                        } elseif (is_bool($strValue)) {
                            $arrRestriction[] = new class_orm_objectlist_restriction(" AND " . $strTableColumn . " = ? ", $strValue ? 1 : 0);
                        } elseif (is_array($strValue)) {
                            $arrRestriction[] = new class_orm_objectlist_in_restriction($strTableColumn, $strValue);
                        }
                    }
                }
            }
        }

        return $arrRestriction;
    }

    /**
     * @param class_orm_objectlist $objORM
     */
    public function addWhereRestrictions(class_orm_objectlist $objORM)
    {
        $objRestrictions = $this->getOrmRestrictions();
        foreach ($objRestrictions as $objRestriction) {
            $objORM->addWhereRestriction($objRestriction);
        }
    }
}
