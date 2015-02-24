<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * This interface is used to generate the click-link for objects found by the search.
 * Since the search tries to build a target link automatically, this interface is optional.
 * This means, you won't have to implement this interface for standard objects. Only if you want to
 * provide a special action for the "on click" link, this interface is relevant for you.
 *
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @since 4.3
 */
interface interface_search_resultobject {

    /**
     * Return an on-lick link for the passed object.
     * This link is used by the backend-search for the autocomplete-field
     *
     * @see getLinkAdminHref()
     * @return mixed
     */
    public function getSearchAdminLinkForObject();

}
