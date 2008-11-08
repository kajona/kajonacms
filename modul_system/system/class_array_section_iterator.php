<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/

include_once(_systempath_."/interface_iterator.php");
include_once(_systempath_."/class_array_iterator.php");

/**
 * Class to iterator over an array.
 * This class is able to create a pageview-mechanism
 *
 * @package modul_system
 */
class class_array_section_iterator extends class_array_iterator {

    private $intTotalElements;
    private $intPageNumber;
    private $arrSection;

	/**
	 * Constructor
	 *
	 * @param int $intNrOfElements The total number of elements
	 */
	public function __construct($intNrOfElements) {
		$this->arrModul["name"] 		= "class_array_section_iterator";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;

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
        $this->intPageNumber = $intPageNumber;
    }

    /**
     * calculates the start-pos of the current array-section
     *
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
        return ($this->intElementsPerPage + ($this->calculateStartPos()-1));
    }
    
    /**
     * Set the section of the array containing the data
     *
     * @param unknown_type $arrSectionContent
     */
    public function setArraySection($arrSectionContent) {
        $this->arrSection = $arrSectionContent;
    }
    
    /**
     * Creates an array containg only the needed key / value pairs. The other ones are empty.
     *
     * @return array
     */
    public function getArrayExtended() {
        $arrReturn = array();
        for($intI = 0; $intI < $this->intTotalElements; $intI++) {
            $arrReturn[] = "";
        }
        //load data
        for($intI = $this->calculateStartPos(); $intI <= $this->calculateEndPos(); $intI++)
            if(isset($this->arrSection[($intI-$this->calculateStartPos())]))
                $arrReturn[$intI] = $this->arrSection[($intI-$this->calculateStartPos())];
        
        return $arrReturn;
    }
    
    /**
     * Returns the elements placed on the given page
     *
     * @return array
     */
    public function getElementsOnPage() {
        $arrReturn = array();
        //calc elements to return
        $intStart = ($this->intPageNumber * $this->intElementsPerPage)-$this->intElementsPerPage;
        $intEnd = $this->intElementsPerPage + $intStart -1;
        if($intEnd > $this->getNumberOfElements())
            $intEnd = $this->getNumberOfElements()-1;

        for($intI = $intStart; $intI <= $intEnd; $intI++)  {
            if(!$this->setCursorPostion($intI))
                break;
            $arrReturn[] = $this->arrElements[$this->intArrCursor];
        }
        return $arrReturn;
    }
}
?>