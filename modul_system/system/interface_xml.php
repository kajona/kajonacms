<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	interface_xml.php                                                                                   *
* 	Interface for all xml-classes																		*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

/**
 * Interface for all xml-classes (modules)
 * Ensures, that all needed methods are being implemented
 *
 * @package modul_system
 */
interface interface_xml {


	/**
	 * This method is being called from the element and controls all other actions
	 * If given, the action passed in the GET-Array is being passed by param
	 */
	public function action($strAction);

}
?>
