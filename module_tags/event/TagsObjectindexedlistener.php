<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

namespace Kajona\Tags\Event;

use class_module_search_document;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\SystemModule;
use Kajona\Tags\System\TagsTag;

/**
 * Takes care of adding assigned tags to the objects' index
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 * @since 4.4
 *
 */
class TagsObjectindexedlistener  implements GenericeventListenerInterface {


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
        /** @var \Kajona\System\System\Model $objObject */
        $objObject = $arrArguments[0];
        /** @var class_module_search_document $objSearchDocument */
        $objSearchDocument = $arrArguments[1];

        if(SystemModule::getModuleByName("tags") == null)
            return true;

        //load tags for the object
        $objTags = TagsTag::getTagsForSystemid($objObject->getSystemid());

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
        CoreEventdispatcher::getInstance()->removeAndAddListener("core.search.objectindexed", new TagsObjectindexedlistener());
    }

}

//static init block on include
TagsObjectindexedlistener::staticConstruct();
