<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * The orm assignment array is used to create a lazy loading way of handling object-assignments.
 * In most cases this is transparent, so there's no real usage of this class directly.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.8
 */
class class_orm_assignment_array extends ArrayObject {
    private $bitInitialized = false;

    /**
     * The referenced target object
     * @var class_root|null
     */
    private $objTargetObject = null;
    private $strProperty = "";

    /**
     * Create a new lazy-loaded array for a mapped assignment-property
     * @param class_root $objTargetObject
     * @param int $strProperty
     */
    function __construct(class_root $objTargetObject, $strProperty) {
        $this->objTargetObject = $objTargetObject;
        $this->strProperty = $strProperty;

        parent::__construct(array());
    }


    private function lazyLoadArray() {
        if($this->bitInitialized)
            return;

        $this->bitInitialized = true;

        $objInit = new class_orm_objectinit($this->objTargetObject);
        foreach($objInit->getAssignmentsFromDatabase($this->strProperty) as $strOneId) {
            $this->append(class_objectfactory::getInstance()->getObject($strOneId));
        }
    }

    public function offsetExists($index) {
        $this->lazyLoadArray();
        return parent::offsetExists($index);
    }

    public function offsetGet($index) {
        $this->lazyLoadArray();
        return parent::offsetGet($index);
    }

    public function offsetSet($index, $newval) {
        $this->lazyLoadArray();
        parent::offsetSet($index, $newval);
    }

    public function offsetUnset($index) {
        $this->lazyLoadArray();
        parent::offsetUnset($index);
    }

    public function append($value) {
        $this->lazyLoadArray();
        parent::append($value);
    }

    public function getArrayCopy() {
        $this->lazyLoadArray();
        return parent::getArrayCopy();
    }

    public function count() {
        $this->lazyLoadArray();
        return parent::count();
    }

    public function asort() {
        $this->lazyLoadArray();
        parent::asort();
    }

    public function ksort() {
        $this->lazyLoadArray();
        parent::ksort();
    }

    public function uasort($cmp_function) {
        $this->lazyLoadArray();
        parent::uasort($cmp_function);
    }

    public function uksort($cmp_function) {
        $this->lazyLoadArray();
        parent::uksort($cmp_function);
    }

    public function natsort() {
        $this->lazyLoadArray();
        parent::natsort();
    }

    public function natcasesort() {
        $this->lazyLoadArray();
        parent::natcasesort();
    }

    public function getIterator() {
        $this->lazyLoadArray();
        return parent::getIterator();
    }

    /**
     * @return boolean
     */
    public function getBitInitialized() {
        return $this->bitInitialized;
    }


}