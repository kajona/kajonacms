<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Guestbook\System;

use Kajona\Dashboard\System\EventEntry;
use Kajona\Dashboard\System\TodoEntry;
use Kajona\Dashboard\System\TodoProviderInterface;
use Kajona\System\System\Date;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\SystemModule;

/**
 * Guestbook Open Items provider
 *
 * @module guestbook
 */
class GuestbookTodoProvider implements TodoProviderInterface
{
    /**
     * Returns an human readable name of this provider
     *
     * @return string
     */
    public function getName()
    {
        return Lang::getInstance()->getLang("modul_titel", "guestbook");
    }

    /**
     * @param string $strCategory
     *
     * @return EventEntry[]
     */
    public function getCurrentTodosByCategory($strCategory, $bitLimited = true)
    {
        switch($strCategory) {
            case "guestbook_todo_open":
                return $this->getPostTodoOpen($bitLimited);
                break;

            default:
                return array();
        }
    }

    /**
     * Returns an array of all available categories
     *
     * @return array
     */
    public function getCategories()
    {
        return array(
            "guestbook_todo_open" => Lang::getInstance()->getLang("todo_provider_open", "guestbook"),
        );
    }

    /**
     * Returns whether the currently logged in user can view these events
     *
     * @return boolean
     */
    public function rightView()
    {
        return SystemModule::getModuleByName("guestbook")->rightView();
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public static function getExtensionName()
    {
        return self::EXTENSION_POINT;
    }

    private function getPostTodoOpen($bitLimited)
    {
        $objFilter = new GuestbookPostFilter();
        $objFilter->setIntStatus(0);


        $arrPosts = GuestbookPost::getObjectListFiltered($objFilter, "", 0, $bitLimited ? self::LIMITED_COUNT : null);

        $arrResult = array();

        foreach($arrPosts as $objOnePost) {
            /** @var GuestbookPost $objOnePost */
            if($objOnePost->rightView()) {

                $arrResult[] = $this->convertPostToTodo($objOnePost, "guestbook_todo_open");
            }
        }

        return $arrResult;
    }

    private function convertPostToTodo(GuestbookPost $objPost, $strCategory)
    {
        $objEvent = new TodoEntry();
        $objEvent->setStrIcon($objPost->getStrIcon());
        $objEvent->setStrCategory($strCategory);
        $objEvent->setStrDisplayName($objPost->getStrDisplayName());
        $objEvent->setArrModuleNavi(array(
            Link::getLinkAdmin("guestbook", "listPost", "&systemid=".$objPost->getStrPrevId(), "", "", "icon_bookLens")
        ));

        return $objEvent;
    }
}
