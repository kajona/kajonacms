<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\System;

use class_common_sortmanager;
use class_logger;

/**
 * Class PageelementSortmanager
 *
 * @author sidler
 */
class PageelementSortmanager extends class_common_sortmanager
{


    /**
     * Overwritten with no functionality, this is handled by the pageelement directly.
     *
     * @param bool $arrRestrictionModules
     *
     * @see class_module_pages_pageelement::deleteObjectInternal()
     * @return mixed|void
     */
    function fixSortOnDelete($arrRestrictionModules = false)
    {
        return;
    }

    public function fixSortOnPrevIdChange($strOldPrevid, $strNewPrevid, $arrRestrictionModules = false)
    {
        //shift it to the last position by default
        //As a special feature, we set the element as the last
        $strQuery = "UPDATE "._dbprefix_."system SET system_sort = ? WHERE system_id = ?";
        $intNewSort = count($this->objSource->getSortedElementsAtPlaceholder()) + 1;
        $this->objSource->setIntSort($intNewSort);
        $this->objDB->_pQuery($strQuery, array($intNewSort, $this->objSource->getSystemid()));

        $this->objDB->flushQueryCache();

    }


    public function setAbsolutePosition($intNewPosition, $arrRestrictionModules = false)
    {
        class_logger::getInstance()->addLogRow("move ".$this->objSource->getSystemid()." to new pos ".$intNewPosition, class_logger::$levelInfo);
        $this->objDB->flushQueryCache();

        //No caching here to allow multiple shiftings per request
        $arrElements = $this->objSource->getSortedElementsAtPlaceholder();

        //more than one record to set?
        if (count($arrElements) <= 1) {
            return;
        }

        //senseless new pos?
        if ($intNewPosition <= 0 || $intNewPosition > count($arrElements)) {
            return;
        }

        $intCurPos = $this->objSource->getIntSort();

        if ($intNewPosition == $intCurPos) {
            return;
        }


        //searching the current element to get to know if element should be sorted up- or downwards
        $bitSortDown = false;
        $bitSortUp = false;
        if ($intNewPosition < $intCurPos) {
            $bitSortUp = true;
        }
        else {
            $bitSortDown = true;
        }


        //sort up?
        if ($bitSortUp) {
            //move the record to be shifted to the wanted pos
            $strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=?
								WHERE system_id=?";
            $this->objDB->_pQuery($strQuery, array(((int)$intNewPosition), $this->objSource->getSystemid()));

            //start at the pos to be reached and move all one down
            for ($intI = $intNewPosition; $intI < $intCurPos; $intI++) {

                //break for errors created on version pre 4.0
                if ($this->objSource->getSystemid() == $arrElements[$intI - 1]["system_id"]) {
                    continue;
                }

                $strQuery = "UPDATE "._dbprefix_."system
                            SET system_sort=?
                            WHERE system_id=?";
                $this->objDB->_pQuery($strQuery, array($intI + 1, $arrElements[$intI - 1]["system_id"]));
            }
        }

        if ($bitSortDown) {
            //move the record to be shifted to the wanted pos
            $strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=?
								WHERE system_id=?";
            $this->objDB->_pQuery($strQuery, array(((int)$intNewPosition), $this->objSource->getSystemid()));

            //start at the pos to be reached and move all one up
            for ($intI = $intCurPos + 1; $intI <= $intNewPosition; $intI++) {

                //break for errors created on version pre 4.0
                if ($this->objSource->getSystemid() == $arrElements[$intI - 1]["system_id"]) {
                    continue;
                }

                $strQuery = "UPDATE "._dbprefix_."system
                            SET system_sort= ?
                            WHERE system_id=?";
                $this->objDB->_pQuery($strQuery, array($intI - 1, $arrElements[$intI - 1]["system_id"]));
            }
        }

        //flush the cache
        $this->objSource->flushCompletePagesCache();
        $this->objDB->flushQueryCache();
        $this->objSource->setIntSort($intNewPosition);
    }


    public function setPosition($strMode = "up")
    {

        $arrElementsOnPlaceholder = $this->objSource->getSortedElementsAtPlaceholder();

        foreach ($arrElementsOnPlaceholder as $arrOneElement) {
            if ($arrOneElement["system_id"] == $this->objSource->getSystemid()) {
                if ($strMode == "up") {
                    $this->setAbsolutePosition($arrOneElement["system_sort"] - 1);
                }
                else {
                    $this->setAbsolutePosition($arrOneElement["system_sort"] + 1);
                }

                break;
            }
        }
    }
}
