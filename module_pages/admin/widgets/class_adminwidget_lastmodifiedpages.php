<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$		                        *
********************************************************************************************************/


/**
 * @package modul_dashboard
 *
 */
class class_adminwidget_lastmodifiedpages extends class_adminwidget implements interface_adminwidget {

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

        $intMax = $this->getFieldValue("nrofrows");
        if($intMax < 0)
            $intMax = 1;

        $arrRecords = class_modul_system_common::getLastModifiedRecords($intMax, _pages_modul_id_);

        foreach($arrRecords as $objSingleRecord) {
            $objPage = new class_module_pages_page($objSingleRecord->getSystemid());
            $strReturn .= $this->widgetText(getLinkAdmin("pages_content", "list", "&systemid=".$objPage->getSystemid(), $objPage->getStrName()) );
            $strReturn .= $this->widgetText("&nbsp; &nbsp; ".timeToString($objPage->getIntLmTime())."");
        }

        return $strReturn;
    }


    /**
     * Return a short (!) name of the widget.
     *
     * @return
     */
    public function getWidgetName() {
        return $this->getText("lmpages_name");
    }

}


?>