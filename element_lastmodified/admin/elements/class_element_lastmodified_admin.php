<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_lastmodified.php 3530 2011-01-06 12:30:26Z sidler $                              *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the lastmodified-element
 *
 * @package element_lastmodified
 * @author sidler@mulchprod.de
 */
class class_element_lastmodified_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_lastmodified");
		parent::__construct();
	}

   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		return "";
	}


}
