<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_array_iterator.php																			*
* 	Class to iterate over an array.           															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

include_once(_systempath_."/interface_iterator.php");

/**
 * Class to iterator over an array.
 * This class is able to create a pageview-mechanism
 *
 * @package modul_system
 */
class class_array_iterator implements interface_iterator {

    private $arrElements = array();
    private $arrModule = array();
    private $intArrCursor = 0;

    protected $intElementsPerPage;

	/**
	 * Constructor
	 *
	 */
	public function __construct($arrElements) {
		$this->arrModul["name"] 		= "class_array_iterator";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;

        //Loop over elements to create numeric indizees
        if(count($arrElements) > 0) {
            foreach ($arrElements as $objOneElement) {
                $this->arrElements[] = $objOneElement;
            }
        }
        else {
            $this->arrElements = array();
        }
	}

	/**
     * Returns the current element
     *
     * @return mixed
     */
    public function getCurrentElement() {
        return $this->arrElements[$this->intArrCursor];
    }

    /**
     * Returns the next element, null if no further element available
     *
     * @return mixed
     */
    public function getNextElement() {
        if(!$this->isNext())
            return null;

        return $this->arrElements[++$this->intArrCursor];
    }

    /**
     * Checks if theres one more element to return
     *
     * @return bool
     */
    public function isNext() {
        return ($this->intArrCursor < count($this->arrElements));
    }

    /**
     * Returns the first element of the colection,
     * rewinds the cursor
     *
     * @return mixed
     */
    public function getFirstElement() {
        $this->intArrCursor = 0;
        return $this->arrElements[$this->intArrCursor];
    }

    /**
     * Returns the number of elements
     *
     * @return int
     */
    public function getNumberOfElements() {
        return count($this->arrElements);
    }

// --- PageViewStuff ------------------------------------------------------------------------------------

    /**
     * Set the number of elements per page
     *
     * @param int $intElements
     */
    public function setIntElementsPerPage($intElements) {
        $this->intElementsPerPage = $intElements;
    }

    /**
     * Set the cursor to a definded position
     *
     * @param int $intElement
     * @return bool
     */
    public function setCursorPostion($intElement) {
        if($this->getNumberOfElements() > $intElement) {
            $this->intArrCursor = $intElement;
            return true;
        }
        else
            return false;

    }

    /**
     * Returns the number of pages available
     *
     * @return int
     */
    public function getNrOfPages() {
        if($this->intElementsPerPage == (int)0)
            return 0;

        return ceil($this->getNumberOfElements() / $this->intElementsPerPage);
    }

    /**
     * Returnsthe elements placed on the given page
     *
     * @param int $intPageNumber
     * @return array
     */
    public function getElementsOnPage($intPageNumber) {
        $arrReturn = array();
        //calc elements to return
        $intStart = ($intPageNumber * $this->intElementsPerPage)-$this->intElementsPerPage;
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