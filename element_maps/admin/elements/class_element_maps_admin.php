<?php
/*"******************************************************************************************************
 *   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
 *   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
 *       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
 *-------------------------------------------------------------------------------------------------------*
 *   $Id: class_element_paragraph.php 3952 2011-06-26 12:13:25Z sidler $                                     *
 ********************************************************************************************************/

/**
 * Admin class to handle the maps
 *
 * @package element_maps
 * @author jschroeter@kajona.de
 */
class class_element_maps_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Contructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_maps");
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
        $this->setArrModuleEntry("tableColumns", "");
		parent::__construct();
	}


	/**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {

		$strReturn = "";

		$strReturn .= $this->objToolkit->formInputText("char1", $this->getLang("maps_address"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : ""));
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("maps_geocode_button"), "geocode", "onclick=\"lookupAddress(); return false;\"");
		$strReturn .= $this->objToolkit->formTextRow($this->getLang("maps_geocode_hint"));
		$strReturn .= $this->objToolkit->formInputHidden("char2", (isset($arrElementData["char2"]) ? $arrElementData["char2"] : ""));
		$strReturn .= $this->objToolkit->formWysiwygEditor("text", $this->getLang("maps_infotext"), (isset($arrElementData["text"]) ? $arrElementData["text"] : ""));
        $strReturn .= $this->objToolkit->formHeadline($this->getLang("maps_preview"));
        
        $floatLat = "47.660727";
        $floatLng = "9.181154";
        if (isset($arrElementData["char2"])) {
	        $arrLatLng = explode(',', $arrElementData["char2"]);
	        if (count($arrLatLng) == 2) {
	        	$floatLat = $arrLatLng[0];
	            $floatLng = $arrLatLng[1];
	        }
        }
        
        
		$strReturn .= "
		<div id=\"map_canvas\" style=\"width: 640px; height: 400px;\"></div>
		
		<script type=\"text/javascript\" src=\"http://maps.googleapis.com/maps/api/js?sensor=false\"></script> 
	    <script type=\"text/javascript\">
			var map;
			var infoWindow;
			var startPos = new google.maps.LatLng(".$floatLat.", ".$floatLng.");
			var geocoder = new google.maps.Geocoder();

			var mapOptions = {
				zoom: 10,
				center: startPos,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
			
			var marker = new google.maps.Marker({
                position: startPos,
                map: map,
                draggable: true
            });
               
            infoWindow = new google.maps.InfoWindow();
            infoWindow.setPosition(startPos);
            infoWindow.setContent(document.getElementById('text').value);
            infoWindow.open(map);
            
            google.maps.event.addListener(marker, 'click', function() {
                infoWindow.open(map);
            });
                
            google.maps.event.addListener(marker, 'dragend', function(event) {
                document.getElementById('char2').value = event.latLng.toUrlValue();
                infoWindow.setPosition(event.latLng);
			});
			
            //refresh infoWindow when content in CKEditor was changed
            var editor = CKEDITOR.instances['text'];
			var timer;
			function somethingChanged() {
			    if (timer)
			        return;
			 
			    timer = setTimeout(function() {
			        timer = 0;
			        infoWindow.setContent(editor.getData());
			    }, 200);
			}
                
            editor.on('key', somethingChanged);
            editor.on('paste', somethingChanged);
            editor.on('afterCommandExec', somethingChanged);
                
			function lookupAddress() {
				var address = document.getElementById('char1').value;
				geocoder.geocode( {'address': address}, function (results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
					   var pos = results[0].geometry.location;
					   map.setCenter(pos);
					   marker.setPosition(pos);
					   infoWindow.setPosition(pos);
					   document.getElementById('char2').value = pos.toUrlValue();
					} else {
					   alert('".$this->getLang("maps_geocode_error")."'.replace('{error}', status));
					}
			    });
			}
        </script>";


		//load templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_maps", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
		    $this->addOptionalFormElement($this->objToolkit->formInputDropdown("char3", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char3"]) ? $arrElementData["char3"] : "" )));
		else
		    $strReturn .= $this->objToolkit->formInputDropdown("char3", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char3"]) ? $arrElementData["char3"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("char1");
		return $strReturn;
	}

    /**
     * saves the submitted data to the database
     * It IS wanted to not let the system save the element here!
     *
     * @param string $strSystemid
     * @return bool
     */
    public function actionSave($strSystemid) {
    	
    	$strContent = processWysiwygHtmlContent($this->getParam("text"));
    	
    	if (trim(str_replace("&nbsp;", "", strip_tags($strContent))) == "") {
    		$strContent = "";
    	}
    	
        $strQuery = "UPDATE ".$this->getArrModule("table")." SET
                char1 = ?,
                char2 = ?,
                char3 = ?,
                text = ?
                WHERE content_id= ?";

        if(
            $this->objDB->_pQuery(
                $strQuery,
                array($this->getParam("char1"), $this->getParam("char2"), $this->getParam("char3"), $strContent, $strSystemid),
                array(true, true, true, false)
            )
        )
            return true;
        else
            return false;
    }

	/**
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {
		$arrData = $this->loadElementData();
		return uniStrTrim(htmlStripTags($arrData["char1"]), 60);
	}


}
