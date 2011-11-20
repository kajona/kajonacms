<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Interface for the admin-classes of page-elements
 *
 * @package module_pages
 */
interface interface_admin_element {

	/**
	 * Called to get a form to edit oder create the given element
	 *
	 * @param mixed The content of the current element to fill the form
	 */
	public function getEditForm($arrElementData);

}
?>