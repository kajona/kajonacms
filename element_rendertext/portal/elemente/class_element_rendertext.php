<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_rendertext.php																		*
* 																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_zeitstempel.php 1487 2007-04-10 19:34:56Z rsr $                                  *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");
include_once(_systempath_."/class_modul_pages_page.php");
//necassary classes
require_once(_systempath_."/class_image.php");


/**
 * Portal-Part of the paragraph
 *
 * @package modul_pages
 */
class class_element_rendertext extends class_element_portal implements  interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModul["name"] 			= "element_rendertext";
		$arrModul["author"] 		= "rsr@itx.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"]			= _dbprefix_."element_rendertext";
		$arrModul["modul"]          = "elemente";

		parent::__construct($arrModul, $objElementData);
	}

	/**
	 * Does a little "make-up" to the contents
	 *
	 * @return string
	 */
	public function loadData() {

		$strReturn = "";
		
		$strFileType = ".png";
		eregi("\#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})", $this->arrElementData["rendertext_font_color"], $arrFontColor);
		eregi("\#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})", $this->arrElementData["rendertext_background_color"], $arrBackColor);
		$strFont = _systempath_."/fonts/".$this->arrElementData["rendertext_font_family"];
		$strFileName = "rendertext_".$this->arrElementData["content_id"].$strFileType;
		
		// to keep the image updated similar to the page-element
		// we should create a string, which contain all element
		// data, which should update the cached picture
		// IMPORANT:
		// this will create a great amount of pictures in the
		// cache-directory, which should be deleted sometimes
		$strCacheAdd = $this->arrElementData["content_id"];
		$strCacheAdd.= $this->arrElementData["rendertext_text"];
		$strCacheAdd.= $this->arrElementData["rendertext_font_family"];
		$strCacheAdd.= $this->arrElementData["rendertext_font_color"];
		$strCacheAdd.= $this->arrElementData["rendertext_font_size"];
		$strCacheAdd.= $this->arrElementData["rendertext_width"];
		$strCacheAdd.= $this->arrElementData["rendertext_height"];
		$strCacheAdd.= $this->arrElementData["rendertext_background_color"];
		$strCacheAdd.= $this->arrElementData["rendertext_transparency"];
		$strCacheAdd = trim($strCacheAdd);
		
		// create a blank image
		$objImage = new class_image("/"._bildergalerie_cachepfad_, $strCacheAdd);
        $objImage->setIntHeight($this->arrElementData["rendertext_height"]);
        $objImage->setIntWidth($this->arrElementData["rendertext_width"]);
        $objImage->createBlankImage($strFileType);
        
        //create the colors
        $intFGCol = $objImage->registerColor(hexdec($arrFontColor[1]), hexdec($arrFontColor[2]), hexdec($arrFontColor[3]));
        $intBGCol = $objImage->registerColor(hexdec($arrBackColor[1]), hexdec($arrBackColor[2]), hexdec($arrBackColor[3]));
        
        // transe?
		if ($this->arrElementData["rendertext_transparency"] == "checked")
			$objImage->setColorTransparent($intBGCol);
        
        // create a background
        $objImage->drawFilledRectangle(0, 0, $this->arrElementData["rendertext_width"], $this->arrElementData["rendertext_height"], $intBGCol);
        
        // add the text
		$objImage->imageText($this->arrElementData["rendertext_text"], 0, $this->arrElementData["rendertext_height"] - 10, (int)$this->arrElementData["rendertext_font_size"], $intFGCol, $this->arrElementData["rendertext_font_family"], true, 0);
		
		// save it and get the path
		$objImage->saveImage("", true);
		$strImgSrcWeb = _webpath_._bildergalerie_cachepfad_.$objImage->getCachename();
		
		//release memory
		$objImage->releaseResources();
				
		switch ($this->arrElementData["rendertext_mode"]) {
			case "rendertext_img":
				$strReturn .= "<img src=\"".$strImgSrcWeb."\" alt=\"".$this->arrElementData["rendertext_title"]."\" title=\"".$this->arrElementData["rendertext_title"]."\" id=\"".$this->arrElementData["content_id"]."\" name=\"".$this->arrElementData["content_id"]."\" />";
				break;
			case "rendertext_pfad":
				$strReturn .= $strImgSrcWeb;
				break;
			default:
				break;
		}

		return $strReturn;
	}

}	 //class_element_rendertext
?>