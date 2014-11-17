<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                            *
********************************************************************************************************/

/**
 * @package module_dashboard
 */
class class_adminwidget_weather extends class_adminwidget implements interface_adminwidget {

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("unit", "location"));
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputDropdown(
            "unit",
            array(
                "f" => $this->getLang("weather_fahrenheit"),
                "c" => $this->getLang("weather_celsius")
            ),
            $this->getLang("weather_unit"),
            $this->getFieldValue("unit")
        );
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("weather_location_finder"));
        $strReturn .= $this->objToolkit->formInputText("location", $this->getLang("weather_location"), $this->getFieldValue("location"));
        return $strReturn;
    }

    /**
     * This method is called, when the widget should generate it's content.
     * Return the complete content using the methods provided by the base class.
     * Do NOT use the toolkit right here!
     *
     * @return string
     */
    public function getWidgetOutput() {
        $strReturn = "";

        if($this->getFieldValue("location") == "") {
            return "Please set up a location";
        }

        //request the xml...
        try {
            $objRemoteloader = new class_remoteloader();
            $objRemoteloader->setStrHost("weather.yahooapis.com");
            $objRemoteloader->setStrQueryParams("/forecastrss?p=" . $this->getFieldValue("location") . "&u=" . $this->getFieldValue("unit"));
            $strContent = $objRemoteloader->getRemoteContent();
        }
        catch(class_exception $objExeption) {
            $strContent = "";
        }

        if($strContent != "") {
            $objXmlparser = new class_xml_parser();
            $objXmlparser->loadString($strContent);

            $arrWeather = $objXmlparser->xmlToArray();
            if(isset($arrWeather["rss"])) {
                if(isset($arrWeather["rss"][0]["channel"][0]["yweather:location"][0]["attributes"]["city"])) {
                    $strReturn .= $this->widgetText($this->getLang("weather_location_string") . $arrWeather["rss"][0]["channel"][0]["yweather:location"][0]["attributes"]["city"]);
                }

                if(isset($arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:condition"][0]["attributes"]["temp"])) {
                    $strReturn .= $this->widgetText(
                        $this->getLang("weather_temp_string")
                        . $arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:condition"][0]["attributes"]["temp"] . " °"
                        . $arrWeather["rss"][0]["channel"][0]["yweather:units"][0]["attributes"]["temperature"]
                    );
                }

                $strReturn .= $this->widgetSeparator();
                $strReturn .= $this->widgetText($this->getLang("weather_forecast"));

                $strReturn .= $this->widgetText(
                    $arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:forecast"][0]["attributes"]["date"] . ": "
                    . $arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:forecast"][0]["attributes"]["high"]
                    . " °" . $arrWeather["rss"][0]["channel"][0]["yweather:units"][0]["attributes"]["temperature"]
                );

                $strReturn .= $this->widgetText(
                    $arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:forecast"][1]["attributes"]["date"] . ": "
                    . $arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:forecast"][1]["attributes"]["high"]
                    . " °" . $arrWeather["rss"][0]["channel"][0]["yweather:units"][0]["attributes"]["temperature"]
                );
            }
            else {
                $strReturn .= $this->getLang("weather_errorloading");
            }

        }
        else {
            $strReturn .= $this->getLang("weather_errorloading");
        }


        return $strReturn;
    }

    /**
     * This callback is triggered on a users' first login into the system.
     * You may use this method to install a widget as a default widget to
     * a users dashboard.
     *
     * @param $strUserid
     *
     * @return bool
     */
    public function onFistLogin($strUserid) {
        return true;
    }


    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName() {
        return $this->getLang("weather_name");
    }

}


