<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						               	*
********************************************************************************************************/

/**
 * Portal-part of the votings-element
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class class_element_votings_portal extends class_element_portal implements interface_portal_element {


    /**
     * Loads the votings-class and passes control
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";
        //Load the data
        $objvotingsModule = class_module_system_module::getModuleByName("votings");
        if($objvotingsModule != null) {
            $objVotings = $objvotingsModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objVotings->action();
        }
        return $strReturn;
    }

}
