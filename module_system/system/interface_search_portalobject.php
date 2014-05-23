<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * A special interface to be implemented by all objects to be included into the
 * portal search index.
 *
 * @package module_search
 * @author sidler@mulchprod.de
 * @since 4.5
 */
interface interface_search_portalobject extends interface_search_resultobject {

    /**
     * Return an on-lick link for the passed object.
     * This link is rendered by the portal search result generator, so
     * make sure the link is a valid portal page.
     * If you want to suppress the entry from the result, return an empty string instead.
     *
     * @see getLinkPortalHref()
     * @return mixed
     */
    public function getSearchPortalLinkForObject();

    /**
     * Since the portal may be split in different languages,
     * return the content lang of the current record using the common
     * abbreviation such as "de" or "en".
     * If the content is not assigned to any language, return "" instead (e.g. a single image).
     * @return mixed
     */
    public function getContentLang();

}
