<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\System;


/**
 * List of events managed by the search module.
 * Please take care to not referencing this class directly! There may be scenarios where
 * this class is not available (e.g. if module search is not installed).
 *
 * @package module_search
 * @since 4.5
 */
interface SearchEventidentifier
{


    /**
     * Name of the event thrown as soon as record is indexed.
     *
     * Use this listener-identifier to add additional content to
     * a search-document.
     * The params-array contains two entries:
     *
     * @param \Kajona\System\System\Model $objInstance the record to be indexed
     * @param SearchDocument $objSearchDocument the matching search document which may be extended
     *
     * @since 4.5
     *
     */
    const EVENT_SEARCH_OBJECTINDEXED = "core.search.objectindexed";


}
