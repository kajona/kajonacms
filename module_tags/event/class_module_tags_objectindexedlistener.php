<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
class class_module_tags_objectindexedlistener  implements interface_genericevent_listener {


    /**
     * Called whenever an object is index and to be added to the search-index.
     * Use this callback to add additional content to the objects search-document.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {

        //unwrap arguments
        /** @var class_model $objObject */
        $objObject = $arrArguments[0];
        /** @var class_module_search_document $objSearchDocument */
        $objSearchDocument = $arrArguments[1];

        if(class_module_system_module::getModuleByName("tags") == null)
            return true;

        //load tags for the object
        $objTags = class_module_tags_tag::getTagsForSystemid($objObject->getSystemid());

        foreach($objTags as $objOneTag)
            $objSearchDocument->addContent("tag", $objOneTag->getStrName());

        return true;

    }

    /**
     * Internal init block, called on file-inclusion, e.g. by the class-loader
     *
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener("core.search.objectindexed", new class_module_tags_objectindexedlistener());
    }

}

//static init block on include
class_module_tags_objectindexedlistener::staticConstruct();
