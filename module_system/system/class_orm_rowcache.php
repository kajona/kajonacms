<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * Caches rows fetched by the database layer. You may add additional rows in order to
 * have them accessible for other classes and the orm init handler.
 * If you pass them on your own, make sure to include all relevant tables and fields,
 * the orm mapper performs no consistency checks. If there are some fields missing (e.g. due to
 * a missing table), the object will muss those values when being initialized.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_orm_rowcache extends class_orm_base {

    private static $arrInitRows = array();

    /**
     * Returns a single row from the currently cached init-rows
     *
     * @param string $strSystemid
     *
     * @return array|null
     */
    public static function getCachedInitRow($strSystemid) {
        if(isset(self::$arrInitRows[$strSystemid]))
            return self::$arrInitRows[$strSystemid];


        return null;
    }


    /**
     * Add a single row to the list of cached database-rows.
     * This avoids additional queries to init a single object afterwards.
     * On high-performance systems or large object-nets, this could reduce the amount of database-queries
     * fired drastically.
     * For best performance, include the matching row of the tables system, system_date and system_rights.
     * Use the class-filter if you want to make sure the cached row matches a single target-class. This makes sense
     * if you query tables of an inheritance-structure (and not all tables may be in the cache-row resultset).
     *
     * @param array $arrInitRow
     * @param string $strClassFilter
     *
     * @return void
     */
    public static function addSingleInitRow($arrInitRow, $strClassFilter = "") {
        if(isset($arrInitRow["system_id"]) && ($strClassFilter == "" || $strClassFilter == $arrInitRow["system_class"])) {
            self::$arrInitRows[$arrInitRow["system_id"]] = $arrInitRow;
        }
    }

    /**
     * Add an array of rows to the list of cached database-rows.
     * This avoids additional queries to init a single object afterwards.
     * On high-performance systems or large object-nets, this could reduce the amount of database-queries
     * fired drastically.
     * For best performance, include the matching row of the tables system, system_date and system_rights.
     * Use the class-filter if you want to make sure the cached row matches a single target-class. This makes sense
     * if you query tables of an inheritance-structure (and not all tables may be in the cache-row resultset).
     *
     * @param array $arrInitRows
     * @param string $strClassFilter
     *
     * @return void
     */
    public static function addArrayOfInitRows($arrInitRows, $strClassFilter = "") {
        foreach($arrInitRows as $arrOneRow)
            self::addSingleInitRow($arrOneRow, $strClassFilter);
    }

    /**
     * @return array
     */
    public static function getArrInitRows() {
        return self::$arrInitRows;
    }

    /**
     * Removes a single row from the cache
     *
     * @param string $strSystemid
     *
     * @return void
     */
    public static function removeSingleRow($strSystemid) {
        if(isset(self::$arrInitRows[$strSystemid]))
            unset(self::$arrInitRows[$strSystemid]);
    }
    /**
     * Resets the cached rows
     * @return void
     */
    public static function flushCache() {
        self::$arrInitRows = array();
    }


}
