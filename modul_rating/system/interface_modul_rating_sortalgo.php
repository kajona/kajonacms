<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                         *
********************************************************************************************************/

/**
 * Interface to be implemented by all rating-sort-algorithms designed to calculate the lists 
 *
 * @package modul_rating
 */
interface interface_modul_rating_sortalgo {

	/**
     * Sets an array of elements to be sorted.
     * Elements have to be an instance of interface_sortable_rating.
     *
     * @param array $arrElements
     */
    public function setElementsArray($arrElements);
    
    /**
     * Does the sorting and returns the sorted array of elements.
     *
     */
    public function doSorting();
		
    
}
?>