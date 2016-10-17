<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Simple data-container for logentries.
 * Has no regular use.
 *
 * @author sidler@mulchprod.de
 */
final class ChangelogContainer
{
    private $objDate;
    private $strSystemid;
    private $strUserId;
    private $strClass;
    private $strAction;
    private $strProperty;
    private $strOldValue;
    private $strNewValue;

    /**
     * @param int $intDate
     * @param string $strSystemid
     * @param string $strUserId
     * @param string $strClass
     * @param string $strAction
     * @param string $strProperty
     * @param string $strOldValue
     * @param string $strNewValue
     */
    public function __construct($intDate, $strSystemid, $strUserId, $strClass, $strAction, $strProperty, $strOldValue, $strNewValue)
    {
        $this->objDate = new Date($intDate);
        $this->strSystemid = $strSystemid;
        $this->strUserId = $strUserId;
        $this->strClass = $strClass;
        $this->strAction = $strAction;
        $this->strProperty = $strProperty;
        $this->strOldValue = $strOldValue;
        $this->strNewValue = $strNewValue;
    }

    /**
     * @return VersionableInterface
     */
    public function getObjTarget()
    {
        if (class_exists($this->strClass)) {
            return new $this->strClass($this->strSystemid);
        } else {
            return null;
        }
    }

    /**
     * @return Date
     */
    public function getObjDate()
    {
        return $this->objDate;
    }

    /**
     * @return mixed
     */
    public function getStrSystemid()
    {
        return $this->strSystemid;
    }

    /**
     * @return mixed
     */
    public function getStrUserId()
    {
        return $this->strUserId;
    }

    /**
     * @return string
     */
    public function getStrUsername()
    {
        $strUserId = $this->getStrUserId();
        if (validateSystemid($strUserId)) {
            return Objectfactory::getInstance()->getObject($strUserId)->getStrDisplayName();
        } else {
            return "";
        }
    }

    /**
     * @return mixed
     */
    public function getStrClass()
    {
        return $this->strClass;
    }

    /**
     * @return mixed
     */
    public function getStrAction()
    {
        return $this->strAction;
    }

    /**
     * @return mixed
     */
    public function getStrOldValue()
    {
        return $this->strOldValue;
    }

    /**
     * @return mixed
     */
    public function getStrNewValue()
    {
        return $this->strNewValue;
    }

    /**
     * @return mixed
     */
    public function getStrProperty()
    {
        return $this->strProperty;
    }

}
