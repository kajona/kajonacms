<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                        *
********************************************************************************************************/

/**
 * Does an absolute, linear sorting based on the current rating-value
 * @package module_rating
 */
class class_module_rating_sort_absolute implements interface_module_rating_sortalgo {
	
	private $arrElements = array();
	
	/**
     * Sets an array of elements to be sorted.
     * Elements have to be an instance of interface_sortable_rating.
     *
     * @param array $arrElements
     */
    public function setElementsArray($arrElements) {
    	$this->arrElements = $arrElements;
    }
    
    /**
     * Does the sorting and returns the sorted array of elements.
     *
     */
    public function doSorting() {
    	//move elements into a single array
    	$arrToSort = array();
        /** @var \Kajona\System\System\Model $objOneElement */
    	foreach($this->arrElements as $objOneElement) {
    		$floatRating = $objOneElement->getFloatRating();
            
    		//avoid replacement of files having the same rating
    		while(isset($arrToSort["".$floatRating]))
    		  $floatRating += 0.0001;
    		  
    		$arrToSort["".$floatRating] = $objOneElement;
    	}

    	ksort($arrToSort, SORT_NUMERIC);
    	$arrToSort = array_reverse($arrToSort);
        
    	return $arrToSort;
    }
	
	
	
}

