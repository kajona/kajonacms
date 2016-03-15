<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                         *
********************************************************************************************************/

namespace Kajona\Rating\System;


/**
 * Interface to be implemented by all rating-sort-algorithms designed to calculate the lists
 *
 * @package module_rating
 */
interface ModuleRatingSortalgoInterface
{

    /**
     * Sets an array of elements to be sorted.
     * Elements have to be an instance of SortableRatingInterface.
     *
     * @param array $arrElements
     *
     * @return void
     */
    public function setElementsArray($arrElements);

    /**
     * Does the sorting and returns the sorted array of elements.
     *
     * @return array
     */
    public function doSorting();


}
