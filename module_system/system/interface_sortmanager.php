<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * A implementation of the sortmanager-interface handles all operations related to sort-operations
 *
 * @package module_system
 * @since 4.0
 * @author sidler@mulchprod.de
 */
interface interface_sortmanager {

    /**
     * Creates a new instannce of the sort-manger
     * @param class_root $objSource
     */
    function __construct(class_root $objSource);


    /**
     * Fixes the sort-ids when a record is assigned to a new prev-id.
     * The old siblings have to be shifted, the records new sort-id
     * is set up by the new number of siblings.
     *
     * @param $strOldPrevid
     * @param $strNewPrevid
     *
     * @return void
     */
    function fixSortOnPrevIdChange($strOldPrevid, $strNewPrevid);


    /**
     * Fixes the sort-id of siblings when deleting a record
     * The method is called right before deleting the record itself!
     *
     * @param bool|array $arrRestrictionModules If an array of module-ids is passed, the determination of siblings will be limited to the module-records matching one of the module-ids
     *
     * @return mixed
     */
    function fixSortOnDelete($arrRestrictionModules = false);

    /**
     * Sets the Position of a SystemRecord in the currect level one position upwards or downwards
     *
     * @param string $strDirection upwards || downwards
     * @return void
     * @deprecated
     */
    function setPosition($strDirection = "upwards");


    /**
     * Sets the position of systemid using a given value.
     *
     * @param int $intNewPosition
     * @param array|bool $arrRestrictionModules If an array of module-ids is passed, the determination of siblings will be limited to the module-records matching one of the module-ids
     *
     * @return void
     */
    function setAbsolutePosition($intNewPosition, $arrRestrictionModules = false);
}