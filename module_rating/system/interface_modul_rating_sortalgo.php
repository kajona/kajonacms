<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: interface_modul_rating_sortalgo.php 3874 2011-05-25 08:47:27Z sidler $                         *
********************************************************************************************************/

/**
 * Interface to be implemented by all rating-sort-algorithms designed to calculate the lists 
 *
 * @package module_rating
 */
interface interface_module_rating_sortalgo {

	/**
     * Sets an array of elements to be sorted.
     * Elements have to be an instance of interface_sortable_rating.
     *
     * @param array $arrElements
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
