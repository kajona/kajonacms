<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                        *
********************************************************************************************************/

include_once(_systempath_."/interface_modul_rating_sortalgo.php");

/**
 * Does an absolute, linear sorting based on the current rating-value
 * @package modul_rating
 */
class class_modul_rating_algo_absolute implements interface_modul_rating_sortalgo {
	
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
    	foreach($this->arrElements as $objOneElement) {
    		
    		
    		include_once (_systempath_."/class_modul_rating_rate.php");
            $objRating = class_modul_rating_rate::getRating($objOneElement->getSystemid());
    		var_dump($objRating->getRatingHistoryAsArray());
    		
    		$floatRating = $objOneElement->getFloatRating();
    		//avoid replacement of files having the same rating
    		while(isset($arrToSort[$floatRating]))
    		  $floatRating += 0.0001;
    		  
    		$arrToSort[$floatRating] = $objOneElement;
    	}
    	
    	ksort($arrToSort, SORT_NUMERIC);
    	$arrToSort = array_reverse($arrToSort);
    	
    	return $arrToSort;
    }
	
	
	
}

?>