<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	interface_sortable_rating.php                                                                       *
* 	Interface for all objects to be sortable by the rating-module    								    *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_xml.php 2016 2008-04-27 21:40:43Z sidler $                                           *
********************************************************************************************************/

/**
 * Objects to be sortable by the rating have to implement this interface.
 * E.g. needed to create top-lists. 
 *
 * @package modul_system
 */
interface interface_sortable_rating {


	/**
	 * Returns the rating for the current object
	 * 
	 * @return float
	 */
	public function getFloatRating();
	

}
?>