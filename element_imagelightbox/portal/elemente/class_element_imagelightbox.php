<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
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


	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
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

		//some javascript
		$strReturn .= "<script type=\"text/javascript\">
			if (typeof bitPhotoViewerLoadingStarted == \"undefined\") {
	            var bitPhotoViewerLoadingStarted = false;
	            var arrViewers = new Array();
	        }

	        //add viewer
	        arrViewers.push(\"pv_".$this->getSystemid()."\");

	        kajonaAjaxHelper.loadPhotoViewerBase = function(callback) {
	            if (!bitPhotoViewerLoadingStarted) {
	                bitPhotoViewerLoadingStarted = true;

	                var l = new kajonaAjaxHelper.Loader();
	                l.addYUIComponents([ \"dragdrop\", \"animation\", \"container\" ]);
	                l.addJavascriptFile(\"photoviewer/build/photoviewer_base-min.js\");
	                l.addCssFile(\"_webpath_/portal/scripts/photoviewer/build/photoviewer_base.css\");
	                l.addCssFile(\"_webpath_/portal/scripts/photoviewer/assets/skins/vanillamin/vanillamin.css\");
	                l.load(callback);
	            }
	        };

	        kajonaAjaxHelper.loadPhotoViewerBase(function () {
	            YAHOO.photoViewer.config = { viewers: {} };

	            //init all viewers
	            for (var i=0; i<arrViewers.length; i++) {
	                YAHOO.photoViewer.config.viewers[arrViewers[i]] = {
	                    properties: {
	                        id: arrViewers[i],
	                        grow: 0.2,
	                        fade: 0.2,
	                        modal: true,
	                        dragable: false,
	                        fixedcenter: true,
	                        loadFrom: \"html\",
	                        position: \"absolute\",
	                        easing: YAHOO.util.Easing.easeBothStrong,
	                        buttonText: {
	                            next: \" \",
	                            prev: \" \",
	                            close: \"Schließen\"
	                        }
	                    }
	                };
	            }
	        });
		</script>";

		$strReturn .= "<div id=\"pv_".$this->getSystemid()."\">";

		//generate the preview
		$strReturn .= "<a href=\""._webpath_."/image.php?image=".$strImage."&amp;maxWidth=800&amp;maxHeight=800\" class=\"photoViewer\" title=\"".$this->arrElementData["char2"]."\">\n";
		$strReturn .= "<img src=\""._webpath_."/image.php?image=".$strImage."&amp;maxWidth=200&amp;maxHeight=200\" />\n";
		$strReturn .= "</a>";

		$strReturn .= "</div>";

		return $strReturn;
	}


}
?>