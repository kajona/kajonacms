<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_adminwidget_weather.php								    									*
* 	widget showing a few infos about the current weather	    										*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                            *
********************************************************************************************************/

include_once(_adminpath_."/widgets/class_adminwidget.php");
include_once(_adminpath_."/widgets/interface_adminwidget.php");

class class_adminwidget_weather extends class_adminwidget implements interface_adminwidget {
    
    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
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
        $strReturn .= $this->objToolkit->formInputDropdown("unit", array("f" => $this->getText("weather_fahrenheit"), "c" => $this->getText("weather_celsius")), 
                        $this->getText("weather_unit"), $this->getFieldValue("unit"));
        $strReturn .= $this->objToolkit->formTextRow($this->getText("weather_location_finder"));                
        $strReturn .= $this->objToolkit->formInputText("location", $this->getText("weather_location"), $this->getFieldValue("location"));
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
        
        //request the xml...
        $strSourceFeed = "http://weather.yahooapis.com/forecastrss?p=".$this->getFieldValue("location")."&u=".$this->getFieldValue("unit");
        $strContent = @file_get_contents($strSourceFeed);
        
        if($strContent != "") {
        	include_once(_systempath_."/class_xml_parser.php");
        	$objXmlparser = new class_xml_parser();
            $objXmlparser->loadString($strContent);
            
            $arrWeather = $objXmlparser->xmlToArray();
            
            if(isset($arrWeather["rss"][0]["channel"][0]["yweather:location"][0]["attributes"]["city"]))
                $strReturn .= $this->widgetText($this->getText("weather_location_string").$arrWeather["rss"][0]["channel"][0]["yweather:location"][0]["attributes"]["city"]);
                
            if(isset($arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:condition"][0]["attributes"]["temp"]))
                $strReturn .= $this->widgetText($this->getText("weather_temp_string")
                                    .$arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:condition"][0]["attributes"]["temp"]
                                    ." °".$arrWeather["rss"][0]["channel"][0]["yweather:units"][0]["attributes"]["temperature"]);

            $strReturn .= $this->widgetSeparator();
            $strReturn .= $this->widgetText($this->getText("weather_forecast"));                        
            
            $strReturn .= $this->widgetText($arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:forecast"][0]["attributes"]["date"].": "
                                .$arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:forecast"][0]["attributes"]["high"]
                                ." °".$arrWeather["rss"][0]["channel"][0]["yweather:units"][0]["attributes"]["temperature"]);
                                
            $strReturn .= $this->widgetText($arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:forecast"][1]["attributes"]["date"].": "
                                .$arrWeather["rss"][0]["channel"][0]["item"][0]["yweather:forecast"][1]["attributes"]["high"]
                                ." °".$arrWeather["rss"][0]["channel"][0]["yweather:units"][0]["attributes"]["temperature"]);                                
                                
        }
        else
            $strReturn .= $this->getText("weather_errorloading");
            
            
        
        return $strReturn;
    }
    
    
    /**
     * Return a short (!) name of the widget.
     *
     * @return 
     */
    public function getWidgetName() {
        return $this->getText("weather_name");
    }
    
}


?>