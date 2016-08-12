<?php
/*"******************************************************************************************************
*   (c) 2010-2016 ARTEMEON                                                                              *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Exception;
use Kajona\System\System\Model;

/**
 * Class StatustransitionStatus
 *
 * @author christoph.kappestein@artemeon.de
 * @author stefan.meyer@artemeon.de
 * @module statustransition
 */
class StatustransitionStatus
{
    /**
     * @var int
     */
    protected $intStatus;

    /**
     * @var string
     */
    protected $strTitle;

    /**
     * @var string
     */
    protected $strIcon;

    /**
     * @var StatustransitionTransition[]
     */
    protected $arrTransitions;

    /**
     * WorkflowStatus constructor.
     *
     * @param $intStatus
     * @param $strTitle - Title of the status
     * @param $strIcon - Icon of the status
     * @param array $arrTransitions
     */
    public function __construct($intStatus, $strTitle, $strIcon, array $arrTransitions = array())
    {
        $this->intStatus = $intStatus;
        $this->strTitle = $strTitle;
        $this->strIcon = $strIcon;
        $this->arrTransitions = $arrTransitions;
    }

    /**
     * @return int
     */
    public function getIntStatus()
    {
        return $this->intStatus;
    }

    /**
     * @return string
     */
    public function getStrTitle()
    {
        return $this->strTitle;
    }

    /**
     * @return string
     */
    public function getStrIcon()
    {
        return $this->strIcon;
    }

    /**
     * Gets the transitions for the given status which are allowed for the current user.
     *
     * @return array
     */
    public function getArrTransitions(Model $objObject)
    {
        $arrTransitions = array_filter($this->arrTransitions,
            function (StatustransitionTransition $objTransition) use ($objObject) {
                return $objTransition->bitCheckTransitionRight($objObject);
            });

        return $arrTransitions;
    }

    /**
     * Adds a new transition to the status.
     *
     * @param StatustransitionTransition $objTransition
     * @return $this
     */
    public function addTransition(StatustransitionTransition $objTransition)
    {
        if (array_key_exists($objTransition->getStrTransitionKey(), $this->arrTransitions)) {
            throw new Exception("Key already exists: ".$objTransition->getStrTransitionKey(), Exception::$level_ERROR);
        }

        $this->arrTransitions[$objTransition->getStrTransitionKey()] = $objTransition;
        return $this;
    }

    /**
     * Gets the transition by the given key
     *
     * @param string $strTransitionKey
     * @return StatustransitionTransition|null
     */
    public function getTransitionByKey($strTransitionKey, Model $objObject)
    {
        $arrTransitions = $this->getArrTransitions($objObject);

        if (isset($arrTransitions[$strTransitionKey])) {
            return $arrTransitions[$strTransitionKey];
        }
        return null;
    }
}
