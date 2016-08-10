<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use ArrayObject;


/**
 * The orm assignment array is used to create a lazy loading way of handling object-assignments.
 * In most cases this is transparent, so there's no real usage of this class directly.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.8
 */
class OrmAssignmentArray extends ArrayObject
{

    /**
     * Indicator on whether the assignments have been loaded or not
     *
     * @var bool
     */
    private $bitInitialized = false;

    /**
     * The referenced target object
     *
     * @var Root|null
     */
    private $objTargetObject = null;
    private $strProperty = "";

    /**
     * @var OrmDeletedhandlingEnum
     */
    private $objDeletedHandling = null;

    /**
     * Create a new lazy-loaded array for a mapped assignment-property
     *
     * @param Root $objTargetObject
     * @param int $strProperty
     */
    public function __construct(Root $objTargetObject, $strProperty, $objDeletedHandling)
    {
        $this->objTargetObject = $objTargetObject;
        $this->strProperty = $strProperty;
        $this->objDeletedHandling = $objDeletedHandling;

        parent::__construct(array());
    }

    /**
     * Triggers the internal loading of the mapped assignments.
     * Real work is only done on first access.
     */
    private function lazyLoadArray()
    {
        if ($this->bitInitialized) {
            return;
        }

        $this->bitInitialized = true;

        $objInit = new OrmObjectinit($this->objTargetObject);
        $objInit->setObjHandleLogicalDeleted($this->objDeletedHandling);
        $objCfg = OrmAssignmentConfig::getConfigForProperty($this->objTargetObject, $this->strProperty);

        foreach ($objInit->getAssignmentsFromDatabase($this->strProperty) as $strOneId) {

            $objObject = Objectfactory::getInstance()->getObject($strOneId);

            /**FIXME REMOVE IN FUTURE--------------------------------------------------------------------------**/
            if($objCfg->getArrTypeFilter() !== null && in_array(UserGroup::class, $objCfg->getArrTypeFilter())) {
                $objObject = new UserGroup($strOneId);
            }
            /**FIXME REMOVE IN FUTURE--------------------------------------------------------------------------**/

            if ($objObject !== null && ($objCfg->getArrTypeFilter() == null ||
                    count(array_filter($objCfg->getArrTypeFilter(), function ($strSingleClass) use ($objObject) {
                        return $objObject instanceof $strSingleClass;
                    })) > 0)
            ) {
                $this->append($objObject);
            }
        }
    }

    public function offsetExists($index)
    {
        $this->lazyLoadArray();
        return parent::offsetExists($index);
    }

    public function offsetGet($index)
    {
        $this->lazyLoadArray();
        return parent::offsetGet($index);
    }

    public function offsetSet($index, $newval)
    {
        $this->lazyLoadArray();
        parent::offsetSet($index, $newval);
    }

    public function offsetUnset($index)
    {
        $this->lazyLoadArray();
        parent::offsetUnset($index);
    }

    public function append($value)
    {
        $this->lazyLoadArray();
        parent::append($value);
    }

    public function getArrayCopy()
    {
        $this->lazyLoadArray();
        return parent::getArrayCopy();
    }

    public function count()
    {
        $this->lazyLoadArray();
        return parent::count();
    }

    public function asort()
    {
        $this->lazyLoadArray();
        parent::asort();
    }

    public function ksort()
    {
        $this->lazyLoadArray();
        parent::ksort();
    }

    public function uasort($cmp_function)
    {
        $this->lazyLoadArray();
        parent::uasort($cmp_function);
    }

    public function uksort($cmp_function)
    {
        $this->lazyLoadArray();
        parent::uksort($cmp_function);
    }

    public function natsort()
    {
        $this->lazyLoadArray();
        parent::natsort();
    }

    public function natcasesort()
    {
        $this->lazyLoadArray();
        parent::natcasesort();
    }

    public function getIterator()
    {
        $this->lazyLoadArray();
        return parent::getIterator();
    }

    /**
     * @return boolean
     */
    public function getBitInitialized()
    {
        return $this->bitInitialized;
    }

    /**
     * @return OrmDeletedhandlingEnum
     */
    public function getObjDeletedHandling()
    {
        return $this->objDeletedHandling;
    }


}