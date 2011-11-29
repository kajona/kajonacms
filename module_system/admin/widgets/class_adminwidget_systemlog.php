<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$		                        *
********************************************************************************************************/


/**
 * @package module_dashboard
 *
 */
class class_adminwidget_systemlog extends class_adminwidget implements interface_adminwidget {

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("nrofrows"));
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputText("nrofrows", $this->getText("syslog_nrofrows"), $this->getFieldValue("nrofrows"));
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
        $strLogContent = class_logger::getInstance()->getLogFileContent();
        $arrLogEntries = explode("\n", $strLogContent);
        $arrLogEntries = array_reverse($arrLogEntries);

        for($intI = 0; $intI <= $this->getFieldValue("nrofrows") && $intI < 10 && $intI < count($arrLogEntries); $intI++ ) {
            $strLog = htmlToString($arrLogEntries[$intI], true);
            $strLog = uniStrReplace(array("INFO", "ERROR", "WARNING"), array("<span style=\"color: green\">INFO</span>",
            																"<span style=\"color: red\">ERROR</span>",
            																"<span style=\"color: orange\">WARNING</span>"), $strLog);
            $strReturn .= $this->widgetText($strLog);
        }

        $strReturn .= $this->widgetSeparator();

        $strLogContent = (is_file(_realpath_._projectpath_."/log/php.log") ? file_get_contents(_realpath_._projectpath_."/log/php.log") : "");
        $arrLogEntries = explode("\n", $strLogContent);
        $arrLogEntries = array_reverse($arrLogEntries);

        for($intI = 0; $intI <= $this->getFieldValue("nrofrows") && $intI < 10 && $intI < count($arrLogEntries); $intI++ ) {
            $strLog = htmlToString($arrLogEntries[$intI], true);

            $strReturn .= $this->widgetText($strLog);
        }

        return $strReturn;
    }


    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName() {
        return $this->getText("syslog_name");
    }

}


?>