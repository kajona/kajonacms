<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Objects to be sortable by the rating have to implement this interface.
 * E.g. needed to create top-lists.
 *
 * @package module_system
 */
interface interface_sortable_rating {


	/**
	 * Returns the rating for the current object
	 *
	 * @return float
	 */
	public function getFloatRating();

	/**
	 * Returns the number of ratings the current file received
	 *
	 * @return int
	 */
	public function getIntRatingHits();


}
