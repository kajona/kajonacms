<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/


/**
 * If an object is added/updated to the search index, an event is thrown.
 * This allows listeners to add special content / keywords to the index-document.
 *
 * @author sidler@mulchprod.de
 * @package module_search
 * @since 4.4
 */
interface interface_objectindexed_listener {

    /**
     * Called whenever an object is indexed and to be added to the search-index.
     * Use this callback to add additional content to the objects search-document.
     *
     * @param class_root $objObject
     * @param class_module_search_document $objSearchDocument
     *
     * @abstract
     * @return bool
     */
    public function handleObjectIndexedEvent($objObject, class_module_search_document $objSearchDocument);

}
