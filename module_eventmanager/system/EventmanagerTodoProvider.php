<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Eventmanager\System;

use Kajona\Dashboard\System\EventEntry;
use Kajona\Dashboard\System\TodoEntry;
use Kajona\Dashboard\System\TodoProviderInterface;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\SystemModule;

/**
 * Eventmanager Open Items provider
 *
 * @module eventmanager
 */
class EventmanagerTodoProvider implements TodoProviderInterface
{
    /**
     * Returns an human readable name of this provider
     *
     * @return string
     */
    public function getName()
    {
        return Lang::getInstance()->getLang("modul_titel", "eventmanager");
    }

    /**
     * @param string $strCategory
     *
     * @return EventEntry[]
     */
    public function getCurrentTodosByCategory($strCategory, $bitLimited = true)
    {
        switch($strCategory) {
            case "eventmanager_todo_participant":
                return $this->getParticipantTodoOpen($bitLimited);
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
            "eventmanager_todo_participant" => Lang::getInstance()->getLang("todo_provider_participant", "eventmanager"),
        );
    }

    /**
     * Returns whether the currently logged in user can view these events
     *
     * @return boolean
     */
    public function rightView()
    {
        return SystemModule::getModuleByName("eventmanager")->rightView();
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

    protected function getParticipantTodoOpen($bitLimited)
    {
        $objFilter = new EventmanagerParticipantFilter();
        $objFilter->setIntStatus(0);


        $arrParticipants = EventmanagerParticipant::getObjectListFiltered($objFilter, "", 0, $bitLimited ? self::LIMITED_COUNT : null);

        $arrResult = array();

        foreach($arrParticipants as $objOneParticipant) {
            /** @var EventmanagerParticipant $objOneParticipant */
            if($objOneParticipant->rightView()) {

                $arrResult[] = $this->convertPartcipantToTodo($objOneParticipant, "eventmanager_todo_participant");
            }
        }

        return $arrResult;
    }

    protected function convertPartcipantToTodo(EventmanagerParticipant $objParticipant, $strCategory)
    {
        $objEvent = new TodoEntry();
        $objEvent->setStrIcon($objParticipant->getStrIcon());
        $objEvent->setStrCategory($strCategory);
        $objEvent->setStrDisplayName($objParticipant->getStrDisplayName());
        $objEvent->setArrModuleNavi(array(
            Link::getLinkAdmin("eventmanager", "listParticipant", "&systemid=".$objParticipant->getStrPrevId(), "", "", "icon_group")
        ));

        return $objEvent;
    }
}
