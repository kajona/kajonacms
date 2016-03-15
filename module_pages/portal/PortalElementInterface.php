<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Pages\Portal;

/**
 * Interface for the portal-classes of page-elements
 *
 */
interface PortalElementInterface
{

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
