<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/


/**
 * Admin-Part of the toolkit-classes
 *
 * @package module_system
 */
class class_toolkit_admin extends class_toolkit {

    /**
     * Constructor
     *
     * @param string $strSystemid
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"]           = "modul_elemente_admin";
        $arrModul["author"]         = "sidler@mulchprod.de";

        //Calling the base class
        parent::__construct($arrModul, $strSystemid);
    }


    /**
     * Returns a simple Date-Form
     *
     * @param string $strName
     * @param int $intDay
     * @param int $intMonth
     * @param int $intYear
     * @param string $strTitle
     * @param bool $bitToday If set true, the current date will be inserted, if no date is passed
     * @param string $strClass
     * @return string
     * @deprecated will be removed in 3.3.x Use formDateSingle() instead.
     */
    public function formDateSimple($strName = "", $intDay = "", $intMonth = "", $intYear = "", $strTitle = "", $bitToday = true, $strClass = "inputDate") {
        //no given values, use today
        if($bitToday) {
            if($intDay == "")
                $intDay = strftime("%d", time());
            if($intMonth == "")
                $intMonth = strftime("%m", time());
            if($intYear == "")
                $intYear = strftime("%Y", time());
        }
        if($strName != "")
            $strName .= "_";

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_date_simple");
        $arrTemplate = array();
        $arrTemplate["class"] = $strClass;
        $arrTemplate["titleDay"] = $strName."_day";
        $arrTemplate["titleMonth"] = $strName."_month";
        $arrTemplate["titleYear"] = $strName."_year";
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["valueDay"] = $intDay;
        $arrTemplate["valueMonth"] = $intMonth;
        $arrTemplate["valueYear"] = $intYear;

        //set up the container div
        $arrTemplate["calendarId"] = $strName;
        $strContainerId = $strName."_calendarContainer";
        $arrTemplate["calendarContainerId"] = $strContainerId;

        //commands and values for the calendar
        $arrTemplate["calendarCommands"]  ="<script type=\"text/javascript\">\n";
        $arrTemplate["calendarCommands"] .="    KAJONA.admin.lang.toolsetCalendarWeekday = [".class_carrier::getInstance()->getObjText()->getText("toolsetCalendarWeekday", "system", "admin")."];\n";
        $arrTemplate["calendarCommands"] .="    KAJONA.admin.lang.toolsetCalendarMonth = [".class_carrier::getInstance()->getObjText()->getText("toolsetCalendarMonth", "system", "admin")."];\n";
        $arrTemplate["calendarCommands"] .="</script>\n";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Returns a simple date-form element. By default used to enter a date without a time.
     *
     * @param string $strName
     * @param string $strTitle
     * @param class_date $objDateToShow
     * @param string $strClass = inputDate
     * @param boolean $bitWithTime
     * @return string
     * @since 3.2.0.9
     */
    public function formDateSingle($strName, $strTitle, $objDateToShow, $strClass = "inputDate", $bitWithTime = false) {
        //check passed param
        if($objDateToShow != null && !$objDateToShow instanceof class_date)
            throw new class_exception("param passed to class_toolkit_admin::formDateSingle is not an instance of class_date", class_exception::$level_ERROR);

        if($bitWithTime)
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_datetime_simple");
        else
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_date_simple");
        $arrTemplate = array();
        $arrTemplate["class"] = $strClass;
        $arrTemplate["titleDay"] = $strName."_day";
        $arrTemplate["titleMonth"] = $strName."_month";
        $arrTemplate["titleYear"] = $strName."_year";
        $arrTemplate["titleHour"] = $strName."_hour";
        $arrTemplate["titleMin"] = $strName."_minute";
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["valueDay"] = $objDateToShow != null ? $objDateToShow->getIntDay() : "";
        $arrTemplate["valueMonth"] = $objDateToShow != null ? $objDateToShow->getIntMonth() : "";
        $arrTemplate["valueYear"] = $objDateToShow != null ? $objDateToShow->getIntYear() : "";
        $arrTemplate["valueHour"] = $objDateToShow != null ? $objDateToShow->getIntHour() : "";
        $arrTemplate["valueMin"] = $objDateToShow != null ? $objDateToShow->getIntMin() : "";

        $arrTemplate["titleTime"] = class_carrier::getInstance()->getObjText()->getText("titleTime", "system", "admin");

        //set up the container div
        $arrTemplate["calendarId"] = $strName;
        $strContainerId = $strName."_calendarContainer";
        $arrTemplate["calendarContainerId"] = $strContainerId;

        //commands and values for the calendar
        $arrTemplate["calendarCommands"]  ="<script type=\"text/javascript\">\n";
	    $arrTemplate["calendarCommands"] .="    KAJONA.admin.lang.toolsetCalendarWeekday = [".class_carrier::getInstance()->getObjText()->getText("toolsetCalendarWeekday", "system", "admin")."];\n";
	    $arrTemplate["calendarCommands"] .="    KAJONA.admin.lang.toolsetCalendarMonth = [".class_carrier::getInstance()->getObjText()->getText("toolsetCalendarMonth", "system", "admin")."];\n";
        $arrTemplate["calendarCommands"] .="</script>\n";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Returns a text-field using the cool WYSIWYG editor
     * You can use the different toolbar sets defined in /admin/scripts/ckeditor/config.js
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strContent
     * @param string $strToolbarset
     * @return string
     */
    public function formWysiwygEditor($strName = "inhalt", $strTitle = "", $strContent = "", $strToolbarset = "standard") {
        $strReturn = "";

        //create the html-input element
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "wysiwyg_ckeditor");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["content"] = htmlentities($strContent, ENT_COMPAT, "UTF-8");
        $strReturn .=  $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
        //for the popups, we need the skinwebpath
        $strReturn .= $this->formInputHidden("skinwebpath", _skinwebpath_);

        //set the language the user defined for the admin
        $strLanguage = class_session::getInstance()->getAdminLanguage();
        if($strLanguage == "")
            $strLanguage = "en";

        //include the settings made by admin skin
        $strTemplateInitID = $this->objTemplate->readTemplate("/elements.tpl", "wysiwyg_ckeditor_inits");
        $strTemplateInit = $this->objTemplate->fillTemplate(array(), $strTemplateInitID);

        //to add role-based editors, you could load a different toolbar or also a different CKEditor config file
        //the editor code
        $strReturn .= " <script type=\"text/javascript\" src=\""._webpath_."/admin/scripts/ckeditor/ckeditor.js\"></script>\n";
        $strReturn .= " <script type=\"text/javascript\">\n";
        $strReturn .= "
            var ckeditorConfig = {
                customConfig : 'config_kajona_standard.js',
                toolbar : '".$strToolbarset."',
                ".$strTemplateInit."
                language : '".$strLanguage."',
                filebrowserBrowseUrl : '".uniStrReplace("&amp;", "&", getLinkAdminHref("folderview", "browserChooser", "&form_element=ckeditor"))."',
                filebrowserImageBrowseUrl : '".uniStrReplace("&amp;", "&", getLinkAdminHref("filemanager", "folderContentFolderviewMode", "systemid="._filemanager_default_imagesrepoid_."&suffix=.jpg|.gif|.png&form_element=ckeditor&bit_link=1"))."'
	        };
            CKEDITOR.replace('".$strName."', ckeditorConfig);
        ";
        $strReturn .= "</script>\n";

        return $strReturn;
    }


    /**
     * Returns a divider to split up a page in logical sections
     *
     * @param string $strClass
     * @return string
     */
    public function divider($strClass = "divider") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "divider");
        $arrTemplate = array();
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Creates a percent-beam to illustrate proportions
     *
     * @param float $floatPercent
     * @param int $intLength
     * @return string
     */
    public function percentBeam($floatPercent, $intLength = "300")  {
        //Calc width
        $intWidth = $intLength - 50;
        $intBeamLength = ceil($intWidth * $floatPercent / 100);
        if($intBeamLength == 0)
            $intBeamLength = 1;

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "percent_beam");
        $arrTemplate = array();
        $arrTemplate["length"] = $intLength;
        $arrTemplate["percent"] = number_format($floatPercent, 2);
        $arrTemplate["width"] = $intWidth;
        if($arrTemplate["percent"] == "100.00")
            $arrTemplate["beamwidth"] = $intBeamLength;
        else
            $arrTemplate["beamwidth"] = $intBeamLength-1;
        if(($intWidth - $intBeamLength) <= 0 || $arrTemplate["percent"] == "100.00")
            $arrTemplate["transTillEnd"] = "";
         else
            $arrTemplate["transTillEnd"] = "<img src=\"_skinwebpath_/trans.gif\" width=\"".($intWidth - $intBeamLength-1)."\" height=\"1\" />";
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


/*"*****************************************************************************************************/
// --- FORM-Elements ------------------------------------------------------------------------------------

    /**
     * Returns a checkbox
     *
     * @param string $strName
     * @param string $strTitle
     * @param bool $bitChecked
     * @param string $strClass
     * @return string
     */
    public function formInputCheckbox($strName, $strTitle, $bitChecked = false, $strClass = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_checkbox");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["checked"] = ($bitChecked ? "checked=\"checked\"" : "");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a regular hidden-input-field
     *
     * @param string $strName
     * @param string $strValue
     * @return string
     */
    public function formInputHidden($strName, $strValue = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_hidden");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = $strValue;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Returns a regular text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     * @param string $strOpener
     * @param bool $bitReadonly
     * @return string
     */
    public function formInputText($strName, $strTitle = "", $strValue = "", $strClass = "inputText", $strOpener = "", $bitReadonly = false) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_text");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = $strValue;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["opener"] = $strOpener;
        $arrTemplate["readonly"] = ($bitReadonly ? "readonly=\"readonly\"" : "");

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

   /**
     * Returns a regular text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     * @param bool $bitElements
     * @return string
     */
    public function formInputPageSelector($strName, $strTitle = "", $strValue = "", $strClass = "inputText", $bitElements = true) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_pageselector");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = $strValue;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["opener"] = getLinkAdminDialog("pages",
                                                   "pagesFolderBrowser",
                                                   "&pages=1&form_element=".$strName.(!$bitElements ? "&elements=false" : ""),
                                                   class_carrier::getInstance()->getObjText()->getText("select_page", "pages", "admin"),
                                                   class_carrier::getInstance()->getObjText()->getText("select_page", "pages", "admin"),
                                                   "icon_externalBrowser.gif",
                                                   class_carrier::getInstance()->getObjText()->getText("select_page", "pages", "admin"));

        $strJsVarName = uniStrReplace(array("[", "]"), array("", ""), $strName);

        $arrTemplate["ajaxScript"] = "
	        <script type=\"text/javascript\">
	            KAJONA.admin.loader.loadAutocompleteBase(function () {
	                var pageDataSource = new YAHOO.util.XHRDataSource(KAJONA_WEBPATH+\"/xml.php\");
	                pageDataSource.responseType = YAHOO.util.XHRDataSource.TYPE_XML;
	                pageDataSource.responseSchema = {
	                    resultNode : \"page\",
	                    fields : [\"title\"]
	                };

	                var pageautocomplete = new YAHOO.widget.AutoComplete(\"".$strName."\", \"".$strName."_container\", pageDataSource, {
	                    queryMatchCase: false,
	                    allowBrowserAutocomplete: false,
	                    useShadow: false
	                });
	                pageautocomplete.generateRequest = function(sQuery) {
	                    return \"?admin=1&module=pages&action=getPagesByFilter&filter=\" + sQuery ;
	                };

	                //keep a reference to the autocomplete widget, maybe we want to attach some listeners later
	                KAJONA.admin.".$strJsVarName." = pageautocomplete;
	            });
	        </script>
        ";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    /**
     * Returns a regular text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     * @param bool $bitUser
     * @param bool $bitGroup
     * @param bool $bitBlockCurrentUser
     * @return string
     */
    public function formInputUserSelector($strName, $strTitle = "", $strValue = "", $strClass = "inputText", $bitUser = true, $bitGroups = false, $bitBlockCurrentUser = false) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_userselector");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = $strValue;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["opener"] = getLinkAdminDialog("user",
                                              "userBrowser",
                                               "&form_element=".$strName.($bitGroups ? "&allowGroup=1" : "").($bitBlockCurrentUser ? "&filter=current" : ""),
                                               class_carrier::getInstance()->getObjText()->getText("user_browser", "user", "admin"),
                                               class_carrier::getInstance()->getObjText()->getText("user_browser", "user", "admin"),
                                               "icon_externalBrowser.gif",
                                               class_carrier::getInstance()->getObjText()->getText("user_browser", "user", "admin"));

        $strJsVarName = uniStrReplace(array("[", "]"), array("", ""), $strName);

        $arrTemplate["ajaxScript"] = "
	        <script type=\"text/javascript\">
	            KAJONA.admin.loader.loadAutocompleteBase(function () {
	                var userDataSource = new YAHOO.util.XHRDataSource(KAJONA_WEBPATH+\"/xml.php\");
	                userDataSource.responseType = YAHOO.util.XHRDataSource.TYPE_XML;
	                userDataSource.responseSchema = {
	                    resultNode : \"user\",
	                    fields : [\"title\", \"icon\", \"systemid\"]
	                };

	                var userautocomplete = new YAHOO.widget.AutoComplete(\"".$strName."\", \"".$strName."_container\", userDataSource, {
	                    queryMatchCase: false,
	                    allowBrowserAutocomplete: false,
	                    useShadow: false
	                });
	                userautocomplete.generateRequest = function(sQuery) {
	                    return \"?admin=1&module=user&action=getUserByFilter&user=".
                                    ($bitUser ? "true" : "false")."&group=".
                                    ($bitGroups ? "true" : "false").
                                    ($bitBlockCurrentUser ? "&block=current" : "")."&filter=\" + sQuery ;
	                };

                    userautocomplete.formatResult = function(oResultData, sQuery, sResultMatch) {
                        var sOutput = \"<span class='userSelectorAC' style='background-image: url(\"+oResultData[1]+\"); '>\"+sResultMatch+\"</span>\";
                        var sMarkup = (sResultMatch) ? sOutput : \"\";
                        return sMarkup;
                    };

                    var itemSelectHandler = function(sType, aArgs) {
                        var oData = aArgs[2]; // object literal of data for the result
                        if(document.getElementById('".$strName."_id') != null)
                            document.getElementById('".$strName."_id').value=oData[2];
                    };
                    userautocomplete.itemSelectEvent.subscribe(itemSelectHandler);

	                //keep a reference to the autocomplete widget, maybe we want to attach some listeners later
	                KAJONA.admin.".$strJsVarName." = userautocomplete;
	            });
	        </script>
        ";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

   /**
     * Returns a regular text-input field with a file browser button.
     * Use $strRepositoryId to set a specific filemanager repository id
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strRepositoryId
     * @param string $strClass
     * @return string
     * @since 3.3.4
     */
    public function formInputFileSelector($strName, $strTitle = "", $strValue = "", $strRepositoryId = "", $strClass = "inputText") {
        $strOpener = getLinkAdminDialog("filemanager",
										"folderContentFolderviewMode",
										"&form_element=".$strName."&systemid=".$strRepositoryId,
										class_carrier::getInstance()->getObjText()->getText("filebrowser", "system", "admin"),
										class_carrier::getInstance()->getObjText()->getText("filebrowser", "system", "admin"),
										"icon_externalBrowser.gif",
										class_carrier::getInstance()->getObjText()->getText("filebrowser", "system", "admin"));

        return $this->formInputText($strName, $strTitle, $strValue, $strClass, $strOpener);
    }


    /**
     * Returns a regular text-input field with a file browser button.
     * The repository is set to the images-repo by default.
     * In addition, a button to edit the image is added by default.
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     * @return string
     * @since 3.4.0
     */
    public function formInputImageSelector($strName, $strTitle = "", $strValue = "", $strClass = "inputText") {
        $strOpener = getLinkAdminDialog("filemanager",
										"folderContentFolderviewMode",
										"&form_element=".$strName."&systemid="._filemanager_default_imagesrepoid_,
										class_carrier::getInstance()->getObjText()->getText("filebrowser", "system", "admin"),
										class_carrier::getInstance()->getObjText()->getText("filebrowser", "system", "admin"),
										"icon_externalBrowser.gif",
										class_carrier::getInstance()->getObjText()->getText("filebrowser", "system", "admin"));

        $strOpener .= " ".getLinkAdminDialog("filemanager",
                                         "imageDetails",
                                         "imageFile='+document.getElementById('".$strName."').value+'",
                                         class_carrier::getInstance()->getObjText()->getText("cropImage", "filemanager", "admin"),
                                         class_carrier::getInstance()->getObjText()->getText("cropImage", "filemanager", "admin"),
                                         "icon_crop.gif",
                                         class_carrier::getInstance()->getObjText()->getText("cropImage", "filemanager", "admin"),
                                         true, false,
                                         " (function() {
                                             if(document.getElementById('".$strName."').value != '') {
                                                 KAJONA.admin.folderview.dialog.setContentIFrame('".getLinkAdminHref("filemanager", "imageDetails", "imageFile='+document.getElementById('".$strName."').value+'")."');
                                                 KAJONA.admin.folderview.dialog.setTitle('".$strTitle."');
                                                 KAJONA.admin.folderview.dialog.init();
                                             }
                                             return false; })(); return false;");

        return $this->formInputText($strName, $strTitle, $strValue, $strClass, $strOpener);
    }

    /**
     * Returns a text-input field as textarea
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass = inputTextarea
     * @param bool $bitReadonly
     * @return string
     */
    public function formInputTextArea($strName, $strTitle = "", $strValue = "", $strClass = "inputTextarea", $bitReadonly = false) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_textarea");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = $strValue;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["readonly"] = ($bitReadonly ? "readonly=\"readonly\"" : "");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a password text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @return string
     */
    public function formInputPassword($strName, $strTitle = "", $strValue = "", $strClass = "inputText") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_password");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = $strValue;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a button to submit a form
     *
     * @param string $strValue
     * @param string $strName
     * @param string $strEventhandler
     * @param string $strClass
     * @param bool $bitEnabled
     * @return string
     */
    public function formInputSubmit($strValue = "Submit", $strName = "Submit", $strEventhandler = "", $strClass = "inputSubmit", $bitEnabled = true) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_submit");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = $strValue;
        $arrTemplate["eventhandler"] = $strEventhandler;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["disabled"] = $bitEnabled ? "" : "disabled=\"disabled\"";
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a input-file element
     *
     * @param string $strName
     * @param string $strTitle
     * @param bool $bitMultiple
     * @param string $strClass
     * @return string
     */
    public function formInputUpload($strName, $strTitle = "", $strClass = "inputText") {
        $strReturn = "";

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_upload");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;

        $objText = class_carrier::getInstance()->getObjText();
        $arrTemplate["maxSize"] = $objText->getText("max_size", "filemanager", "admin")." ".bytesToString(class_config::getInstance()->getPhpMaxUploadSize());

        $strReturn = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

        return $strReturn;
    }

    /**
     * Returns a input-file element for uploading multiple files with progress bar
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strAllowedFileTypes
     * @param bool $bitMultiple
     * @param bool $bitFallback
     * @return string
     */
    public function formInputUploadFlash($strName, $strTitle, $strAllowedFileTypes, $bitMultiple = false, $bitFallback = false) {

        //upload works with session.use_only_cookies=disabled only. if set to enabled, use the fallback upload
        if(class_carrier::getInstance()->getObjConfig()->getPhpIni("session.use_only_cookies") == "1") {
            $strReturn = $this->formInputUpload($strName, $strTitle);
            $strReturn .= $this->formInputSubmit(class_carrier::getInstance()->getObjText()->getText("upload_multiple_uploadFiles", "filemanager", "admin"));
            return $strReturn;
        }


        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_uploadFlash");
        $arrTemplate = array();
        $arrTemplate["title"] = $strTitle;

        $strAllowedFileTypes = uniStrReplace(array(".", ","), array("*.", ";"), $strAllowedFileTypes);
        if($strAllowedFileTypes == "")
            $strAllowedFileTypes = "*.*";

        $objConfig = class_carrier::getInstance()->getObjConfig();
        $objText = class_carrier::getInstance()->getObjText();

        $arrTemplate["javascript"] = "
            <script type=\"text/javascript\">
                var uploader;

                function initUploader() {
                    YAHOO.widget.Uploader.SWFURL = \""._webpath_."/admin/scripts/yui/uploader/assets/uploader.swf\";
                    uploader = new KAJONA.admin.filemanager.Uploader({
                        \"overlayContainerId\": \"kajonaUploadButtonsOverlay\",
                        \"selectLinkId\": \"kajonaUploadSelectLink\",
                        \"uploadLinkId\": \"kajonaUploadUploadLink\",
                        \"cancelLinkId\": \"kajonaUploadCancelLink\",
                        \"multipleFiles\": ".($bitMultiple ? "true" : "false").",
                        \"allowedFileTypes\": \"".$strAllowedFileTypes."\",
                        \"allowedFileTypesDescription\": \"".$strAllowedFileTypes."\",
                        \"maxFileSize\": ".$objConfig->getPhpMaxUploadSize().",
                        \"warningNotComplete\": \"".$objText->getText("upload_multiple_warningNotComplete", "filemanager", "admin")."\",
                        \"uploadUrl\": \""._webpath_."/xml.php?admin=1&module=filemanager&action=fileUpload&".$objConfig->getPhpIni("session.name")."=".class_session::getInstance()->getSessionId()."\",
                        \"uploadUrlParams\": {\"systemid\" : document.getElementById(\"flashuploadSystemid\").value,
                                              \"folder\" : document.getElementById(\"flashuploadFolder\").value,
                                              \"inputElement\" : \"".$strName."\"}, //create valid input-name element. no array needed!
                        \"uploadInputName\": \"".$strName."\"
                    });
                    uploader.init();
                }
                KAJONA.admin.loader.loadUploaderBase(initUploader);

                jsDialog_0.setTitle('".$objText->getText("upload_multiple_dialogHeader", "filemanager", "admin")."');
            </script>";

        $arrTemplate["upload_fehler_filter"] = $objText->getText("upload_fehler_filter", "filemanager", "admin");
        $arrTemplate["upload_multiple_uploadFiles"] = $objText->getText("upload_multiple_uploadFiles", "filemanager", "admin");
        $arrTemplate["upload_multiple_cancel"] = $objText->getText("upload_multiple_cancel", "filemanager", "admin");
        $arrTemplate["upload_multiple_totalFilesAndSize"] = $objText->getText("upload_multiple_totalFilesAndSize", "filemanager", "admin");
        $arrTemplate["upload_multiple_errorFilesize"] = $objText->getText("upload_multiple_errorFilesize", "filemanager", "admin")." ".bytesToString($objConfig->getPhpMaxUploadSize());
        $arrTemplate["upload_multiple_pleaseWait"] = $objText->getText("upload_multiple_pleaseWait", "filemanager", "admin");

        $arrTemplate["modalDialog"] = $this->jsDialog(0);

        //Fallback code if no or old Flash Player available
        if ($bitFallback) {
            $strFallbackForm = $this->formInputUpload($strName, $strTitle);
            $strFallbackForm .= $this->formInputSubmit($objText->getText("upload_multiple_uploadFiles", "filemanager", "admin"));
            $arrTemplate["fallbackContent"] = $strFallbackForm;
        } else {
            $arrTemplate["fallbackContent"] = $objText->getText("upload_multiple_errorFlash", "filemanager", "admin");
        }

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returning a complete Dropdown
     *
     * @param string $strName
     * @param mixed $arrKeyValues
     * @param string $strTitle
     * @param string $strKeySelected
     * @param string $strClass
     * @param bool $bitEnabled
     * @param string $strAddons
     * @return string
     */
    public function formInputDropdown($strName, $arrKeyValues, $strTitle = "", $strKeySelected = "", $strClass = "inputDropdown", $bitEnabled = true, $strAddons = "") {
        $strOptions = "";
        $strTemplateOptionID = $this->objTemplate->readTemplate("/elements.tpl", "input_dropdown_row");
        $strTemplateOptionSelectedID = $this->objTemplate->readTemplate("/elements.tpl", "input_dropdown_row_selected");
        //Iterating over the array to create the options
        foreach ($arrKeyValues as $strKey => $strValue) {
            $arrTemplate = array();
            $arrTemplate["key"] = $strKey;
            $arrTemplate["value"] = $strValue;
            if((string)$strKey == (string)$strKeySelected)
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionSelectedID);
            else
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionID);
        }

        $arrTemplate = array();
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_dropdown");
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["disabled"] = ($bitEnabled ? "" : "disabled=\"disabled\"");
        $arrTemplate["options"] = $strOptions;
        $arrTemplate["addons"] = $strAddons;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Creates the header needed to open a form-element
     *
     * @param string $strAction
     * @param string $strName
     * @param string $strEncoding
     * @param string $strOnSubmit
     * @return string
     */
    public function formHeader($strAction, $strName = "", $strEncoding = "", $strOnSubmit = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "form_start");
        $arrTemplate = array();
        $arrTemplate["name"] = ($strName != "" ? $strName : "form".generateSystemid());
        $arrTemplate["action"] = $strAction;
        $arrTemplate["enctype"] = $strEncoding;
        $arrTemplate["onsubmit"] = $strOnSubmit;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Creates a foldable wrapper around optional form fields
     *
     * @param string $strContent
     * @param string $strTitle
     * @param bool $bitVisible
     * @return string
     */
    public function formOptionalElementsWrapper($strContent, $strTitle = "", $bitVisible = false) {
        $strId = generateSystemid();
        $strCallbackVisible = "function() {YAHOO.util.Dom.addClass('".$strId."', 'optionalElementsWrapperVisible'); }";
        $strCallbackInvisible = "function() {YAHOO.util.Dom.removeClass('".$strId."', 'optionalElementsWrapperVisible'); }";
        $arrFolder = $this->getLayoutFolder($strContent, "<img src=\""._skinwebpath_."/pics/icon_folderClosed.gif\" alt=\"\" /> ".$strTitle, $bitVisible, $strCallbackVisible, $strCallbackInvisible);
        return "<br /><div id=\"".$strId."\" class=\"optionalElementsWrapper".($bitVisible ? " optionalElementsWrapperVisible" : "")."\">".$this->getFieldset($arrFolder[1], $arrFolder[0])."</div>";
    }

    /**
     * Returns a single TextRow in a form
     *
     * @param string $strText
     * @return string
     */
    public function formTextRow($strText, $strClass = "text") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "text_row_form");
        $arrTemplate = array();
        $arrTemplate["text"] = $strText;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Returns a headline in a form
     *
     * @param string $strText
     * @param string $strClass
     * @return string
     */
    public function formHeadline($strText, $strClass = "heading") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "headline_form");
        $arrTemplate = array();
        $arrTemplate["text"] = $strText;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Returns the tags to close an open form.
     * Includes the hidden fields for a passed pe param and a passed pv param by default.
     *
     * @return string
     */
    public function formClose($bitIncludePeFields = true) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "form_close");
        $strPeFields = "";
        if($bitIncludePeFields) {
            $arrParams = getAllPassedParams();
            if(array_key_exists("pe", $arrParams))
                $strPeFields .= $this->formInputHidden("pe", $arrParams["pe"]);
            if(array_key_exists("pv", $arrParams))
                $strPeFields .= $this->formInputHidden("pv", $arrParams["pv"]);
        }
        return $strPeFields.$this->objTemplate->fillTemplate(array(), $strTemplateID);
    }


/*"*****************************************************************************************************/
// --- LIST-Elements ------------------------------------------------------------------------------------

    /**
     * Returns the htmlcode needed to start a proper list
     *
     * @return string
     */
    public function listHeader() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_header");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * Returns the htmlcode needed to start a proper list, supporting drag n drop to
     * reorder list-items
     *
     * @param string $strListId
     * @param bool $bitOnlySameTable dropping only allowed within the same table or also in other tables
     * @return string
     */
    public function dragableListHeader($strListId, $bitOnlySameTable = false) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dragable_list_header");
        return $this->objTemplate->fillTemplate(array("listid" => $strListId, "sameTable" => $bitOnlySameTable? "true" : "false"), $strTemplateID);
    }

    /**
     * Returns the code to finish the opened list
     *
     * @return string
     */
    public function listFooter() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_footer");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * Returns the code to finish the opened list
     *
     * @param string $strListId
     * @return string
     */
    public function dragableListFooter($strListId) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dragable_list_footer");
        return $this->objTemplate->fillTemplate(array("listid" => $strListId), $strTemplateID);
    }

    /**
     * Returns a row in a list with 2 columns
     *
     * @param string $strName
     * @param string $strActions
     * @param int $intCount, used to determing the class needed
     * @param string $strType to react on special cases
     * @param string $strListitemID id of row-entry, e.g. to use in ajax elements
     * @return string
     */
    public function listRow2($strName, $strActions, $intCount, $strType = "", $strListitemID = "") {
        if($intCount % 2 == 0)
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_row_2_1".$strType);
        else
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_row_2_2".$strType);

        $arrTemplate = array();
        $arrTemplate["title"] = $strName;
        $arrTemplate["actions"] = $strActions;
        $arrTemplate["listitemid"] = $strListitemID;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a row in a list with 2 columns and a leading image
     *
     * @param string $strImage
     * @param string $strName
     * @param string $strActions
     * @param int $intCount, used to determing the class needed
     * @param string $strType to react on special cases
     * @param string $strListitemID id of row-entry, e.g. to use in ajax elements
     * @return string
     */
    public function listRow2Image($strImage, $strName, $strActions, $intCount, $strType = "", $strListitemID = "") {
        if($intCount % 2 == 0)
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_row_2image_1".$strType);
        else
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_row_2image_2".$strType);

        $arrTemplate = array();
        $arrTemplate["image"] = $strImage;
        $arrTemplate["title"] = $strName;
        $arrTemplate["actions"] = $strActions;
        $arrTemplate["listitemid"] = $strListitemID;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a row in a list with 3 columns, with leading image
     *
     * @param string $strName
     * @param string $strCenter
     * @param string $strActions
     * @param string $strImage
     * @param int $intCount, used to determing the class needed
     * @param string $strListitemID id of row-entry, e.g. to use in ajax elements
     * @return string
     */
    public function listRow3($strName, $strCenter, $strActions, $strImage, $intCount, $strListitemID = "") {
        if($intCount % 2 == 0)
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_row_3_1");
        else
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_row_3_2");

        $arrTemplate = array();
        $arrTemplate["image"] = $strImage;
        $arrTemplate["title"] = $strName;
        $arrTemplate["center"] = $strCenter;
        $arrTemplate["actions"] = $strActions;
        $arrTemplate["listitemid"] = $strListitemID;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Returns a table filled with infos
     *
     * @param mixed $arrHeader the first row to name the columns
     * @param mixed $arrValues every entry is one row
     * @return string
     */
    public function dataTable($arrHeader, $arrValues) {
        $strReturn = "";
        $intCounter = "";
        //The Table header & the templates
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_header");
        $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateID);

        $strTemplateHeaderHeaderID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_head_header");
        $strTemplateHeaderContentID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_head");
        $strTemplateHeaderFooterID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_head_footer");
        $strTemplateContentHeaderID1 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_header_1");
        $strTemplateContentHeaderID2 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_header_2");
        $strTemplateContentContentID1 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_1");
        $strTemplateContentContentID2 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_1");
        $strTemplateContentFooterID1 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_footer_2");
        $strTemplateContentFooterID2 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_footer_2");
        //Iterating over the rows

        //Starting with the header, column by column
        if(is_array($arrHeader)) {
            $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateHeaderHeaderID);

            foreach ($arrHeader as $strHeader)
                $strReturn .= $this->objTemplate->fillTemplate(array("value" => $strHeader), $strTemplateHeaderContentID);

            $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateHeaderFooterID);
        }

        //And the content, row by row, column by column
        foreach ($arrValues as $arrValueRow) {
            if(++$intCounter % 2 == 0)
                $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateContentHeaderID1);
            else
                $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateContentHeaderID2);
            foreach($arrValueRow as $strValue) {
                if($intCounter % 2 == 0)
                    $strReturn .= $this->objTemplate->fillTemplate(array("value" => $strValue), $strTemplateContentContentID1);
                else
                    $strReturn .= $this->objTemplate->fillTemplate(array("value" => $strValue), $strTemplateContentContentID2);
            }
            if($intCounter % 2 == 0)
                $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateContentFooterID1);
            else
                $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateContentFooterID2);
        }

        //And the footer
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_footer");
        $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateID);
        return $strReturn;
    }


/*"*****************************************************************************************************/
// --- Action-Elements ----------------------------------------------------------------------------------

    /**
     * Creates a action-Entry in a list
     *
     * @param string $strContent
     * @return string
     */
    public function listButton($strContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_button");
        $arrTemplate = array();
        $arrTemplate["content"] = $strContent;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }




    /**
     * Generates a delete-button. The passed element name and question is shown as a modal dialog
     * when the icon was clicked. So set the link-href-param for the final deletion, otherwise the
     * user has no more chance to delete the record!
     *
     * @param $strElementName
     * @param $strQuestion
     * @param $strLinkHref
     * @return string
     */
    public function listDeleteButton($strElementName, $strQuestion, $strLinkHref) {
        //place it into a standard-js-dialog
        $strDialog = $this->jsDialog(1);

        $strElementName = uniStrReplace(array('\''), array('\\\''), $strElementName);

        $strQuestion = uniStrReplace("%%element_name%%", htmlToString($strElementName, true), $strQuestion);

        //create the list-button and the js code to show the dialog
        $strButton = getLinkAdminManual("href=\"#\" onclick=\"javascript:jsDialog_1.setTitle('".class_carrier::getInstance()->getObjText()->getText("dialog_deleteHeader", "system", "admin")."'); jsDialog_1.setContent('".$strQuestion."', '".class_carrier::getInstance()->getObjText()->getText("dialog_deleteButton", "system", "admin")."',  '".$strLinkHref."'); jsDialog_1.init(); return false;\"",
                                         "",
                                         class_carrier::getInstance()->getObjText()->getText("commons_delete", "system", "admin"),
                                         "icon_ton.gif" );

        return $this->listButton($strButton).$strDialog;
    }

    /**
     * Generates a button allowing to change the status of the record passed.
     * Therefore an ajax-method is called.
     *
     * @param string $strSystemid
     * @return string
     */
    public function listStatusButton($strSystemid) {
        //read the current status
        $strButton = "";
        $objRecord = new class_modul_system_common($strSystemid);
        $strImage = "";
        $strNewImage = "";
        $strText = "";
        if($objRecord->getStatus() == 1) {
            $strImage = "icon_enabled.gif";
            $strNewImage = "icon_disabled.gif";
            $strText = class_carrier::getInstance()->getObjText()->getText("status_active", "system", "admin");
        }
        else {
            $strImage = "icon_disabled.gif";
            $strNewImage = "icon_enabled.gif";
            $strText = class_carrier::getInstance()->getObjText()->getText("status_inactive", "system", "admin");
        }

        $strJavascript = "";

        //output texts and image paths only once
        if(class_carrier::getInstance()->getObjSession()->getSession("statusButton", class_session::$intScopeRequest) === false) {
            $strJavascript .= "<script type=\"text/javascript\">
                var strActiveText = '".class_carrier::getInstance()->getObjText()->getText("status_active", "system", "admin")."';
                var strInActiveText = '".class_carrier::getInstance()->getObjText()->getText("status_inactive", "system", "admin")."';
                var strActiveImageSrc = '"._skinwebpath_."/pics/icon_enabled.gif';
                var strInActiveImageSrc = '"._skinwebpath_."/pics/icon_disabled.gif';

                KAJONA.admin.loader.loadAjaxBase();
            </script>";
            class_carrier::getInstance()->getObjSession()->setSession("statusButton", "true", class_session::$intScopeRequest);
        }

        $strButton = getLinkAdminManual("href=\"javascript:KAJONA.admin.ajax.setSystemStatus('".$strSystemid."');\"", "", $strText, $strImage, "statusImage_".$strSystemid, "statusLink_".$strSystemid);

        return $this->listButton($strButton).$strJavascript;
    }

/*"*****************************************************************************************************/
// --- Misc-Elements ------------------------------------------------------------------------------------

    /**
     * Returns a warning box, e.g. shown before deleting a record
     *
     * @param string $strContent
     * @param string $strClass
     * @return string
     */
    public function warningBox($strContent, $strClass = "warnbox") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "warning_box");
        $arrTemplate = array();
        $arrTemplate["content"] = $strContent;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a single TextRow
     *
     * @param string $strText
     * @return string
     */
    public function getTextRow($strText, $strClass = "text") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "text_row");
        $arrTemplate = array();
        $arrTemplate["text"] = $strText;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Creates the mechanism to fold parts of the site / make them visible or invisible
     *
     * @param string $strContent
     * @param string $strLinkText The text / content,
     * @param bool $bitVisible
     * @param string $strCallbackVisible JS function
     * @param string $strCallbackInvisible JS function
     * @return mixed 0: The html-layout code
     *               1: The link to fold / unfold
     */
    public function getLayoutFolder($strContent, $strLinkText, $bitVisible = false, $strCallbackVisible = "", $strCallbackInvisible = "") {
        $arrReturn = array();
        $strID = str_replace(array(" ", "."), array("", ""), microtime());
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "layout_folder");
        $arrTemplate = array();
        $arrTemplate["id"] = $strID;
        $arrTemplate["content"] = $strContent;
        $arrTemplate["display"] = ($bitVisible ? "block" : "none");
        $arrReturn[0] = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
        $arrReturn[1] = "<a href=\"javascript:KAJONA.util.fold('".$strID."', ". ($strCallbackVisible != "" ? $strCallbackVisible : "null") .", ". ($strCallbackInvisible != "" ? $strCallbackInvisible : "null") .");\">".$strLinkText."</a>";
        return $arrReturn;
    }

    /**
     * Creates the mechanism to fold parts of the site / make them vivsible or invisible
     *
     * @param string $strContent
     * @param string $strLinkText Mouseovertext
     * @param string $strImageVisible clickable
     * @param string $strImageInvisible clickable
     * @param bool $bitVisible
     * @return string
     */
    public function getLayoutFolderPic($strContent, $strLinkText = "", $strImageVisible = "icon_folderOpen.gif", $strImageInvisible = "icon_folderClosed.gif", $bitVisible = true) {
        $strID = str_replace(array(" ", "."), array("", ""), microtime());
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "layout_folder_pic");
        $arrTemplate = array();
        $arrTemplate["id"] = $strID;
        $arrTemplate["content"] = $strContent;
        $arrTemplate["display"] = ($bitVisible ? "block" : "none");
        $arrTemplate["link"] = "<a href=\"javascript:KAJONA.util.foldImage('".$strID."', '".$strID."_img', '"._skinwebpath_."/pics/".$strImageVisible."', '"._skinwebpath_."/pics/".$strImageInvisible."')\" title=\"".$strLinkText."\">".getImageAdmin(($bitVisible ? $strImageVisible : $strImageInvisible), $strLinkText, false, $strID."_img")."</a>";
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a infobox about the page being edited
     *
     * @param mixed $arrContent
     * @return string
     */
    public function getPageInfobox($arrContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "page_infobox");
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Returns a infox used by the filemanager
     *
     * @param mixed $arrContent
     * @return string
     */
    public function getFilemanagerInfoBox($arrContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "filemanager_infobox");
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Creates the page to view & manipulate image.
     *
     * @since 3.2
     * @replace class_toolkit_admin::getFileDetails()
     * @param array $arrContent
     * @return string
     */
    public function getFilemanagerImageDetails($arrContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "folderview_image_details");
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }


    /**
     * Creates a fieldset to structure elements
     *
     * @param string $strTitle
     * @param string $strContent
     * @param string $strClass
     * @return string
     */
    public function getFieldset($strTitle, $strContent, $strClass = "fieldset") {
        //remove old placeholder from content
        $this->objTemplate->setTemplate($strContent);
        $this->objTemplate->deletePlaceholder();
        $strContent = $this->objTemplate->getTemplate();
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "misc_fieldset");
        $arrContent = array();
        $arrContent["title"] = $strTitle;
        $arrContent["content"] = $strContent;
        $arrContent["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Container for graphs, e.g. used by stats.
     *
     * @param string $strImgSrc
     * @return string
     */
    public function getGraphContainer($strImgSrc) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "graph_container");
        $arrContent = array();
        $arrContent["imgsrc"] = $strImgSrc;
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Renders the login-status and corresponding links
     * @param array $arrElements
     * @return string
     * @since 3.4.0
     */
    public function getLoginStatus($arrElements) {
        //Loading a small login-form
		$strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "logout_form");
		$strReturn = $this->objTemplate->fillTemplate($arrElements, $strTemplateID);
		return $strReturn;
    }

/*"*****************************************************************************************************/
// --- Navigation-Elements ------------------------------------------------------------------------------

    /**
     * Generates the module-navigation in the admin-area
     *
     * @param mixed $arrModules
     * @param string $strCurrent
     * @return string
     */
    public function getAdminModuleNavi($arrModules, $strCurrent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "modulenavi_main");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "modulenavi_main_row");
        $strTemplateRowHiddenID = $this->objTemplate->readTemplate("/elements.tpl", "modulenavi_main_row_hidden");
        $strTemplateRowIDFirst = $this->objTemplate->readTemplate("/elements.tpl", "modulenavi_main_row_first");
        $strTemplateRowIDLast = $this->objTemplate->readTemplate("/elements.tpl", "modulenavi_main_row_last");
        $strTemplateRowSelectedID = $this->objTemplate->readTemplate("/elements.tpl", "modulenavi_main_row_selected");
        $strTemplateRowSelectedIDFirst = $this->objTemplate->readTemplate("/elements.tpl", "modulenavi_main_row_selected_first");
        $strTemplateRowSelectedIDLast = $this->objTemplate->readTemplate("/elements.tpl", "modulenavi_main_row_selected_last");
        $strRows = "";
        $strCurrent = uniSubstr($strCurrent, uniStrpos($strCurrent, "_")+1);
        $intCount = 1;
        $intMax = count($arrModules);
        foreach ($arrModules as $arrOneModule) {
            if($strCurrent == $arrOneModule["rawName"]) {
                if($intCount == 1)
                    $strRows .= $this->objTemplate->fillTemplate($arrOneModule, $strTemplateRowSelectedIDFirst);
                elseif ($intCount == $intMax)
                    $strRows .= $this->objTemplate->fillTemplate($arrOneModule, $strTemplateRowSelectedIDLast);
                else
                    $strRows .= $this->objTemplate->fillTemplate($arrOneModule, $strTemplateRowSelectedID);
            }
            else {
                if($intCount == 1)
                    $strRows .= $this->objTemplate->fillTemplate($arrOneModule, $strTemplateRowIDFirst);
                elseif ($intCount == $intMax)
                    $strRows .= $this->objTemplate->fillTemplate($arrOneModule, $strTemplateRowIDLast);
                else {
                    //allow to hide modules if too much given
                    if($intCount >= 8) {
                        $strTemp = $this->objTemplate->fillTemplate($arrOneModule, $strTemplateRowHiddenID);
                        if($strTemp == "")
                            $strRows .= $this->objTemplate->fillTemplate($arrOneModule, $strTemplateRowID);
                        else
                            $strRows .= $strTemp;
                    }
                    else
                        $strRows .= $this->objTemplate->fillTemplate($arrOneModule, $strTemplateRowID);
                }

            }

            $intCount++;
        }
        return $this->objTemplate->fillTemplate(array("rows" => $strRows), $strTemplateID);
    }

    /**
     * Generates the moduleaction-navigation in the admin-area
     *
     * @param mixed $arrModules
     * @return string
     */
    public function getAdminModuleActionNavi($arrActions) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "moduleactionnavi_main");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "moduleactionnavi_row");
        $strTemplateSpacerID = $this->objTemplate->readTemplate("/elements.tpl", "moduleactionnavi_spacer");
        $strRows = "";
        foreach ($arrActions as $strOneAction) {
            //spacer or a regular navigationpoint given?
            if($strOneAction == "") {
                $strRows .= $this->objTemplate->fillTemplate(array(), $strTemplateSpacerID);
            }
            else {
                $arrRow = array();
                $arrRow = splitUpLink($strOneAction);
                $strRows .= $this->objTemplate->fillTemplate($arrRow, $strTemplateRowID);
            }
        }
        return $this->objTemplate->fillTemplate(array("rows" => $strRows), $strTemplateID);
    }

/*"*****************************************************************************************************/
// --- Path Navigation ----------------------------------------------------------------------------------

    /**
     * Generates the layout for a small navigation
     *
     * @param mixed $arrEntries
     * @return string
     */
    public function getPathNavigation($arrEntries) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "path_container");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "path_entry");
        $strRows = "";
        foreach ($arrEntries as $strOneEntry) {
            $strRows .= $this->objTemplate->fillTemplate(array("pathlink" => $strOneEntry), $strTemplateRowID);
        }
        return $this->objTemplate->fillTemplate(array("pathnavi" => $strRows), $strTemplateID);

    }

/*"*****************************************************************************************************/
// --- Content Toolbar ----------------------------------------------------------------------------------

    /**
     * A content toolbar can be used to group a subset of actions linking different views
     *
     * @param mixed $arrEntries
     * @param int $intActiveEntry Array-counting, so first element is 0, last is array-length - 1
     * @return string
     */
    public function getContentToolbar($arrEntries, $intActiveEntry = -1) {
        $strTemplateWrapperID = $this->objTemplate->readTemplate("/elements.tpl", "contentToolbar_wrapper");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contentToolbar_entry");
        $strTemplateActiveEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contentToolbar_entry_active");
        $strRows = "";
        foreach ($arrEntries as $intI => $strOneEntry) {
            if($intI == $intActiveEntry)
                $strRows .= $this->objTemplate->fillTemplate(array("entry" => $strOneEntry), $strTemplateActiveEntryID);
            else
                $strRows .= $this->objTemplate->fillTemplate(array("entry" => $strOneEntry), $strTemplateEntryID);
        }
        return $this->objTemplate->fillTemplate(array("entries" => $strRows), $strTemplateWrapperID);

    }

/*"*****************************************************************************************************/
// --- Validation Errors --------------------------------------------------------------------------------

    /**
     * Generates a list of errors found by the form-validation
     *
     * @param class_admin $objCalling
     * @param string $strTargetAction
     * @return string
     */
    public function getValidationErrors($objCalling, $strTargetAction = null) {
        $strRendercode = "";
        //render mandatory fields?
        if($strTargetAction != null && is_callable(array($objCalling, "getRequiredFields")) ) {
            $strTempAction = $objCalling->getAction();
            $objCalling->setAction($strTargetAction);
            $arrFields = $objCalling->getRequiredFields();

            $objCalling->setAction($strTempAction);

            if(count($arrFields) > 0 ) {

                $strRendercode .= "<script type=\"text/javascript\">YAHOO.util.Event.onDOMReady(function () {
                        KAJONA.admin.forms.renderMandatoryFields([";
                foreach($arrFields as $strName => $strType) {
                    $strRendercode .= "[ '".$strName."', '".$strType."' ], ";
                }
                $strRendercode .= " [] ]); });</script>";
            }
        }

        $arrErrors = $objCalling->getValidationErrors();
        if(count($arrErrors) == 0)
            return $strRendercode;

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "error_container");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "error_row");
        $strRows = "";
        foreach ($arrErrors as $strOneError) {
            $strRows .= $this->objTemplate->fillTemplate(array("field_errortext" => $strOneError), $strTemplateRowID);
        }
        $arrTemplate = array();
        $arrTemplate["errorrows"] = $strRows;
        $arrTemplate["errorintro"] = $objCalling->getText("errorintro");
        if($arrTemplate["errorintro"] == "!errorintro!")
            $arrTemplate["errorintro"] = $objCalling->getText("errorintro", "system");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID).$strRendercode;
    }


/*"*****************************************************************************************************/
// --- Pre-formatted ------------------------------------------------------------------------------------


    /**
     * Returns a simple <pre>-Element to display pre-formatted text such as logfiles
     *
     * @param array $arrLines
     * @param int $nrRows number of rows to display
     * @return string
     */
    public function getPreformatted($arrLines, $nrRows = 0) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "preformatted");
        $strRows = "";
        $intI = 0;
        foreach ($arrLines as $strOneLine) {
            if($nrRows != 0 && $intI++ > $nrRows)
                break;
            $strOneLine = str_replace(array("<pre>", "</pre>", "\n"), array(" ", " ", "\r\n"), $strOneLine);
            $strRows .= htmlToString($strOneLine, true);
        }
        return $this->objTemplate->fillTemplate(array("pretext" => $strRows), $strTemplateID);
    }

/*"*****************************************************************************************************/
// --- Language handling --------------------------------------------------------------------------------

    /**
     * Creates the sourrounding code of a language switch, places the buttons
     *
     * @param string $strLanguageButtons
     * @return string
     */
    public function getLanguageSwitch($strLanguageButtons) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "language_switch");
        $arrTemplate = array();
        $arrTemplate["languagebuttons"] = $strLanguageButtons;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Creates the code for one button for a specified language, part of a language switch
     *
     * @param string $strLanguage
     * @param string $strLanguageName  The full name of the language
     * @param bool $bitActive
     * @return string
     */
    public function getLanguageButton($strLanguage, $strLanguageName, $bitActive = false) {
        //active language?
        if($bitActive)
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "language_switch_button_active");
        else
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "language_switch_button");
        $arrTemplate = array();
        $arrTemplate["language"] = $strLanguage;
        $arrTemplate["languageName"] = $strLanguageName;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


/*"*****************************************************************************************************/
// --- Pageview mechanism ------------------------------------------------------------------------------

    /**
     * Creates a pageview
     *
     * @param array $arrData
     * @param int $intCurrentpage
     * @param string $strModule
     * @param string $strAction
     * @param string $strLinkAdd
     * @param int $intElementPerPage
     * @return mixed a one-dimensional array: ["elements"] and ["pageview"]
     *
     * @deprecated migrate to getSimplePageview instead!
     */
    public function getPageview($arrData, $intCurrentpage, $strModule, $strAction, $strLinkAdd = "", $intElementPerPage = 15) {
        $arrReturn = array();

        if($intCurrentpage <= 0)
            $intCurrentpage = 1;

        if($intElementPerPage <= 0)
            $intElementPerPage = 1;

        $objArrayIterator = new class_array_iterator($arrData);
        $objArrayIterator->setIntElementsPerPage($intElementPerPage);
        $intNrOfPages = $objArrayIterator->getNrOfPages();
        $intNrOfElements = $objArrayIterator->getNumberOfElements();

        $arrReturn["elements"] = $objArrayIterator->getElementsOnPage($intCurrentpage);
        //read templates
        $strTemplateBodyID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_body");
        $strTemplateForwardID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_link_forward");
        $strTemplateBackwardID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_link_backward");
        $strTemplateListID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_page_list");
        $strTemplateListItemActiveID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_list_item_active");
        $strTemplateListItemID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_list_item");
        //build layout
        $arrTemplate = array();

        $strListItems = "";

        //just load the current +-4 pages and the first/last +-2
        $intCounter2 = 1;
        for($intI = 1; $intI <= $intNrOfPages; $intI++) {
            $bitDisplay = false;
            if($intCounter2 <= 2) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intNrOfPages-1)) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intCurrentpage-2) && $intCounter2 <= ($intCurrentpage+2)) {
                $bitDisplay = true;
            }


            if($bitDisplay) {
                $arrLinkTemplate = array();
                $arrLinkTemplate["href"] = getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".$intI);
                $arrLinkTemplate["pageNr"] = $intI;

                if($intI == $intCurrentpage)
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemActiveID);
                else
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemID);
            }
            $intCounter2++;
        }
        $arrTemplate["pageList"] = $this->objTemplate->fillTemplate(array("pageListItems" => $strListItems), $strTemplateListID);
        $arrTemplate["nrOfElementsText"] = class_carrier::getInstance()->getObjText()->getText("pageview_total", "system", "admin");
        $arrTemplate["nrOfElements"] = $intNrOfElements;
        if($intCurrentpage < $intNrOfPages)
            $arrTemplate["linkForward"] = $this->objTemplate->fillTemplate(array("linkText" => class_carrier::getInstance()->getObjText()->getText("pageview_forward", "system", "admin"),
                                                                                 "href" => getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage+1))), $strTemplateForwardID);
        if($intCurrentpage > 1)
            $arrTemplate["linkBackward"] = $this->objTemplate->fillTemplate(array("linkText" => class_carrier::getInstance()->getObjText()->getText("commons_back", "commons", "admin"),
                                                                                  "href" => getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage-1))), $strTemplateBackwardID);


        $arrReturn["pageview"] = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateBodyID);
        return $arrReturn;
    }

    /**
     * Creates a pageview
     *
     * @param class_array_section_iterator $objArraySectionIterator
     * @param string $strModule
     * @param string $strAction
     * @param string $strLinkAdd
     * @return mixed a two-dimensional array: ["elements"] and ["pageview"]
     * @since 3.3.0
     */
    public function getSimplePageview($objArraySectionIterator, $strModule, $strAction, $strLinkAdd = "") {
        $arrReturn = array();

        $intCurrentpage = $objArraySectionIterator->getPageNumber();
        $intNrOfPages = $objArraySectionIterator->getNrOfPages();
        $intNrOfElements = $objArraySectionIterator->getNumberOfElements();


        $arrReturn["elements"] = $objArraySectionIterator->getArrayExtended(true);

        //read templates
        $strTemplateBodyID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_body");
        $strTemplateForwardID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_link_forward");
        $strTemplateBackwardID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_link_backward");
        $strTemplateListID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_page_list");
        $strTemplateListItemActiveID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_list_item_active");
        $strTemplateListItemID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_list_item");
        //build layout
        $arrTemplate = array();

        $strListItems = "";

        //just load the current +-4 pages and the first/last +-2
        $intCounter2 = 1;
        for($intI = 1; $intI <= $intNrOfPages; $intI++) {
            $bitDisplay = false;
            if($intCounter2 <= 2) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intNrOfPages-1)) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intCurrentpage-2) && $intCounter2 <= ($intCurrentpage+2)) {
                $bitDisplay = true;
            }


            if($bitDisplay) {
                $arrLinkTemplate = array();
                $arrLinkTemplate["href"] = getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".$intI);
                $arrLinkTemplate["pageNr"] = $intI;

                if($intI == $intCurrentpage)
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemActiveID);
                else
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemID);
            }
            $intCounter2++;
        }
        $arrTemplate["pageList"] = $this->objTemplate->fillTemplate(array("pageListItems" => $strListItems), $strTemplateListID);
        $arrTemplate["nrOfElementsText"] = class_carrier::getInstance()->getObjText()->getText("pageview_total", "system", "admin");
        $arrTemplate["nrOfElements"] = $intNrOfElements;
        if($intCurrentpage < $intNrOfPages)
            $arrTemplate["linkForward"] = $this->objTemplate->fillTemplate(array("linkText" => class_carrier::getInstance()->getObjText()->getText("pageview_forward", "system", "admin"),
                                                                                 "href" => getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage+1))), $strTemplateForwardID);
        if($intCurrentpage > 1)
            $arrTemplate["linkBackward"] = $this->objTemplate->fillTemplate(array("linkText" => class_carrier::getInstance()->getObjText()->getText("commons_back", "commons", "admin"),
                                                                                  "href" => getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage-1))), $strTemplateBackwardID);


        $arrReturn["pageview"] = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateBodyID);
        return $arrReturn;
    }


/*"*****************************************************************************************************/
// --- Adminwidget / Dashboard --------------------------------------------------------------------------


    public function getMainDashboard($arrColumn) {
        $strReturn = "<table class=\"dashBoard\"><tr>";
        foreach ($arrColumn as $strOneColumn)
            $strReturn .= "<td>".$strOneColumn."</td>";
        $strReturn .= "</tr></table>";
        return $strReturn;
    }

    /**
     * Generates the header for a column on the dashboard.
     * Inits the ajax-componentes for this list
     *
     * @param string $strColumnId
     * @return string
     */
    public function getDashboardColumnHeader($strColumnId) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dashboard_column_header");
        return $this->objTemplate->fillTemplate(array("column_id" => $strColumnId), $strTemplateID);
    }

    /**
     * The footer of a dashboard column.
     *
     * @return string
     */
    public function getDashboardColumnFooter() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dashboard_column_footer");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * The widget-enclose is the code-fragment to be built around the widget itself.
     * Used to handle the widget on the current column.
     *
     * @param string $strDashboardEntryId
     * @param string $strWidgetContent
     * @return string
     */
    public function getDashboardWidgetEncloser($strDashboardEntryId, $strWidgetContent) {
         $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dashboard_encloser");
        $arrTemplate = array();
        $arrTemplate["entryid"] = $strDashboardEntryId;
        $arrTemplate["content"] = $strWidgetContent;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Builds the widget out of its main components.
     *
     * @param string $strSystemid
     * @param string $strName
     * @param string $strContent
     * @param string $strEditLink
     * @param string $strDeleteLink
     * @return string
     */
    public function getAdminwidget($strSystemid, $strName, $strContent, $strEditLink = "", $strDeleteLink = "", $strLayoutSection = "adminwidget_widget") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", $strLayoutSection);
        $arrTemplate = array();
        $arrTemplate["widget_name"] = $strName;
        $arrTemplate["widget_content"] = $strContent;
        $arrTemplate["widget_id"] = $strSystemid;
        $arrTemplate["widget_edit"] = $strEditLink;
        $arrTemplate["widget_delete"] = $strDeleteLink;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Generates a text-row in a widget
     *
     * @param string $strText
     * @return string
     */
    public function adminwidgetText($strText) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "adminwidget_text");
        return $this->objTemplate->fillTemplate(array("text" => $strText), $strTemplateID);
    }

    /**
     * Generate a separator / divider in a widget
     *
     * @return string
     */
    public function adminwidgetSeparator() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "adminwidget_separator");
        return $this->objTemplate->fillTemplate(array(""), $strTemplateID);
    }

//--- modal dialog --------------------------------------------------------------------------------------

    /**
     * Creates a modal dialog on the page. By default, the dialog is hidden, so has to be set visible.
     * The type-param decides what template is used for the dialog-layout. The name of the dialog is built via jsDialog_$intTypeNr.
     * Set the contents via js-calls.
     *
     * @param int $intDialogType (0 = regular modal dialog, 1 = confirmation dialog, 2 = rawDialog, 3 = loadingDialog)
     * @return string
     */
    public function jsDialog($intDialogType) {
        $strContent = "";
        //create the html-part
        $arrTemplate = array();
        $strContainerId = generateSystemid();
        $arrTemplate["dialog_id"] = $strContainerId;

        $strTemplateId = null;
        if($intDialogType == 0 && class_carrier::getInstance()->getObjSession()->getSession("jsDialog_".$intDialogType, class_session::$intScopeRequest) === false) {
            $strTemplateId = $this->objTemplate->readTemplate("/elements.tpl", "dialogContainer");
            class_carrier::getInstance()->getObjSession()->setSession("jsDialog_".$intDialogType, "true",  class_session::$intScopeRequest);
        }
        else if($intDialogType == 1 && class_carrier::getInstance()->getObjSession()->getSession("jsDialog_".$intDialogType, class_session::$intScopeRequest) === false) {
            $arrTemplate["dialog_cancelButton"] = class_carrier::getInstance()->getObjText()->getText("dialog_cancelButton", "system", "admin");

            $strTemplateId = $this->objTemplate->readTemplate("/elements.tpl", "dialogConfirmationContainer");
            class_carrier::getInstance()->getObjSession()->setSession("jsDialog_".$intDialogType, "true",  class_session::$intScopeRequest);
        }
        else if($intDialogType == 2 && class_carrier::getInstance()->getObjSession()->getSession("jsDialog_".$intDialogType, class_session::$intScopeRequest) === false) {
            $strTemplateId = $this->objTemplate->readTemplate("/elements.tpl", "dialogRawContainer");
            class_carrier::getInstance()->getObjSession()->setSession("jsDialog_".$intDialogType, "true",  class_session::$intScopeRequest);
        }
        else if($intDialogType == 3 && class_carrier::getInstance()->getObjSession()->getSession("jsDialog_".$intDialogType, class_session::$intScopeRequest) === false) {
            $arrTemplate["dialog_title"] = class_carrier::getInstance()->getObjText()->getText("dialog_loadingHeader", "system", "admin");
            $strTemplateId = $this->objTemplate->readTemplate("/elements.tpl", "dialogLoadingContainer");
            class_carrier::getInstance()->getObjSession()->setSession("jsDialog_".$intDialogType, "true",  class_session::$intScopeRequest);
        }

        if($strTemplateId != null) {
            $strContent .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateId);

            //and create the java-script
            $strContent .="<script type=\"text/javascript\">
                KAJONA.admin.loader.loadDialogBase();
                var jsDialog_".$intDialogType." = new KAJONA.admin.ModalDialog('".$strContainerId."', ".$intDialogType.");
            </script>";
        }

        return $strContent;
    }


//--- misc ----------------------------------------------------------------------------------------------

    /**
     * Sets the users browser focus to the element with the given id
     *
     * @param string $strElementId
     * @return string
     */
    public function setBrowserFocus($strElementId) {
        $strReturn = "
            <script type=\"text/javascript\">
                KAJONA.util.setBrowserFocus(\"".$strElementId."\");
            </script>";
        return $strReturn;
    }

    /**
     * Create a tree-view UI-element. Please not, that currently it's only possible to use
     * one tree-view per page.
     * The nodes are loaded via AJAX by calling the method passed as the first arg.
     * The optional third param is an ordered list of systemid identifying the nodes to expand initially.
     *
     * @param string $strLoadNodeDataFunction
     * @param string $strRootNodeSystemid
     * @param array $arrNodesToExpand
     * @param string $strSideContent
     * @param string $strRootNodeTitle
     * @param string $strRootNodeLink
     * @return string
     */
    public function getTreeview($strLoadNodeDataFunction, $strRootNodeSystemid, $arrNodesToExpand = array(), $strSideContent = "", $strRootNodeTitle = " ", $strRootNodeLink = "") {
        $arrTemplate = array();
        $arrTemplate["rootNodeSystemid"] = $strRootNodeSystemid;
        $arrTemplate["loadNodeDataFunction"] = $strLoadNodeDataFunction;
        $arrTemplate["sideContent"] = $strSideContent;
        $arrTemplate["rootNodeTitle"] = $strRootNodeTitle;
        $arrTemplate["rootNodeLink"] = $strRootNodeLink;
        $arrTemplate["treeviewExpanders"] = "";
        for($intI = 0; $intI < count($arrNodesToExpand); $intI++) {
            $arrTemplate["treeviewExpanders"] .= "\"".$arrNodesToExpand[$intI]."\"";
            if($intI < count($arrNodesToExpand)-1)
                $arrTemplate["treeviewExpanders"] .= ",";
        }
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "treeview");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Renderes the quickhelp-button and the quickhelp-text passed
     *
     * @param string $strText
     * @return string
     */
    public function getQuickhelp($strText) {
        $strReturn = "";
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "quickhelp");
        $arrTemplate = array();
        $arrTemplate["title"] = class_carrier::getInstance()->getObjText()->getText("quickhelp_title", "system", "admin");
        $arrTemplate["text"] = $strText;
        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

        //and the button
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "quickhelp_button");
        $arrTemplate = array();
        $arrTemplate["text"] = class_carrier::getInstance()->getObjText()->getText("quickhelp_title", "system", "admin");
        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

        return $strReturn;
    }

    /**
     * Generates the wrapper required to render the list of tags.
     *
     * @param string $strWrapperid
     * @param string $strTargetsystemid
     * @param string $strAttribute
     * @return string
     */
    public function getTaglistWrapper($strWrapperid, $strTargetsystemid, $strAttribute) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tags_wrapper");
        $arrTemplate = array();
        $arrTemplate["wrapperId"] = $strWrapperid;
        $arrTemplate["targetSystemid"] = $strTargetsystemid;
        $arrTemplate["attribute"] = $strAttribute;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Renders a single tag (including the options to remove the tag again)
     *
     * @param string $strTagname
     * @param string $strTagId
     * @param string $strTargetid
     * @param string $strAttribute
     * @return string
     */
    public function getTagEntry($strTagname, $strTagId, $strTargetid, $strAttribute) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tags_tag");
        $arrTemplate = array();
        $arrTemplate["tagname"] = $strTagname;
        $arrTemplate["strTagId"] = $strTagId;
        $arrTemplate["strTargetSystemid"] = $strTargetid;
        $arrTemplate["strAttribute"] = $strAttribute;
        $arrTemplate["deleteIcon"] = getImageAdmin("icon_ton.gif", class_carrier::getInstance()->getObjText()->getText("commons_delete", "tags", "admin"));
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Returns a regular text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strClass
     * @return string
     */
    public function formInputTagSelector($strName, $strTitle = "", $strClass = "inputText") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_tagselector");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;

        $arrTemplate["ajaxScript"] = "
	        <script type=\"text/javascript\">
	            KAJONA.admin.loader.loadAutocompleteBase(function () {
	                var pageDataSource = new YAHOO.util.XHRDataSource(KAJONA_WEBPATH+\"/xml.php\");
	                pageDataSource.responseType = YAHOO.util.XHRDataSource.TYPE_XML;
	                pageDataSource.responseSchema = {
	                    resultNode : \"tag\",
	                    fields : [\"name\"]
	                };

	                var pageautocomplete = new YAHOO.widget.AutoComplete(\"".$strName."\", \"".$strName."_container\", pageDataSource, {
	                    queryMatchCase: false,
	                    allowBrowserAutocomplete: false,
	                    useShadow: false,
                        delimChar: [\",\"]
	                });
	                pageautocomplete.generateRequest = function(sQuery) {
	                    return \"?admin=1&module=tags&action=getTagsByFilter&filter=\" + sQuery ;
	                };
	            });
	        </script>
        ";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    public function getAspectChooser($strLastModule, $strLastAction, $strLastSystemid) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "aspect_chooser");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "aspect_chooser_entry");

        $arrTemplate = array();
        $arrTemplate["options"] = "";

        //process rows
        $strCurrentId = class_modul_system_aspect::getCurrentAspectId();
        $arrAspects = class_modul_system_aspect::getAllAspects(true);

        $intNrOfAspects = 0;
        foreach($arrAspects as $objSingleAspect) {
            if($objSingleAspect->rightView()) {
                $arrSubtemplate = array();
                //start on dashboard since the current module may not be visible in another aspect
                $arrSubtemplate["value"] = getLinkAdminHref("dashboard", "", "&aspect=".$objSingleAspect->getSystemid());
                $arrSubtemplate["name"] = $objSingleAspect->getStrName();
                $arrSubtemplate["selected"] = $strCurrentId == $objSingleAspect->getSystemid() ? "selected=\"selected\"" : "";

                $arrTemplate["options"] .= $this->objTemplate->fillTemplate($arrSubtemplate, $strTemplateRowID);
                $intNrOfAspects++;
            }
        }

        if($arrTemplate["options"] == "" || $intNrOfAspects < 2)
            return "";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Creates a tooltip shown on hovering the passed text.
     * If both are the same, text and tooltip, only the plain text is returned.
     *
     * @param string $strText
     * @param string $strTooltip
     * @return string
     * @since 3.4.0
     */
    public function getTooltipText($strText, $strTooltip) {
        if($strText == $strTooltip)
            return $strText;

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tooltip_text");
        return $this->objTemplate->fillTemplate(array("text" => $strText, "tooltip" => $strTooltip), $strTemplateID);
    }

// --- Calendar Fields ----------------------------------------------------------------------------------

    /**
     * Renders a legend below the current calendar in order to illustrate the different event-types.
     *
     * @param array $arrEntries
     * @return string
     */
    public function getCalendarLegend($arrEntries) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_legend");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_legend_entry");

        $strEntries = "";
        foreach($arrEntries as $strName => $strClass)
            $strEntries .= $this->objTemplate->fillTemplate(array("name" => $strName, "class" => $strClass), $strTemplateEntryID);

        return $this->objTemplate->fillTemplate(array("entries" => $strEntries), $strTemplateID);
    }

    /**
     * Renders a legend below the current calendar in order to illustrate the different event-types.
     *
     * @param array $arrEntries
     * @return string
     */
    public function getCalendarFilter($arrEntries) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_filter");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_filter_entry");

        $strEntries = "";
        foreach($arrEntries as $strId => $strName) {
            $strChecked = class_carrier::getInstance()->getObjSession()->getSession($strId) == "disabled" ? "" : "checked";
            $strEntries .= $this->objTemplate->fillTemplate(array("filterid" => $strId, "filtername" => $strName, "checked" => $strChecked), $strTemplateEntryID);
        }

        return $this->objTemplate->fillTemplate(array("entries" => $strEntries, "action" => getLinkAdminHref("dashboard", "calendar")), $strTemplateID);
    }

    /**
     * Creates a pager for the calendar, used to switch the current month.
     *
     * @param string $strBackwards
     * @param string $strCenter
     * @param string $strForwards
     * @return string
     * @since 3.4
     */
    public function getCalendarPager($strBackwards, $strCenter, $strForwards) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_pager");
        return $this->objTemplate->fillTemplate(array("backwards" => $strBackwards, "forwards" => $strForwards, "center" => $strCenter), $strTemplateID);
    }

    /**
     * Renders a container used to place the calender via ajax into.
     *
     * @param string $strContainerId
     * @return string
     * @since 3.4
     */
    public function getCalendarContainer($strContainerId) {
       $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_container");
        return $this->objTemplate->fillTemplate(array("containerid" => $strContainerId), $strTemplateID);
    }

    /**
     * Creates the wrapper to embedd the calendar.
     *
     * @param string $strContent
     * @return string
     * @since 3.4
     */
    public function getCalendarWrapper($strContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_wrapper");
        return $this->objTemplate->fillTemplate(array("content" => $strContent), $strTemplateID);
    }

    /**
     * Renders the header-row of the calendar. In general those are the days.
     *
     * @param array $arrHeader
     * @return string
     * @since 3.4
     */
    public function getCalendarHeaderRow($arrHeader) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_header_row");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_header_entry");

        $strEntries = "";
        foreach($arrHeader as $strOneHeader)
            $strEntries .= $this->objTemplate->fillTemplate(array("name" => $strOneHeader), $strTemplateEntryID);

        return $this->objTemplate->fillTemplate(array("entries" => $strEntries), $strTemplateID);
    }

    /**
     * Renders a complete row of days.
     *
     * @param string $strContent
     * @return string
     * @since 3.4
     */
    public function getCalendarRow($strContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_row");
        return $this->objTemplate->fillTemplate(array("entries" => $strContent), $strTemplateID);
    }

    /**
     * Renders a single entry within the calendar. In most cases this is a single day.
     *
     * @param string $strContent
     * @param string $strDate
     * @return string
     * @since 3.4
     */
    public function getCalendarEntry($strContent, $strDate, $strClass = "calendarEntry") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_entry");
        return $this->objTemplate->fillTemplate(array("content" => $strContent, "date" => $strDate, "class" => $strClass), $strTemplateID);
    }

    /**
     * Renders a single calendar-event
     *
     * @param string $strContent
     * @param string $strId
     * @param string $strHighlightId
     * @param string $strClass
     * @return string
     * @since 3.4
     */
    public function getCalendarEvent($strContent, $strId = "", $strHighlightId = "", $strClass = "calendarEvent") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_event");
        if($strId == "")
            $strId = generateSystemid();
        return $this->objTemplate->fillTemplate(array("content" => $strContent, "class" => $strClass, "systemid" => $strId, "highlightid" =>$strHighlightId), $strTemplateID);
    }

    //---contect menues ---------------------------------------------------------------------------------

    /**
     * Creates the markup to render a js-based contex-menu.
     * Each entry is an array with the keys
     *   array("name" => "xx", "onclick" => "xx");
     * A menu may be shown by calling
     * KAJONA.admin.contextMenu.showElementMenu('$strIdentifier', this)
     * whereas this is the js-element the menu should be attached to.
     *
     * @since 3.4.1
     * @param string $strIdentifier
     * @param string $arrEntries
     * @return string
     */
    public function registerMenu($strIdentifier, $arrEntries) {
        $strEntries = "";
        foreach($arrEntries as $arrOneEntry)
            $strEntries .= "{elementName:'".$arrOneEntry["name"]."', elementAction:'".uniStrReplace("'", "\'", $arrOneEntry["onclick"])."'},";

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_wrapper");
        $arrTemplate = array();
        $arrTemplate["id"] = $strIdentifier;
        $arrTemplate["entries"] = uniSubstr($strEntries, 0, -1);
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }
}
?>