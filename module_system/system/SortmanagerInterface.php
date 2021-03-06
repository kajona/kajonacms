<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * A implementation of the sortmanager-interface handles all operations related to sort-operations
 *
 * @package module_system
 * @since 4.0
 * @author sidler@mulchprod.de
 */
interface SortmanagerInterface {

    /**
     * Creates a new instannce of the sort-manger
     * @param Root $objSource
     */
    function __construct(Root $objSource);


    /**
     * Fixes the sort-ids when a record is assigned to a new prev-id.
     * The old siblings have to be shifted, the records new sort-id
     * is set up by the new number of siblings.
     *
     * @param $strOldPrevid
     * @param $strNewPrevid
     * @param bool|array $arrRestrictionModules If an array of module-ids is passed, the determination of siblings will be limited to the module-records matching one of the module-ids
     *
     * @return void
     */
    public function fixSortOnPrevIdChange($strOldPrevid, $strNewPrevid, $arrRestrictionModules = false);


    /**
     * Fixes the sort-id of siblings when deleting a record
     * The method is called right before deleting the record itself!
     *
     * @param $intOldSort
     * @param bool|array $arrRestrictionModules If an array of module-ids is passed, the determination of siblings will be limited to the module-records matching one of the module-ids
     *
     * @return mixed
     */
    public function fixSortOnDelete($intOldSort, $arrRestrictionModules = false);

    /**
     * Sets the Position of a SystemRecord in the currect level one position upwards or downwards
     *
     * @param string $strDirection upwards || downwards
     * @return void
     * @deprecated
     */
    public function setPosition($strDirection = "upwards");


    /**
     * Sets the position of systemid using a given value.
     *
     * @param int $intNewPosition
     * @param array|bool $arrRestrictionModules If an array of module-ids is passed, the determination of siblings will be limited to the module-records matching one of the module-ids
     *
     * @return void
     */
    public function setAbsolutePosition($intNewPosition, $arrRestrictionModules = false);
}