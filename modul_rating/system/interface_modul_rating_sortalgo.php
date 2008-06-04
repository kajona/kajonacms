<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   interface_modul_rating_sortalgo.php                                                                 *
*   Interface for all rating sorting algorithms                                                         *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id: interface_modul_rating_sortalgo.php 2016 2008-04-27 21:40:43Z sidler $                         *
********************************************************************************************************/

/**
 * Interface to be implemented by all sorting-algorithms designed to sort ratings 
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
