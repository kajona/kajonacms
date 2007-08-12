<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	interface_portal_element.php																		*
* 	Interface for all portal-classes of elements														*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/

/**
 * Interface for the portal-classes of page-elements
 *
 * @package modul_pages
 */
interface interface_portal_element {

	/**
	 * Contructor, passing the element-data to the base-classes
	 *
	 * @param mixed $objElementData Infos for the current element, e.g. the systemid
	 */
	public function __construct($objElementData);


	/**
	 * Sucessor of getElementOutput()
	 * loadData() is responsible to create the html-output of the current object.
	 * loadData() is being invoked from external.
	 * All data belonging to this element and the content is accessible by using
	 * $this->arrElementData[]
	 *
	 * @return string
	 */
	public function loadData();

	/**
	 * Method to pass control to the element.
	 * Returns the output of the element
	 * @deprecated method moved to class_elemente_portal
	 */
	//public function getElementOutput();
}
?>