<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Additional base class for packages with only an element-installer.
 * Implements the remove-handlers automatically and therefore adds remove-support to elements.
 *
 * @abstract
 * @package module_system
 * @since 4.5
 */
abstract class class_elementinstaller_base extends class_installer_base implements interface_installer_removable {

    /**
     * Validates whether the current module/element is removable or not.
     * This is the place to trigger special validations and consistency checks going
     * beyond the common metadata-dependencies.
     *
     * @return bool
     */
    public function isRemovable() {
        return true;
    }

    /**
     * Removes the elements / modules handled by the current installer.
     * Use the reference param to add a human readable logging.
     *
     * @param string &$strReturn
     *
     * @return bool
     */
    public function remove(&$strReturn) {
        //delete the page-element
        $objElement = class_module_pages_element::getElement($this->objMetadata->getStrTitle());
        if($objElement != null) {
            $strReturn .= "Deleting page-element '".$this->objMetadata->getStrTitle()."'...\n";
            $objElement->deleteObject();
        }
        else {
            $strReturn .= "Error finding page-element '".$this->objMetadata->getStrTitle()."', aborting.\n";
            return false;
        }

        return true;
    }

}

