<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Takes care of adding assigned tags to the objects' index
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 * @since 4.4
 *
 */
class class_module_tags_objectindexedlistener  implements interface_objectindexed_listener {



    /**
     * Called whenever an object is index and to be added to the search-index.
     * Use this callback to add additional content to the objects search-document.
     *
     * @param class_root $objObject
     * @param class_module_search_document $objSearchDocument
     *
     * @return bool
     */
    public function handleObjectIndexedEvent($objObject, class_module_search_document $objSearchDocument) {
        if(class_module_system_module::getModuleByName("tags") == null)
            return;

        //load tags for the object
        $objTags = class_module_tags_tag::getTagsForSystemid($objObject->getSystemid());

        foreach($objTags as $objOneTag)
            $objSearchDocument->addContent("tag", $objOneTag->getStrName());

    }


}
