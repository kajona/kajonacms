<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package modul_pages
 */
class class_element_imagelightbox extends class_element_portal implements interface_portal_element {
    private $intThumbMaxWidth = 200;
    private $intThumbMaxHeight = 200;
    private $intDetailMaxWidth = 800;
    private $intDetailMaxHeight = 800;

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
	        if (YAHOO.lang.isUndefined(arrViewers)) {
	            var arrViewers = new Array();

	            //add viewer: all images with class \"photoViewer\" in the div with the id \"contentContainer\"
	            arrViewers.push(\"contentContainer\");

	            YAHOO.util.Event.onDOMReady(function () {
	                YAHOO.namespace(\"YAHOO.photoViewer\");
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
	                            buttonText: {
	                                next: \" \",
	                                prev: \" \",
	                                close: \"X\"
	                            },
	                            /* remove/rename the slideShow property to disable slideshow feature */
	                            slideShow: {
	                                autoStart: false,
	                                duration: 3500,
	                                controlsText: {
	                                    play: \" \",
	                                    pause: \" \",
	                                    stop: \" \",
	                                    display: \"{0}/{1}\"
	                                }
	                            }
	                        }
	                    };
	                }
	            });

	            kajonaAjaxHelper.Loader.load(
	                [\"dragdrop\", \"animation\", \"container\"],
	                [KAJONA_WEBPATH+\"/portal/scripts/photoviewer/build/photoviewer_base.js\",
	                 KAJONA_WEBPATH+\"/portal/scripts/photoviewer/assets/skins/kajona/kajona.css\"]
	            );
	        }
		</script>";

		$strReturn .= "<div class=\"imagelightbox\">";

		//generate the preview
		$strReturn .= "<a href=\""._webpath_."/image.php?image=".$strImage."&amp;maxWidth=".$this->intDetailMaxWidth."&amp;maxHeight=".$this->intDetailMaxHeight."\" class=\"photoViewer\" title=\"".$this->arrElementData["char2"]."\">\n";
		$strReturn .= "<img src=\""._webpath_."/image.php?image=".$strImage."&amp;maxWidth=".$this->intThumbMaxWidth."&amp;maxHeight=".$this->intThumbMaxHeight."\" alt=\"".$this->arrElementData["text"]."\" />\n";
		$strReturn .= "</a>";

		$strReturn .= "</div>";

		return $strReturn;
	}


}
?>