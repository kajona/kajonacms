<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package modul_pages
 */
class class_element_imagelightbox extends class_element_portal implements interface_portal_element {

    private $arrError = array();

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_imagelightbox";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_universal";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
	public function loadData() {
		$strReturn = "";

		$strImage = $this->arrElementData["char1"];

		//Include the javascript-file
		$strReturn .= "<script type=\"text/javascript\">\n";
		$strReturn .= "  kajonaAjaxHelper.addFileToLoad('"._webpath_."/portal/scripts/lightbox.js');\n";
		$strReturn .= " addCss('"._webpath_."/portal/css/lightbox.css');\n";
		$strReturn .= "</script>\n";


		$strReturn .= "<div>";

		//generate the preview
		$strReturn .= "<a href=\""._webpath_."/image.php?image=".$strImage."&amp;maxWidth=800&amp;maxHeight=800\" rel=\"lightbox\" title=\"".$this->arrElementData["char2"]."\">\n";
		$strReturn .= "<img src=\""._webpath_."/image.php?image=".$strImage."&amp;maxWidth=200&amp;maxHeight=200\" />\n";
		$strReturn .= "</a>";

		$strReturn .= "</div>";

		return $strReturn;
	}



}
?>