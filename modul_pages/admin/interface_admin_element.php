<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	interface_admin_element.php																			*
* 	Interface for all admin-classes of elements															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Interface for the admin-classes of page-elements
 *
 * @package modul_pages
 */
interface interface_admin_element {

	/**
	 * Called to get a form to edit oder create the given element
	 *
	 * @param mixed The content of the current element to fill the form
	 */
	public function getEditForm($arrElementData);

	/**
	 * This Method should handle all the savings to the database
	 *
	 * @param string $strSystemid The systemid of the current element
	 * @deprecated NOT NEEDED ANYMORE, SYSTEM TAKES CARE OF SAVING ELEMENTS!!!!!!!
	 */
	//public function actionSave($strSystemid);

}
?>