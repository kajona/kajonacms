<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/

/**
 * Interface for the portal-classes of page-elements
 *
 * @package module_pages
 */
interface interface_portal_element {

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

}
