<?php
/*"******************************************************************************************************
 *   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
 *   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
 *       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
 *-------------------------------------------------------------------------------------------------------*
 *   $Id$                                     *
 ********************************************************************************************************/

namespace Kajona\Maps\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryButton;
use Kajona\System\Admin\Formentries\FormentryPlaintext;
use Kajona\System\Admin\Formentries\FormentryTextrow;


/**
 * Admin class to handle the maps
 *
 * @author jschroeter@kajona.de
 *
 * @targetTable element_universal.content_id
 */
class ElementMapsAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType text
     * @fieldLabel maps_address
     * @fieldMandatory
     *
     * @addSearchIndex
     *
     * @elementContentTitle
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType hidden
     *
     * @addSearchIndex
     */
    private $strChar2;

    /**
     * @var string
     * @tableColumn element_universal.char3
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @addSearchIndex
     *
     * @fieldTemplateDir /module_maps
     */
    private $strChar3;

    /**
     * @var string
     * @tableColumn element_universal.text
     * @blockEscaping
     *
     * @fieldType wysiwyg
     * @fieldLabel maps_infotext
     *
     * @addSearchIndex
     */
    private $strText;

    /**
     * Creates the backend form to enter a new map configuration
     * @return AdminFormgenerator|null
     */
    public function getAdminForm()
    {
        $objForm = parent::getAdminForm();


        $objForm->addField(new FormentryButton("", "geocode_button"))->setStrLabel($this->getLang("maps_geocode_button"))->setStrEventhandler("onclick=\"lookupAddress(); return false;\"");
        $objForm->addField(new FormentryTextrow("geocode_hint"))->setStrValue($this->getLang("maps_geocode_hint"));

        $objForm->setFieldToPosition("geocode_button", 2);
        $objForm->setFieldToPosition("geocode_hint", 2);

        $floatLat = "47.660727";
        $floatLng = "9.181154";
        if ($this->getStrChar2() != "") {
            $arrLatLng = explode(',', $this->getStrChar2());
            if (count($arrLatLng) == 2) {
                $floatLat = $arrLatLng[0];
                $floatLng = $arrLatLng[1];
            }
        }


        $strJs = "
		<div id=\"map_canvas\" style=\"width: 640px; height: 400px;\"></div>

		<script type=\"text/javascript\" src=\"http://maps.googleapis.com/maps/api/js?sensor=false\"></script>
	    <script type=\"text/javascript\">
			var map;
			var infoWindow;
			var startPos = new google.maps.LatLng('" . $floatLat . "', '" . $floatLng . "');
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
					   alert('" . addslashes($this->getLang("maps_geocode_error")) . "'.replace('{error}', status));
					}
			    });
			}
        </script>";

        $objForm->addField(new FormentryPlaintext("js"))->setStrValue($strJs);

        return $objForm;
    }

    /**
     * @param $strText
     *
     */
    public function setStrText($strText)
    {
        $this->strText = $strText;
    }

    /**
     * @return string
     */
    public function getStrText()
    {
        return $this->strText;
    }

    /**
     * @param string $strChar3
     */
    public function setStrChar3($strChar3)
    {
        $this->strChar3 = $strChar3;
    }

    /**
     * @return string
     */
    public function getStrChar3()
    {
        return $this->strChar3;
    }

    /**
     * @param string $strChar2
     */
    public function setStrChar2($strChar2)
    {
        $this->strChar2 = $strChar2;
    }

    /**
     * @return string
     */
    public function getStrChar2()
    {
        return $this->strChar2;
    }

    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1)
    {
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1()
    {
        return $this->strChar1;
    }


}
