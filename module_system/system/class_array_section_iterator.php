<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Class to iterator over an array.
 * This class is able to create a pageview-mechanism
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_array_section_iterator extends class_array_iterator {

    private $intTotalElements;
    private $intPageNumber = 1;
    private $arrSection;

    /**
     * Constructor
     *
     * @param int $intNrOfElements The total number of elements
     */
    public function __construct($intNrOfElements) {
        $this->intTotalElements = $intNrOfElements;

        parent::__construct(array());
    }


    /**
     * Returns the number of elements
     *
     * @return int
     * @overwrite
     */
    public function getNumberOfElements() {
        return $this->intTotalElements;
    }

    /**
     * Sets the current page-number
     *
     * @param int $intPageNumber
     */
    public function setPageNumber($intPageNumber) {
        if((int)$intPageNumber > 0) {
            $this->intPageNumber = $intPageNumber;
        }
    }

    public function getPageNumber() {
        return $this->intPageNumber;
    }

    /**
     * calculates the start-pos of the current array-section
     *
     * @return int
     */
    public function calculateStartPos() {
        return (($this->intPageNumber * $this->intElementsPerPage) - $this->intElementsPerPage);
    }

    /**
     * Calculates the end-pos of the current array-section
     *
     * @return int
     */
    public function calculateEndPos() {
        return ($this->intElementsPerPage + ($this->calculateStartPos() - 1));
    }

    /**
     * Set the section of the array containing the data
     *
     * @param array $arrSectionContent
     */
    public function setArraySection($arrSectionContent) {
        $this->arrSection = $arrSectionContent;
    }

    /**
     * Creates an array containing only the needed key / value pairs. The other ones are empty.
     *
     * @param bool $bitCompressed if set to true, the array will be reduced to elements containing a value.
     *             otherwise, the array is left as described, so filled with null values.
     *
     * @return array
     */
    public function getArrayExtended($bitCompressed = false) {
        $arrReturn = array();

        if(!$bitCompressed) {
            for($intI = 0; $intI < $this->intTotalElements; $intI++) {
                $arrReturn[] = null;
            }
        }
        //load data
        for($intI = $this->calculateStartPos(); $intI <= $this->calculateEndPos(); $intI++) {
            if(isset($this->arrSection[($intI - $this->calculateStartPos())])) {
                $arrReturn[$intI] = $this->arrSection[($intI - $this->calculateStartPos())];
            }
        }

        return $arrReturn;
    }

    public function valid() {
        return $this->intArrCursor < count($this->arrSection);
    }

    public function rewind() {
        $this->intArrCursor = 0;
    }

    public function current() {
        return $this->arrSection[$this->intArrCursor];
    }


    /**
     * Returns the elements placed on the given page
     *
     * @param int $intPageNumber
     *
     * @return array
     */
    public function getElementsOnPage($intPageNumber) {
        $arrReturn = array();
        //calc elements to return
        $intStart = ($this->intPageNumber * $this->intElementsPerPage) - $this->intElementsPerPage;
        $intEnd = $this->intElementsPerPage + $intStart - 1;
        if($intEnd > $this->getNumberOfElements()) {
            $intEnd = $this->getNumberOfElements() - 1;
        }

        for($intI = $intStart; $intI <= $intEnd; $intI++) {
            if(!$this->setCursorPosition($intI)) {
                break;
            }
            if(isset($this->arrElements[$this->intArrCursor]))
                $arrReturn[] = $this->arrElements[$this->intArrCursor];
        }
        return $arrReturn;
    }

}
