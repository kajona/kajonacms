<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/


/**
 * Admin-Part of the toolkit-classes
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_toolkit_admin extends class_toolkit
{

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        //Calling the base class
        parent::__construct();
    }

    /**
     * Returns a simple date-form element. By default used to enter a date without a time.
     *
     * @param string $strName
     * @param string $strTitle
     * @param class_date $objDateToShow
     * @param string $strClass = inputDate
     * @param boolean $bitWithTime
     *
     * @throws class_exception
     * @return string
     * @since 3.2.0.9
     */
    public function formDateSingle($strName, $strTitle, $objDateToShow, $strClass = "", $bitWithTime = false, $bitReadOnly = false)
    {
        //check passed param
        if ($objDateToShow != null && !$objDateToShow instanceof class_date) {
            throw new class_exception("param passed to class_toolkit_admin::formDateSingle is not an instance of class_date", class_exception::$level_ERROR);
        }

        if ($bitWithTime) {
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_datetime_simple");
        }
        else {
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_date_simple");
        }
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
        $arrTemplate["valuePlain"] = dateToString($objDateToShow, false);
//        if($bitWithTime)
        $arrTemplate["dateFormat"] = class_carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system");
//        else
//            $arrTemplate["dateFormat"] = class_carrier::getInstance()->getObjLang()->getLang("dateStyleLong", "system");
        $arrTemplate["calendarLang"] = class_carrier::getInstance()->getObjSession()->getAdminLanguage();

        $arrTemplate["titleTime"] = class_carrier::getInstance()->getObjLang()->getLang("titleTime", "system");

        //set up the container div
        $arrTemplate["calendarId"] = $strName;
        $strContainerId = $strName."_calendarContainer";
        $arrTemplate["calendarContainerId"] = $strContainerId;
        $arrTemplate["calendarLang_weekday"] = " [".class_carrier::getInstance()->getObjLang()->getLang("toolsetCalendarWeekday", "system")."]\n";
        $arrTemplate["calendarLang_month"] = " [".class_carrier::getInstance()->getObjLang()->getLang("toolsetCalendarMonth", "system")."]\n";

        $arrTemplate["readonly"] = ($bitReadOnly ? "disabled=\"disabled\"" : "");

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
     *
     * @return string
     */
    public function formWysiwygEditor($strName = "inhalt", $strTitle = "", $strContent = "", $strToolbarset = "standard")
    {
        $strReturn = "";

        //create the html-input element
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "wysiwyg_ckeditor");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["editorid"] = generateSystemid();
        $arrTemplate["content"] = htmlentities($strContent, ENT_COMPAT, "UTF-8");
        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
        //for the popups, we need the skinwebpath
        $strReturn .= $this->formInputHidden("skinwebpath", _skinwebpath_);

        //set the language the user defined for the admin
        $strLanguage = class_session::getInstance()->getAdminLanguage();
        if ($strLanguage == "") {
            $strLanguage = "en";
        }

        //include the settings made by admin skin
        $strTemplateInitID = $this->objTemplate->readTemplate("/elements.tpl", "wysiwyg_ckeditor_inits");
        $strTemplateInit = $this->objTemplate->fillTemplate(array(), $strTemplateInitID);

        //check if a customized editor-config is available
        $strConfigFile = "'config_kajona_standard.js'";
        if (is_file(_realpath_."/project/admin/scripts/ckeditor/config_kajona_standard.js")) {
            $strConfigFile = "KAJONA_WEBPATH+'/project/admin/scripts/ckeditor/config_kajona_standard.js'";
        }

        //to add role-based editors, you could load a different toolbar or also a different CKEditor config file
        //the editor code
        $strReturn .= " <script type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/admin/scripts/ckeditor/ckeditor.js\"></script>\n";
        $strReturn .= " <script type=\"text/javascript\">\n";
        $strReturn .= "
            var ckeditorConfig = {
                customConfig : ".$strConfigFile.",
                toolbar : '".$strToolbarset."',
                ".$strTemplateInit."
                language : '".$strLanguage."',
                filebrowserBrowseUrl : '".uniStrReplace("&amp;", "&", getLinkAdminHref("folderview", "browserChooser", "&form_element=ckeditor"))."',
                filebrowserImageBrowseUrl : '".uniStrReplace("&amp;", "&", getLinkAdminHref("mediamanager", "folderContentFolderviewMode", "systemid=".class_module_system_setting::getConfigValue("_mediamanager_default_imagesrepoid_")."&form_element=ckeditor&bit_link=1"))."'
	        };
            CKEDITOR.replace($(\"textarea[name='".$strName."'][data-kajona-editorid='".$arrTemplate["editorid"]."']\")[0], ckeditorConfig);
        ";
        $strReturn .= "</script>\n";

        return $strReturn;
    }


    /**
     * Returns a divider to split up a page in logical sections
     *
     * @param string $strClass
     *
     * @return string
     */
    public function divider($strClass = "divider")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "divider");
        $arrTemplate = array();
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Creates a percent-beam to illustrate proportions
     *
     * @param float $floatPercent
     *
     * @return string
     */
    public function percentBeam($floatPercent, $bitRenderAnimated = true)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "percent_beam");
        $arrTemplate = array();
        $arrTemplate["percent"] = number_format($floatPercent, 2);
        $arrTemplate["animationClass"] = $bitRenderAnimated ? "progress-bar-striped" : "";
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
     * @param bool $bitReadOnly
     *
     * @return string
     */
    public function formInputCheckbox($strName, $strTitle, $bitChecked = false, $strClass = "", $bitReadOnly = false)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_checkbox");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["checked"] = ($bitChecked ? "checked=\"checked\"" : "");
        $arrTemplate["readonly"] = ($bitReadOnly ? "disabled=\"disabled\"" : "");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a On-Off toggle button
     *
     * @param string $strName
     * @param string $strTitle
     * @param bool $bitChecked
     * @param bool $bitReadOnly
     * @param string $strOnSwitchJSCallback
     * @param string $strClass
     *
     * @return string
     */
    public function formInputOnOff($strName, $strTitle, $bitChecked = false, $bitReadOnly = false, $strOnSwitchJSCallback = "", $strClass = "")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_on_off_switch");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["checked"] = ($bitChecked ? "checked=\"checked\"" : "");
        $arrTemplate["readonly"] = ($bitReadOnly ? "disabled=\"disabled\"" : "");
        $arrTemplate["onSwitchJSCallback"] = $strOnSwitchJSCallback;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a regular hidden-input-field
     *
     * @param string $strName
     * @param string $strValue
     *
     * @return string
     */
    public function formInputHidden($strName, $strValue = "")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_hidden");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
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
     *
     * @return string
     */
    public function formInputText($strName, $strTitle = "", $strValue = "", $strClass = "", $strOpener = "", $bitReadonly = false)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_text");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
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
     * @param bool $bitRenderOpener
     * @param string $strAddonAction
     *
     * @throws class_exception
     * @return string
     */
    public function formInputPageSelector($strName, $strTitle = "", $strValue = "", $strClass = "", $bitElements = true, $bitRenderOpener = true, $strAddonAction = "")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_pageselector");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;

        $arrTemplate["opener"] = "";
        if ($bitRenderOpener) {
            $arrTemplate["opener"] .= getLinkAdminDialog(
                "pages",
                "pagesFolderBrowser",
                "&pages=1&form_element=".$strName.(!$bitElements ? "&elements=false" : ""),
                class_carrier::getInstance()->getObjLang()->getLang("select_page", "pages"),
                class_carrier::getInstance()->getObjLang()->getLang("select_page", "pages"),
                "icon_externalBrowser",
                class_carrier::getInstance()->getObjLang()->getLang("select_page", "pages")
            );
        }

        $arrTemplate["opener"] .= $strAddonAction;

        $strJsVarName = uniStrReplace(array("[", "]"), array("", ""), $strName);

        $arrTemplate["ajaxScript"] = "
	        <script type=\"text/javascript\">
                    $(function() {
                        var objConfig = new KAJONA.v4skin.defaultAutoComplete();
                        objConfig.source = function(request, response) {
                            $.ajax({
                                url: '".getLinkAdminXml("pages", "getPagesByFilter")."',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    filter: request.term
                                },
                                success: response
                            });
                        };

                        KAJONA.admin.".$strJsVarName." = $('#".uniStrReplace(array("[", "]"), array("\\\[", "\\\]"), $strName)."').autocomplete(objConfig);
                    });
	        </script>
        ";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    /**
     * Returns a regular text-input field.
     * The param $strValue expects a system-id.
     * The element creates two fields:
     * a text-field, and a hidden field for the selected systemid.
     * The hidden field is names as $strName, appended by "_id".
     * If you want to filter the list for users having at least view-permissions on a given systemid, you may pass the id as an optional param.
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     * @param bool $bitUser
     * @param bool $bitGroups
     * @param bool $bitBlockCurrentUser
     * @param string $arrValidateSystemid If you want to check the view-permissions for a given systemid, pass the id here
     *
     * @return string
     * @throws class_exception
     */
    public function formInputUserSelector($strName, $strTitle = "", $strValue = "", $strClass = "", $bitUser = true, $bitGroups = false, $bitBlockCurrentUser = false, array $arrValidateSystemid = null)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_userselector");

        $strUserName = "";
        $strUserId = "";

        //value is a systemid
        if (validateSystemid($strValue)) {
            $objUser = new class_module_user_user($strValue);
            $strUserName = $objUser->getStrDisplayName();
            $strUserId = $strValue;
        }

        $strCheckIds = json_encode($arrValidateSystemid);

        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strUserName, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["value_id"] = htmlspecialchars($strUserId, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["opener"] = class_link::getLinkAdminDialog(
            "user",
            "userBrowser",
            "&form_element={$strName}&checkid={$strCheckIds}".($bitGroups ? "&allowGroup=1" : "").($bitBlockCurrentUser ? "&filter=current" : ""),
            class_carrier::getInstance()->getObjLang()->getLang("user_browser", "user"),
            class_carrier::getInstance()->getObjLang()->getLang("user_browser", "user"),
            "icon_externalBrowser",
            class_carrier::getInstance()->getObjLang()->getLang("user_browser", "user")
        );

        $strResetIcon = class_link::getLinkAdminManual(
            "href=\"#\" onclick=\"document.getElementById('".$strName."').value='';document.getElementById('".$strName."_id').value='';return false;\"",
            "",
            class_carrier::getInstance()->getObjLang()->getLang("user_browser_reset", "user"),
            "icon_delete"
        );

        $arrTemplate["opener"] .= " ".$strResetIcon;

        $strName = uniStrReplace(array("[", "]"), array("\\\[", "\\\]"), $strName);
        $arrTemplate["ajaxScript"] = "
	        <script type=\"text/javascript\">
                    $(function() {

                        var objConfig = new KAJONA.v4skin.defaultAutoComplete();
                        objConfig.source = function(request, response) {
                            $.ajax({
                                url: '".getLinkAdminXml("user", "getUserByFilter")."',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    filter: request.term,
                                    user: ".($bitUser ? "'true'" : "'false'").",
                                    group: ".($bitGroups ? "'true'" : "'false'").",
                                    block: ".($bitBlockCurrentUser ? "'current'" : "''").",
                                    checkid: '".$strCheckIds."'
                                },
                                success: response
                            });
                        };


                        $('#".$strName."').autocomplete(objConfig).data( 'ui-autocomplete' )._renderItem = function( ul, item ) {
                            return $( '<li></li>' )
                                .data('ui-autocomplete-item', item)
                                .append( '<a class=\'ui-autocomplete-item\' >'+item.icon+item.title+'</a>' )
                                .appendTo( ul );
                        } ;
                    });
	        </script>
        ";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * General form entry which displays an list of objects which can be deleted. It is possible to provide an addlink
     * where entries can be appended to the list. To add an entry you can use the javascript function
     * KAJONA.v4skin.setObjectListItems
     *
     * @param $strName
     * @param string $strTitle
     * @param array $arrObjects
     * @param string $strAddLink
     *
     * @return string
     * @throws class_exception
     */
    public function formInputObjectList($strName, $strTitle = "", array $arrObjects, $strAddLink, $bitReadOnly = false)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_objectlist");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "input_objectlist_row");

        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["addLink"] = $bitReadOnly ? "" : $strAddLink;

        $strTable = '';
        foreach ($arrObjects as $objObject) {
            /** @var $objObject class_model */
            if ($objObject instanceof interface_model && $objObject->rightView()) {
                $strRemoveLink = "";
                if (!$bitReadOnly) {
                    $strDelete = class_carrier::getInstance()->getObjLang()->getLang("commons_remove_assignment", "system");
                    $strRemoveLink = class_link::getLinkAdminDialog(null, "", "", $strDelete, $strDelete, "icon_delete", $strDelete, true, false, "KAJONA.v4skin.removeObjectListItem(this);return false;");
                }

                $strIcon = is_array($objObject->getStrIcon()) ? $objObject->getStrIcon()[0] : $objObject->getStrIcon();
                $arrTemplateRow = array(
                    'name'        => $strName,
                    'displayName' => class_formentry_objectlist::getDisplayName($objObject),
                    'path'        => class_formentry_objectlist::getPathName($objObject),
                    'icon'        => class_adminskin_helper::getAdminImage($strIcon),
                    'value'       => $objObject->getSystemid(),
                    'removeLink'  => $strRemoveLink,
                );

                $strTable .= $this->objTemplate->fillTemplate($arrTemplateRow, $strTemplateRowID, true);
            }
        }

        $arrTemplate["table"] = $strTable;

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
     *
     * @return string
     * @since 3.3.4
     */
    public function formInputFileSelector($strName, $strTitle = "", $strValue = "", $strRepositoryId = "", $strClass = "")
    {
        $strOpener = getLinkAdminDialog(
            "mediamanager",
            "folderContentFolderviewMode",
            "&form_element=".$strName."&systemid=".$strRepositoryId,
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system"),
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system"),
            "icon_externalBrowser",
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system")
        );

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
     *
     * @return string
     * @since 3.4.0
     */
    public function formInputImageSelector($strName, $strTitle = "", $strValue = "", $strClass = "")
    {
        $strOpener = getLinkAdminDialog(
            "mediamanager",
            "folderContentFolderviewMode",
            "&form_element=".$strName."&systemid=".class_module_system_setting::getConfigValue("_mediamanager_default_imagesrepoid_"),
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system"),
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system"),
            "icon_externalBrowser",
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system")
        );

        $strOpener .= " ".getLinkAdminDialog(
                "mediamanager",
                "imageDetails",
                "file='+document.getElementById('".$strName."').value+'",
                class_carrier::getInstance()->getObjLang()->getLang("action_edit_image", "mediamanager"),
                class_carrier::getInstance()->getObjLang()->getLang("action_edit_image", "mediamanager"),
                "icon_crop",
                class_carrier::getInstance()->getObjLang()->getLang("action_edit_image", "mediamanager"),
                true,
                false,
                " (function() {
             if(document.getElementById('".$strName."').value != '') {
                 KAJONA.admin.folderview.dialog.setContentIFrame('".urldecode(getLinkAdminHref("mediamanager", "imageDetails", "file='+document.getElementById('".$strName."').value+'"))."');
                 KAJONA.admin.folderview.dialog.setTitle('".$strTitle."');
                 KAJONA.admin.folderview.dialog.init();
             }
             return false; })(); return false;"
            );

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
     * @param int $numberOfRows
     *
     * @return string
     */
    public function formInputTextArea($strName, $strTitle = "", $strValue = "", $strClass = "", $bitReadonly = false, $numberOfRows = 4)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_textarea");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["readonly"] = ($bitReadonly ? " readonly=\"readonly\" " : "");
        $arrTemplate["numberOfRows"] = $numberOfRows;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a password text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     *
     * @return string
     */
    public function formInputPassword($strName, $strTitle = "", $strValue = "", $strClass = "")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_password");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
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
     * @param string $strClass use cancelbutton for cancel-buttons
     * @param bool $bitEnabled
     *
     * @return string
     */
    public function formInputSubmit($strValue = null, $strName = "Submit", $strEventhandler = "", $strClass = "", $bitEnabled = true)
    {
        if ($strValue === null) {
            $strValue = class_carrier::getInstance()->getObjLang()->getLang("commons_save", "system");
        }

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
     * @param string $strClass
     *
     * @return string
     */
    public function formInputUpload($strName, $strTitle = "", $strClass = "")
    {

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_upload");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;

        $objText = class_carrier::getInstance()->getObjLang();
        $arrTemplate["maxSize"] = $objText->getLang("max_size", "mediamanager")." ".bytesToString(class_config::getInstance()->getPhpMaxUploadSize());

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a input-file element for uploading multiple files with progress bar. Only functionable in combination with
     * the mediamanager module
     *
     * @param string $strName
     * @param string $strAllowedFileTypes
     * @param string $strMediamangerRepoSystemId
     *
     * @return string
     */
    public function formInputUploadMultiple($strName, $strAllowedFileTypes, $strMediamangerRepoSystemId)
    {

        if (class_module_system_module::getModuleByName("mediamanager") === null) {
            return ($this->warningBox("Module mediamanger is required for this multiple uploads"));
        }

        $objConfig = class_carrier::getInstance()->getObjConfig();
        $objText = class_carrier::getInstance()->getObjLang();

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_upload_multiple");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["mediamanagerRepoId"] = $strMediamangerRepoSystemId;

        $strAllowedFileRegex = uniStrReplace(array(".", ","), array("", "|"), $strAllowedFileTypes);
        $strAllowedFileTypes = uniStrReplace(array(".", ","), array("", "', '"), $strAllowedFileTypes);

        $arrTemplate["allowedExtensions"] = $strAllowedFileTypes != "" ? $objText->getLang("upload_allowed_extensions", "mediamanager").": '".$strAllowedFileTypes."'" : $strAllowedFileTypes;
        $arrTemplate["maxFileSize"] = $objConfig->getPhpMaxUploadSize();
        $arrTemplate["acceptFileTypes"] = $strAllowedFileRegex != "" ? "/(\.|\/)(".$strAllowedFileRegex.")$/i" : "''";

        $arrTemplate["upload_multiple_errorFilesize"] = $objText->getLang("upload_multiple_errorFilesize", "mediamanager")." ".bytesToString($objConfig->getPhpMaxUploadSize());

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
     * @param string $strDataPlaceholder
     * @param string $strOpener
     *
     * @return string
     * @throws class_exception
     */
    public function formInputDropdown($strName, array $arrKeyValues, $strTitle = "", $strKeySelected = "", $strClass = "", $bitEnabled = true, $strAddons = "", $strDataPlaceholder = "", $strOpener = "")
    {
        $strOptions = "";
        $strTemplateOptionID = $this->objTemplate->readTemplate("/elements.tpl", "input_dropdown_row");
        $strTemplateOptionSelectedID = $this->objTemplate->readTemplate("/elements.tpl", "input_dropdown_row_selected");

        foreach (array("", 0, "\"\"") as $strOneKeyToCheck) {
            if (array_key_exists($strOneKeyToCheck, $arrKeyValues) && trim($arrKeyValues[$strOneKeyToCheck]) == "") {
                unset($arrKeyValues[$strOneKeyToCheck]);
            }
        }

        //see if the selected value is valid
        if (!in_array($strKeySelected, array_keys($arrKeyValues))) {
            $strKeySelected = "";
        }

        if (!isset($arrKeyValues[""])) {
            $strPlaceholder = $strDataPlaceholder != "" ? $strDataPlaceholder : class_carrier::getInstance()->getObjLang()->getLang("commons_dropdown_dataplaceholder", "system");
            $strOptions .= "<option value='' disabled ".($strKeySelected == "" ? " selected " : "").">".$strPlaceholder."</option>";
        }

        //Iterating over the array to create the options
        foreach ($arrKeyValues as $strKey => $strValue) {
            $arrTemplate = array();
            $arrTemplate["key"] = $strKey;
            $arrTemplate["value"] = $strValue;
            if ((string)$strKey == (string)$strKeySelected) {
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionSelectedID);
            }
            else {
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionID);
            }
        }


        $arrTemplate = array();
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_dropdown");
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["disabled"] = ($bitEnabled ? "" : "disabled=\"disabled\"");
        $arrTemplate["options"] = $strOptions;
        $arrTemplate["addons"] = $strAddons;
        $arrTemplate["opener"] = $strOpener;
        $arrTemplate["dataplaceholder"] = $strDataPlaceholder != "" ? $strDataPlaceholder : class_carrier::getInstance()->getObjLang()->getLang("commons_dropdown_dataplaceholder", "system");


        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    /**
     * Returning a complete dropdown but in multiselect-style
     *
     * @param string $strName
     * @param mixed $arrKeyValues
     * @param string $strTitle
     * @param array $arrKeysSelected
     * @param string $strClass
     * @param bool $bitEnabled
     * @param string $strAddons
     *
     * @return string
     */
    public function formInputMultiselect($strName, array $arrKeyValues, $strTitle = "", $arrKeysSelected = array(), $strClass = "", $bitEnabled = true, $strAddons = "")
    {
        $strOptions = "";
        $strTemplateOptionID = $this->objTemplate->readTemplate("/elements.tpl", "input_multiselect_row");
        $strTemplateOptionSelectedID = $this->objTemplate->readTemplate("/elements.tpl", "input_multiselect_row_selected");
        //Iterating over the array to create the options
        foreach ($arrKeyValues as $strKey => $strValue) {
            $arrTemplate = array();
            $arrTemplate["key"] = $strKey;
            $arrTemplate["value"] = $strValue;
            if (in_array($strKey, $arrKeysSelected)) {
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionSelectedID);
            }
            else {
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionID);
            }
        }

        $arrTemplate = array();
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_multiselect");
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["disabled"] = ($bitEnabled ? "" : "disabled=\"disabled\"");
        $arrTemplate["options"] = $strOptions;
        $arrTemplate["addons"] = $strAddons;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Form entry which displays an input text field where you can add or remove tags
     *
     * @param $strName
     * @param string $strTitle
     * @param array $arrObjects
     *
     * @return string
     * @throws class_exception
     */
    public function formInputTagEditor($strName, $strTitle = "", array $arrValues = array(), $strOnChange = null)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_tageditor");

        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["values"] = json_encode(array_values($arrValues));
        $arrTemplate["onChange"] = empty($strOnChange) ? "function(){}" : (string)$strOnChange;

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Form entry which displays an input text field where you must select entries from an autocomplete
     *
     * @param $strName
     * @param string $strTitle
     * @param $strSource
     * @param array $arrValues
     * @param null $strOnChange
     * @return string
     * @throws class_exception
     */
    public function formInputObjectTags($strName, $strTitle = "", $strSource, array $arrValues = array(), $strOnChange = null)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_objecttags");

        $strData = "";
        $arrResult = array();
        if (!empty($arrValues)) {
            foreach ($arrValues as $objValue) {
                if ($objValue instanceof interface_model) {
                    $strData.= '<input type="hidden" name="' . $strName . '_id[]" value="' . $objValue->getStrSystemid() . '" data-title="' . htmlspecialchars($objValue->getStrDisplayName()) . '" />';
                    $arrResult[] = $objValue->getStrDisplayName();
                }
            }
        }

        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["values"] = json_encode(array_values($arrResult));
        $arrTemplate["onChange"] = empty($strOnChange) ? "function(){}" : (string)$strOnChange;
        $arrTemplate["source"] = $strSource;
        $arrTemplate["data"] = $strData;

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Returns a toggle button bar which can be used in the same way as an multiselect
     *
     * @param string $strName
     * @param mixed $arrKeyValues
     * @param string $strTitle
     * @param array $arrKeysSelected
     * @param bool $bitEnabled
     *
     * @return string
     */
    public function formToggleButtonBar($strName, array $arrKeyValues, $strTitle = "", $arrKeysSelected = array(), $bitEnabled = true, $strType = "checkbox")
    {
        $strOptions = "";
        $strTemplateOptionID = $this->objTemplate->readTemplate("/elements.tpl", "input_toggle_buttonbar_button");
        $strTemplateOptionSelectedID = $this->objTemplate->readTemplate("/elements.tpl", "input_toggle_buttonbar_button_selected");
        //Iterating over the array to create the options
        foreach ($arrKeyValues as $strKey => $strValue) {
            $arrTemplate = array();
            $arrTemplate["name"] = $strName;
            $arrTemplate["type"] = $strType;
            $arrTemplate["key"] = $strKey;
            $arrTemplate["value"] = $strValue;
            $arrTemplate["disabled"] = ($bitEnabled ? "" : "disabled=\"disabled\"");
            $arrTemplate["btnclass"] = ($bitEnabled ? "" : "disabled");
            if (in_array($strKey, $arrKeysSelected)) {
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionSelectedID);
            }
            else {
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionID);
            }
        }

        $arrTemplate = array();
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_toggle_buttonbar");
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["options"] = $strOptions;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Creates a list of radio-buttons.
     * In difference to a dropdown a radio-button may not force the user to
     * make a selection / does not generate an implicit selection
     *
     * @param string $strName
     * @param mixed $arrKeyValues
     * @param string $strTitle
     * @param string $strKeySelected
     * @param string $strClass
     * @param bool $bitEnabled
     *
     * @return string
     */
    public function formInputRadiogroup($strName, array $arrKeyValues, $strTitle = "", $strKeySelected = "", $strClass = "", $bitEnabled = true)
    {
        $strOptions = "";
        $strTemplateRadioID = $this->objTemplate->readTemplate("/elements.tpl", "input_radiogroup_row");
        //Iterating over the array to create the options
        foreach ($arrKeyValues as $strKey => $strValue) {
            $arrTemplate = array();
            $arrTemplate["key"] = $strKey;
            $arrTemplate["value"] = $strValue;
            $arrTemplate["name"] = $strName;
            $arrTemplate["class"] = $strClass;
            $arrTemplate["disabled"] = ($bitEnabled ? "" : "disabled=\"disabled\"");
            $arrTemplate["checked"] = ((string)$strKey == (string)$strKeySelected ? " checked " : "");
            $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateRadioID);
        }

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_radiogroup");
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["radios"] = $strOptions;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Form entry which is an container for other form elements
     *
     * @param $strName
     * @param string $strTitle
     * @param array $arrFields
     *
     * @return string
     * @throws class_exception
     */
    public function formInputContainer($strName, $strTitle = "", array $arrFields = array(), $strOpener = "")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_container");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "input_container_row");

        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["opener"] = $strOpener;

        $strElements = "";
        foreach ($arrFields as $strField) {
            $strElements .= $this->objTemplate->fillTemplate(array("element" => $strField), $strTemplateRowID, true);
        }

        $arrTemplate["elements"] = $strElements;

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * @param $strName
     * @param string $strTitle
     * @param $intType
     * @param array $arrValues
     * @param array $arrSelected
     * @param bool $bitInline
     *
     * @return string
     * @throws class_exception
     */
    public function formInputCheckboxArray($strName, $strTitle = "", $intType, array $arrValues, array $arrSelected, $bitInline = false, $bitReadonly = false, $strOpener = "")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_checkboxarray");
        $strTemplateCheckboxID = $this->objTemplate->readTemplate("/elements.tpl", "input_checkboxarray_checkbox");

        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["opener"] = $strOpener;

        $strElements = '';
        foreach ($arrValues as $strKey => $strValue) {
            $arrTemplateRow = array(
                'key'      => $strKey,
                'name'     => $intType == class_formentry_checkboxarray::TYPE_RADIO ? $strName : $strName.'['.$strKey.']',
                'value'    => $intType == class_formentry_checkboxarray::TYPE_RADIO ? $strKey : 'checked',
                'title'    => $strValue,
                'checked'  => in_array($strKey, $arrSelected) ? 'checked' : '',
                'inline'   => $bitInline ? '-inline' : '',
                'readonly' => $bitReadonly ? 'disabled' : '',
            );

            switch ($intType) {
                case class_formentry_checkboxarray::TYPE_RADIO:
                    $arrTemplateRow['type'] = 'radio';
                    $strElements .= $this->objTemplate->fillTemplate($arrTemplateRow, $strTemplateCheckboxID, true);
                    break;

                default:
                case class_formentry_checkboxarray::TYPE_CHECKBOX:
                    $arrTemplateRow['type'] = 'checkbox';
                    $strElements .= $this->objTemplate->fillTemplate($arrTemplateRow, $strTemplateCheckboxID, true);
                    break;
            }
        }

        $arrTemplate["elements"] = $strElements;

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Creates the header needed to open a form-element
     *
     * @param string $strAction
     * @param string $strName
     * @param string $strEncoding
     * @param string $strOnSubmit
     *
     * @return string
     */
    public function formHeader($strAction, $strName = "", $strEncoding = "", $strOnSubmit = "")
    {
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
     *
     * @return string
     */
    public function formOptionalElementsWrapper($strContent, $strTitle = "", $bitVisible = false)
    {
        $arrFolder = $this->getLayoutFolderPic($strContent, $strTitle, "icon_folderOpen", "icon_folderClosed", $bitVisible);
        return $this->getFieldset($arrFolder[1], $arrFolder[0]);
    }

    /**
     * Returns a single TextRow in a form
     *
     * @param string $strText
     * @param string $strClass
     *
     * @return string
     */
    public function formTextRow($strText, $strClass = "")
    {
        if ($strText == "") {
            return "";
        }

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
     *
     * @return string
     */
    public function formHeadline($strText, $strClass = "")
    {
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
     * @param bool $bitIncludePeFields
     *
     * @return string
     */
    public function formClose($bitIncludePeFields = true)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "form_close");
        $strPeFields = "";
        if ($bitIncludePeFields) {
            $arrParams = class_carrier::getAllParams();
            if (array_key_exists("pe", $arrParams)) {
                $strPeFields .= $this->formInputHidden("pe", $arrParams["pe"]);
            }
            if (array_key_exists("folderview", $arrParams)) {
                $strPeFields .= $this->formInputHidden("folderview", $arrParams["folderview"]);

                if (!array_key_exists("pe", $arrParams)) {
                    $strPeFields .= $this->formInputHidden("pe", "1");
                }
            }
            if (array_key_exists("pv", $arrParams)) {
                $strPeFields .= $this->formInputHidden("pv", $arrParams["pv"]);
            }
        }
        return $strPeFields.$this->objTemplate->fillTemplate(array(), $strTemplateID);
    }


    /*"*****************************************************************************************************/
    // --- GRID-Elements ------------------------------------------------------------------------------------

    /**
     * Creates the code to start a sortable grid.
     * By default, a grid is sortable.
     *
     * @param bool $bitSortable
     * @param $intElementsPerPage
     * @param $intCurPage
     *
     * @return string
     */
    public function gridHeader($bitSortable = true, $intElementsPerPage = -1, $intCurPage = -1)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "grid_header");
        return $this->objTemplate->fillTemplate(
            array("sortable" => ($bitSortable ? "sortable" : ""), "elementsPerPage" => $intElementsPerPage, "curPage" => $intCurPage),
            $strTemplateID
        );
    }

    /**
     * Renders a single entry of the current grid.
     *
     * @param interface_admin_gridable|class_model|interface_model $objEntry
     * @param $strActions
     * @param string $strClickAction
     *
     * @return string
     */
    public function gridEntry(interface_admin_gridable $objEntry, $strActions, $strClickAction = "")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "grid_entry");

        $strCSSAddon = "";
        if (method_exists($objEntry, "getIntRecordStatus")) {
            $strCSSAddon = $objEntry->getIntRecordStatus() == 0 ? "disabled" : "";
        }

        $arrTemplate = array(
            "title"       => $objEntry->getStrDisplayName(),
            "image"       => $objEntry->getStrGridIcon(),
            "actions"     => $strActions,
            "systemid"    => $objEntry->getSystemid(),
            "subtitle"    => $objEntry->getStrLongDescription(),
            "info"        => $objEntry->getStrAdditionalInfo(),
            "cssaddon"    => $strCSSAddon,
            "clickaction" => $strClickAction
        );

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Renders the closing elements of a grid.
     *
     * @return string
     */
    public function gridFooter()
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "grid_footer");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /*"*****************************************************************************************************/
    // --- LIST-Elements ------------------------------------------------------------------------------------

    /**
     * Returns the htmlcode needed to start a proper list
     *
     * @return string
     */
    public function listHeader()
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_header");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * Returns the htmlcode needed to start a proper list, supporting drag n drop to
     * reorder list-items
     *
     * @param string $strListId
     * @param bool $bitOnlySameTable dropping only allowed within the same table or also in other tables
     * @param bool $bitAllowDropOnTree
     * @param int $intElementsPerPage
     * @param int $intCurPage
     *
     * @return string
     */
    public function dragableListHeader($strListId, $bitOnlySameTable = false, $bitAllowDropOnTree = false, $intElementsPerPage = -1, $intCurPage = -1)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dragable_list_header");
        return $this->objTemplate->fillTemplate(
            array(
                "listid"          => $strListId,
                "sameTable"       => $bitOnlySameTable ? "true" : "false",
                "jsInject"        => "bitMoveToTree = ".($bitAllowDropOnTree ? "true" : "false").";",
                "elementsPerPage" => $intElementsPerPage,
                "curPage"         => $intCurPage
            ),
            $strTemplateID
        );
    }


    /**
     * Returns the code to finish the opened list
     *
     * @return string
     */
    public function listFooter()
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_footer");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * Returns the code to finish the opened list
     *
     * @param string $strListId
     *
     * @return string
     */
    public function dragableListFooter($strListId)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dragable_list_footer");
        return $this->objTemplate->fillTemplate(array("listid" => $strListId), $strTemplateID);
    }

    /**
     * Renders a simple admin-object, implementing interface_model
     *
     * @param interface_admin_listable|interface_model|class_model $objEntry
     * @param string $strActions
     * @param int $intCount
     * @param bool $bitCheckbox
     *
     * @return string
     */
    public function simpleAdminList(interface_admin_listable $objEntry, $strActions, $intCount, $bitCheckbox = false)
    {
        $strImage = $objEntry->getStrIcon();
        if (is_array($strImage)) {
            $strImage = class_adminskin_helper::getAdminImage($strImage[0], $strImage[1]);
        }
        else {
            $strImage = class_adminskin_helper::getAdminImage($strImage);
        }

        $strCSSAddon = "";
        if (method_exists($objEntry, "getIntRecordStatus")) {
            $strCSSAddon = $objEntry->getIntRecordStatus() == 0 ? "disabled" : "";
        }

        return $this->genericAdminList(
            $objEntry->getSystemid(),
            $objEntry->getStrDisplayName(),
            $strImage,
            $strActions,
            $intCount,
            $objEntry->getStrAdditionalInfo(),
            $objEntry->getStrLongDescription(),
            $bitCheckbox,
            $strCSSAddon
        );
    }

    /**
     * Renders a single admin-row, takes care of selecting the matching template-sections.
     *
     * @param string $strId
     * @param string $strName
     * @param string $strIcon
     * @param string $strActions
     * @param int $intCount
     * @param string $strAdditionalInfo
     * @param string $strDescription
     * @param bool $bitCheckbox
     * @param string $strCssAddon
     *
     * @return string
     */
    public function genericAdminList($strId, $strName, $strIcon, $strActions, $intCount, $strAdditionalInfo = "", $strDescription = "", $bitCheckbox = false, $strCssAddon = "")
    {
        $arrTemplate = array();
        $arrTemplate["listitemid"] = $strId;
        $arrTemplate["image"] = $strIcon;
        $arrTemplate["title"] = $strName;
        $arrTemplate["center"] = $strAdditionalInfo;
        $arrTemplate["actions"] = $strActions;
        $arrTemplate["description"] = $strDescription;
        $arrTemplate["cssaddon"] = $strCssAddon;

        if ($bitCheckbox) {
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "generallist_checkbox");
            $arrTemplate["checkbox"] = $this->objTemplate->fillTemplate(array("systemid" => $strId), $strTemplateID);
        }


        //fallback-awareness for update-scenarios (update to 4.3)
        $strGlobalTemplateId = $this->objTemplate->readTemplate("/elements.tpl");

        if ($strDescription != "") {
            if ($this->objTemplate->containsSection($strGlobalTemplateId, "generallist_desc")) {
                $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "generallist_desc");
            }
            else {
                $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "generallist_desc_1");
            }
        }
        else {
            if ($this->objTemplate->containsSection($strGlobalTemplateId, "generallist")) {
                $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "generallist");
            }
            else {
                $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "generallist_1");
            }
        }

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     *
     * @param \class_admin_batchaction[] $arrActions
     *
     * @return string
     */
    public function renderBatchActionHandlers(array $arrActions)
    {
        $strEntries = "";
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "batchactions_entry");

        foreach ($arrActions as $objOneAction) {
            $strEntries .= $this->objTemplate->fillTemplate(
                array(
                    "title"     => $objOneAction->getStrTitle(),
                    "icon"      => $objOneAction->getStrIcon(),
                    "targeturl" => $objOneAction->getStrTargetUrl()
                ),
                $strTemplateID
            );
        }

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "batchactions_wrapper");
        return $this->objTemplate->fillTemplate(array("entries" => $strEntries), $strTemplateID);
    }

    /**
     * Returns a table filled with infos.
     * The header may be build using cssclass -> value or index -> value arrays
     * Values may be build using cssclass -> value or index -> value arrays, too (per row)
     *
     * @param mixed $arrHeader the first row to name the columns
     * @param mixed $arrValues every entry is one row
     * @param string $strTableCssAddon an optional css-class added to the table tag
     * @param boolean $bitWithTbody whether to render the table with a tbody element
     *
     * @return string
     */
    public function dataTable(array $arrHeader = null, array $arrValues, $strTableCssAddon = "", $bitWithTbody = false)
    {
        $strReturn = "";
        //The Table header & the templates
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_header".($bitWithTbody ? "_tbody" : ""));
        $strReturn .= $this->objTemplate->fillTemplate(array("cssaddon" => $strTableCssAddon), $strTemplateID);

        $strTemplateHeaderHeaderID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_head_header");
        $strTemplateHeaderContentID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_head");
        $strTemplateHeaderFooterID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_head_footer");
        $strTemplateContentHeaderID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_header".($bitWithTbody ? "_tbody" : ""));
        $strTemplateContentContentID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column");
        $strTemplateContentFooterID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_footer".($bitWithTbody ? "_tbody" : ""));
        //Iterating over the rows

        //Starting with the header, column by column
        if (is_array($arrHeader) && !empty($arrHeader)) {
            $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateHeaderHeaderID);

            foreach ($arrHeader as $strCssClass => $strHeader) {
                $strReturn .= $this->objTemplate->fillTemplate(array("value" => $strHeader, "class" => $strCssClass), $strTemplateHeaderContentID);
            }

            $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateHeaderFooterID);
        }

        //And the content, row by row, column by column
        foreach ($arrValues as $strKey => $arrValueRow) {
            $strReturn .= $this->objTemplate->fillTemplate(array("systemid" => $strKey), $strTemplateContentHeaderID);

            foreach ($arrValueRow as $strCssClass => $strValue) {
                $strReturn .= $this->objTemplate->fillTemplate(array("value" => $strValue, "class" => $strCssClass), $strTemplateContentContentID);
            }

            $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateContentFooterID);
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
     *
     * @return string
     */
    public function listButton($strContent)
    {
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
     * @param string $strElementName
     * @param string $strQuestion
     * @param string $strLinkHref
     *
     * @return string
     */
    public function listDeleteButton($strElementName, $strQuestion, $strLinkHref)
    {
        $strElementName = uniStrReplace(array('\''), array('\\\''), $strElementName);
        $strQuestion = uniStrReplace("%%element_name%%", htmlToString($strElementName, true), $strQuestion);

        //get the reload-url
        $objHistory = new class_history();
        $strParam = "";
        if (uniStrpos($strLinkHref, "javascript:") === false) {
            $strParam = "reloadUrl=".urlencode($objHistory->getAdminHistory());
            if (uniSubstr($strLinkHref, -4) == ".php" || uniSubstr($strLinkHref, -5) == ".html") {
                $strParam = "?".$strParam;
            }
            else {
                $strParam = "&".$strParam;
            }
        }

        //create the list-button and the js code to show the dialog
        $strButton = class_link::getLinkAdminManual(
            "href=\"#\" onclick=\"javascript:jsDialog_1.setTitle('".class_carrier::getInstance()->getObjLang()->getLang("dialog_deleteHeader", "system")."'); jsDialog_1.setContent('".$strQuestion."', '".class_carrier::getInstance()->getObjLang()->getLang("dialog_deleteButton", "system")."',  '".$strLinkHref.$strParam."'); jsDialog_1.init(); return false;\"",
            "",
            class_carrier::getInstance()->getObjLang()->getLang("commons_delete", "system"),
            "icon_delete"
        );

        return $this->listButton($strButton);
    }

    /**
     * Generates a button allowing to change the status of the record passed.
     * Therefore an ajax-method is called.
     *
     * @param class_model|string $objInstance or a systemid
     * @param bool $bitReload triggers a page-reload afterwards
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @throws class_exception
     * @return string
     */
    public function listStatusButton($objInstance, $bitReload = false, $strAltActive = "", $strAltInactive = "")
    {
        $strAltActive = $strAltActive != "" ? $strAltActive : class_carrier::getInstance()->getObjLang()->getLang("status_active", "system");
        $strAltInactive = $strAltInactive != "" ? $strAltInactive : class_carrier::getInstance()->getObjLang()->getLang("status_inactive", "system");

        if (is_object($objInstance) && $objInstance instanceof class_model) {
            $objRecord = $objInstance;
        }
        else if (validateSystemid($objInstance) && class_objectfactory::getInstance()->getObject($objInstance) !== null) {
            $objRecord = class_objectfactory::getInstance()->getObject($objInstance);
        }
        else {
            throw new class_exception("failed loading instance for ".(is_object($objInstance) ? " @ ".get_class($objInstance) : $objInstance), class_exception::$level_ERROR);
        }

        if ($objRecord->getIntRecordStatus() == 1) {
            $strLinkContent = class_adminskin_helper::getAdminImage("icon_enabled", $strAltActive);
        }
        else {
            $strLinkContent = class_adminskin_helper::getAdminImage("icon_disabled", $strAltInactive);
        }

        $strJavascript = "";

        //output texts and image paths only once
        if (class_carrier::getInstance()->getObjSession()->getSession("statusButton", class_session::$intScopeRequest) === false) {
            $strJavascript .= "<script type=\"text/javascript\">
                KAJONA.admin.ajax.setSystemStatusMessages.strActiveIcon = '".addslashes(class_adminskin_helper::getAdminImage("icon_enabled", $strAltActive))."';
                KAJONA.admin.ajax.setSystemStatusMessages.strInActiveIcon = '".addslashes(class_adminskin_helper::getAdminImage("icon_disabled", $strAltInactive))."';

            </script>";
            class_carrier::getInstance()->getObjSession()->setSession("statusButton", "true", class_session::$intScopeRequest);
        }

        $strButton = getLinkAdminManual(
            "href=\"javascript:KAJONA.admin.ajax.setSystemStatus('".$objRecord->getSystemid()."', ".($bitReload ? "true" : "false").");\"",
            $strLinkContent,
            "",
            "",
            "",
            "statusLink_".$objRecord->getSystemid(),
            false
        );

        return $this->listButton($strButton).$strJavascript;
    }

    /*"*****************************************************************************************************/
    // --- Misc-Elements ------------------------------------------------------------------------------------

    /**
     * Returns a warning box, e.g. shown before deleting a record
     *
     * @param string $strContent
     * @param string $strClass
     *
     * @return string
     */
    public function warningBox($strContent, $strClass = "alert-warning")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "warning_box");
        $arrTemplate = array();
        $arrTemplate["content"] = $strContent;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns the javascript code which renders a table of contents sub navigation under the main navigation. The
     * navigation contains all points which match the given selector
     *
     * @param string $strSelector
     *
     * @return string
     */
    public function getTableOfContents($strSelector)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "toc_navigation");
        $arrTemplate = array();
        $arrTemplate["selector"] = $strSelector;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a single TextRow
     *
     * @param string $strText
     * @param string $strClass
     *
     * @return string
     */
    public function getTextRow($strText, $strClass = "text")
    {
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
     *
     * @return mixed 0: The html-layout code
     *               1: The link to fold / unfold
     */
    public function getLayoutFolder($strContent, $strLinkText, $bitVisible = false, $strCallbackVisible = "", $strCallbackInvisible = "")
    {
        $arrReturn = array();
        $strID = str_replace(array(" ", "."), array("", ""), microtime());
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "layout_folder");
        $arrTemplate = array();
        $arrTemplate["id"] = $strID;
        $arrTemplate["content"] = $strContent;
        $arrTemplate["display"] = ($bitVisible ? "folderVisible" : "folderHidden");
        $arrReturn[0] = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
        $arrReturn[1] = "<a href=\"javascript:KAJONA.util.fold('".$strID."', ".($strCallbackVisible != "" ? $strCallbackVisible : "null").", ".($strCallbackInvisible != "" ? $strCallbackInvisible : "null").");\">".$strLinkText."</a>";
        return $arrReturn;
    }

    /**
     * Creates the mechanism to fold parts of the site / make them vivsible or invisible.
     * The image is prepended to the passed link-text.
     *
     * @param string $strContent
     * @param string $strLinkText Mouseovertext
     * @param string $strImageVisible clickable
     * @param string $strImageInvisible clickable
     * @param bool $bitVisible
     *
     * @return string
     *
     */
    public function getLayoutFolderPic($strContent, $strLinkText = "", $strImageVisible = "icon_folderOpen", $strImageInvisible = "icon_folderClosed", $bitVisible = true)
    {

        $strImageVisible = class_adminskin_helper::getAdminImage($strImageVisible);
        $strImageInvisible = class_adminskin_helper::getAdminImage($strImageInvisible);

        $strID = generateSystemid();
        $strLinkText = "<span id='{$strID}'>".($bitVisible ? $strImageVisible : $strImageInvisible)."</span> ".$strLinkText;

        $strImageVisible = addslashes(htmlentities($strImageVisible));
        $strImageInvisible = addslashes(htmlentities($strImageInvisible));

        $strVisibleCallback = <<<JS
            function() {  $('#{$strID}').html('{$strImageVisible}'); }
JS;

        $strInvisibleCallback = <<<JS
            function() {  $('#{$strID}').html('{$strImageInvisible}'); }
JS;

        return $this->getLayoutFolder($strContent, $strLinkText, $bitVisible, trim($strVisibleCallback), trim($strInvisibleCallback));
    }


    /**
     * Creates a fieldset to structure elements
     *
     * @param string $strTitle
     * @param string $strContent
     * @param string $strClass
     *
     * @return string
     */
    public function getFieldset($strTitle, $strContent, $strClass = "fieldset")
    {
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
     * Creates a tab-list out of the passed tabs.
     * The params is expected as
     * arraykey => tabname
     * arrayvalue => tabcontent
     *
     * If tabcontent is an url the content is loaded per ajax from this url. Url means the content string starts with
     * http:// or https://
     *
     * @param $arrTabs array(key => content)
     * @param bool $bitFullHeight whether the tab content should use full height
     *
     * @return string
     */
    public function getTabbedContent(array $arrTabs, $bitFullHeight = false)
    {

        $strWrapperID = $this->objTemplate->readTemplate("/elements.tpl", "tabbed_content_wrapper");
        $strHeaderID = $this->objTemplate->readTemplate("/elements.tpl", "tabbed_content_tabheader");
        $strContentID = $this->objTemplate->readTemplate("/elements.tpl", "tabbed_content_tabcontent");

        $strMainTabId = generateSystemid();
        $bitRemoteContent = false;

        $strTabs = "";
        $strTabContent = "";
        $strClassaddon = "active in ";
        foreach ($arrTabs as $strTitle => $strContent) {
            $strTabId = generateSystemid();
            // if content is an url enable ajax loading
            if (substr($strContent, 0, 7) == 'http://' || substr($strContent, 0, 8) == 'https://') {
                $strTabs .= $this->objTemplate->fillTemplate(array("tabid" => $strTabId, "tabtitle" => $strTitle, "href" => $strContent, "classaddon" => $strClassaddon), $strHeaderID);
                $strTabContent .= $this->objTemplate->fillTemplate(array("tabid" => $strTabId, "tabcontent" => "", "classaddon" => $strClassaddon . "contentLoading"), $strContentID);
                $bitRemoteContent = true;
            } else {
                $strTabs .= $this->objTemplate->fillTemplate(array("tabid" => $strTabId, "tabtitle" => $strTitle, "href" => "", "classaddon" => $strClassaddon), $strHeaderID);
                $strTabContent .= $this->objTemplate->fillTemplate(array("tabid" => $strTabId, "tabcontent" => $strContent, "classaddon" => $strClassaddon), $strContentID);
            }
            $strClassaddon = "";
        }

        $strHtml = $this->objTemplate->fillTemplate(array("id" => $strMainTabId, "tabheader" => $strTabs, "tabcontent" => $strTabContent, "classaddon" => ($bitFullHeight === true ? 'fullHeight' : '')), $strWrapperID);

        // add ajax loader if we have content which we need to fetch per ajax
        if ($bitRemoteContent) {
            $strHtml.= <<<HTML
<script type="text/javascript">
$('#{$strMainTabId} > li > a[data-href!=""]').on('click', function(e){
    KAJONA.admin.forms.loadTab($(e.target).data('target').substr(1), $(e.target).data('href'));
});

$(document).ready(function(){
    var el = $('#{$strMainTabId} > li.active > a[data-href!=""]');
    if (el.length > 0) {
        KAJONA.admin.forms.loadTab(el.data('target').substr(1), el.data('href'));
    }
});
</script>
HTML;
        }

        return $strHtml;
    }

    /**
     * Container for graphs, e.g. used by stats.
     *
     * @param string $strImgSrc
     *
     * @return string
     */
    public function getGraphContainer($strImgSrc)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "graph_container");
        $arrContent = array();
        $arrContent["imgsrc"] = $strImgSrc;
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Includes an IFrame with the given URL
     *
     * @param string $strIFrameSrc
     * @param string $strIframeId
     *
     * @return string
     * @deprecated
     */
    public function getIFrame($strIFrameSrc, $strIframeId = "")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "iframe_container");
        $arrContent = array();
        $arrContent["iframesrc"] = $strIFrameSrc;
        $arrContent["iframeid"] = $strIframeId !== "" ? $strIframeId : generateSystemid();
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Renders the login-status and corresponding links
     *
     * @param array $arrElements
     *
     * @return string
     * @since 3.4.0
     */
    public function getLoginStatus(array $arrElements)
    {
        //Loading a small login-form
        $arrElements["renderTags"] = class_module_system_module::getModuleByName("tags") != null && class_module_system_module::getModuleByName("tags")->rightView() ? "true" : "false";
        $arrElements["renderMessages"] = class_module_system_module::getModuleByName("messaging") != null && class_module_system_module::getModuleByName("messaging")->rightView() ? "true" : "false";
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "logout_form");
        $strReturn = $this->objTemplate->fillTemplate($arrElements, $strTemplateID);
        return $strReturn;
    }

    /*"*****************************************************************************************************/
    // --- Navigation-Elements ------------------------------------------------------------------------------

    /**
     * The v4 way of generating a backend-navigation.
     *
     * @param string $strCurrentModule
     *
     * @return string
     */
    public function getAdminSitemap($strCurrentModule = "")
    {
        $strWrapperID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_wrapper");
        $strModuleID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_module_wrapper");
        $strModuleActiveID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_module_wrapper_active");
        $strActionID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_action_entry");
        $strDividerID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_divider_entry");
        $strModules = "";

        if ($strCurrentModule == "elemente") {
            $strCurrentModule = "pages";
        }

        $arrModules = class_module_system_module::getModulesInNaviAsArray(class_module_system_aspect::getCurrentAspectId());

        /** @var $arrNaviInstances class_module_system_module[] */
        $arrNaviInstances = array();
        foreach ($arrModules as $arrModule) {
            $objModule = class_module_system_module::getModuleBySystemid($arrModule["module_id"]);
            if ($objModule->rightView()) {
                $arrNaviInstances[] = $objModule;
            }
        }


        foreach ($arrNaviInstances as $objOneInstance) {

            $arrActions = class_admin_helper::getModuleActionNaviHelper($objOneInstance);

            $strActions = "";
            foreach ($arrActions as $strOneAction) {
                if (trim($strOneAction) != "") {
                    $arrActionEntries = array(
                        "action" => $strOneAction
                    );
                    $strActions .= $this->objTemplate->fillTemplate($arrActionEntries, $strActionID);
                }
                else {
                    $strActions .= $this->objTemplate->fillTemplate(array(), $strDividerID);
                }
            }


            $arrModuleLevel = array(
                "module"      => class_link::getLinkAdmin($objOneInstance->getStrName(), "", "", class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $objOneInstance->getStrName())),
                "actions"     => $strActions,
                "systemid"    => $objOneInstance->getSystemid(),
                "moduleTitle" => $objOneInstance->getStrName(),
                "moduleName"  => class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $objOneInstance->getStrName()),
                "moduleHref"  => class_link::getLinkAdminHref($objOneInstance->getStrName(), "")
            );

            if ($strCurrentModule == $objOneInstance->getStrName()) {
                $strModules .= $this->objTemplate->fillTemplate($arrModuleLevel, $strModuleActiveID);
            }
            else {
                $strModules .= $this->objTemplate->fillTemplate($arrModuleLevel, $strModuleID);
            }

        }

        return $this->objTemplate->fillTemplate(array("level" => $strModules), $strWrapperID);
    }

    /*"*****************************************************************************************************/
    // --- Path Navigation ----------------------------------------------------------------------------------

    /**
     * Generates the layout for a small navigation
     *
     * @param mixed $arrEntries
     *
     * @return string
     */
    public function getPathNavigation(array $arrEntries)
    {
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
     *
     * @return string
     */
    public function getContentToolbar(array $arrEntries, $intActiveEntry = -1)
    {
        $strTemplateWrapperID = $this->objTemplate->readTemplate("/elements.tpl", "contentToolbar_wrapper");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contentToolbar_entry");
        $strTemplateActiveEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contentToolbar_entry_active");
        $strRows = "";
        foreach ($arrEntries as $intI => $strOneEntry) {
            if ($intI == $intActiveEntry) {
                $strRows .= $this->objTemplate->fillTemplate(array("entry" => $strOneEntry), $strTemplateActiveEntryID);
            }
            else {
                $strRows .= $this->objTemplate->fillTemplate(array("entry" => $strOneEntry), $strTemplateEntryID);
            }
        }
        return $this->objTemplate->fillTemplate(array("entries" => $strRows), $strTemplateWrapperID);

    }

    /**
     * A list of action icons for the current record. In most cases the same icons as when rendering the list.
     *
     * @param $strContent
     *
     * @return string
     */
    public function getContentActionToolbar($strContent)
    {
        return $this->objTemplate->fillTemplate(array("content" => $strContent), $this->objTemplate->readTemplate("/elements.tpl", "contentActionToolbar_wrapper"));
    }

    /*"*****************************************************************************************************/
    // --- Validation Errors --------------------------------------------------------------------------------

    /**
     * Generates a list of errors found by the form-validation
     *
     * @param class_admin_controller|class_admin_formgenerator $objCalling
     * @param string $strTargetAction
     *
     * @return string
     */
    public function getValidationErrors($objCalling, $strTargetAction = null)
    {
        $strRendercode = "";
        //render mandatory fields?
        if (method_exists($objCalling, "getRequiredFields") && is_callable(array($objCalling, "getRequiredFields"))) {
            if ($objCalling instanceof class_admin_formgenerator) {
                $arrFields = $objCalling->getRequiredFields();
            }
            else {
                $strTempAction = $objCalling->getAction();
                $objCalling->setAction($strTargetAction);
                $arrFields = $objCalling->getRequiredFields();
                $objCalling->setAction($strTempAction);
            }

            if (count($arrFields) > 0) {

                $strRendercode .= "<script type=\"text/javascript\">$(document).ready(function () {
                        KAJONA.admin.forms.renderMandatoryFields([";

                foreach ($arrFields as $strName => $strType) {
                    $strRendercode .= "[ '".$strName."', '".$strType."' ], ";
                }
                $strRendercode .= " [] ]); });</script>";
            }
        }

        $arrErrors = method_exists($objCalling, "getValidationErrors") ? $objCalling->getValidationErrors() : array();
        if (count($arrErrors) == 0) {
            return $strRendercode;
        }

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "error_container");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "error_row");
        $strRows = "";
        $strRendercode .= "<script type=\"text/javascript\">$(document).ready(function () {
            KAJONA.admin.forms.renderMissingMandatoryFields([";

        foreach ($arrErrors as $strKey => $arrOneErrors) {
            foreach ($arrOneErrors as $strOneError) {
                $strRows .= $this->objTemplate->fillTemplate(array("field_errortext" => $strOneError), $strTemplateRowID);
                $strRendercode .= "[ '".$strKey."' ], ";
            }
        }
        $strRendercode .= " [] ]); });</script>";
        $arrTemplate = array();
        $arrTemplate["errorrows"] = $strRows;
        $arrTemplate["errorintro"] = class_lang::getInstance()->getLang("errorintro", "system");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID).$strRendercode;
    }


    /*"*****************************************************************************************************/
    // --- Pre-formatted ------------------------------------------------------------------------------------


    /**
     * Returns a simple <pre>-Element to display pre-formatted text such as logfiles
     *
     * @param array $arrLines
     * @param int $nrRows number of rows to display
     *
     * @return string
     */
    public function getPreformatted($arrLines, $nrRows = 0, $bitHighlightKeywords = true)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "preformatted");
        $strRows = "";
        $intI = 0;
        foreach ($arrLines as $strOneLine) {
            if ($nrRows != 0 && $intI++ > $nrRows) {
                break;
            }
            $strOneLine = str_replace(array("<pre>", "</pre>", "\n"), array(" ", " ", "\r\n"), $strOneLine);

            $strOneLine = htmlToString($strOneLine, true);
            $strOneLine = uniStrReplace(
                array("INFO", "ERROR", "WARNING"),
                array(
                    "<span style=\"color: green\">INFO</span>",
                    "<span style=\"color: red\">ERROR</span>",
                    "<span style=\"color: orange\">WARNING</span>"
                ),
                $strOneLine
            );
            $strRows .= $strOneLine;
        }


        return $this->objTemplate->fillTemplate(array("pretext" => $strRows), $strTemplateID);
    }

    /*"*****************************************************************************************************/
    // --- Language handling --------------------------------------------------------------------------------

    /**
     * Creates the sourrounding code of a language switch, places the buttons
     *
     * @param string $strLanguageButtons
     * @param $strOnChangeHandler
     *
     * @return string
     */
    public function getLanguageSwitch($strLanguageButtons, $strOnChangeHandler)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "language_switch");
        $arrTemplate = array();
        $arrTemplate["languagebuttons"] = $strLanguageButtons;
        $arrTemplate["onchangehandler"] = $strOnChangeHandler;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Creates the code for one button for a specified language, part of a language switch
     *
     * @param string $strKey
     * @param string $strLanguageName The full name of the language
     * @param bool $bitActive
     *
     * @return string
     */
    public function getLanguageButton($strKey, $strLanguageName, $bitActive = false)
    {
        //active language?
        if ($bitActive) {
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "language_switch_button_active");
        }
        else {
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "language_switch_button");
        }
        $arrTemplate = array();
        $arrTemplate["languageKey"] = $strKey;
        $arrTemplate["languageName"] = $strLanguageName;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /*"*****************************************************************************************************/
    // --- Pageview mechanism ------------------------------------------------------------------------------


    /**
     * Creates a pageview
     *
     * @param class_array_section_iterator $objArraySectionIterator
     * @param string $strModule
     * @param string $strAction
     * @param string $strLinkAdd
     *
     * @return string the pageview code
     * @since 4.6
     */
    public function getPageview($objArraySectionIterator, $strModule, $strAction, $strLinkAdd = "")
    {

        $intCurrentpage = $objArraySectionIterator->getPageNumber();
        $intNrOfPages = $objArraySectionIterator->getNrOfPages();
        $intNrOfElements = $objArraySectionIterator->getNumberOfElements();

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
        for ($intI = 1; $intI <= $intNrOfPages; $intI++) {
            $bitDisplay = false;
            if ($intCounter2 <= 2) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intNrOfPages - 1)) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intCurrentpage - 2) && $intCounter2 <= ($intCurrentpage + 2)) {
                $bitDisplay = true;
            }


            if ($bitDisplay) {
                $arrLinkTemplate = array();
                $arrLinkTemplate["href"] = class_link::getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".$intI);
                $arrLinkTemplate["pageNr"] = $intI;

                if ($intI == $intCurrentpage) {
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemActiveID);
                }
                else {
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemID);
                }
            }
            $intCounter2++;
        }
        $arrTemplate["pageList"] = $this->objTemplate->fillTemplate(array("pageListItems" => $strListItems), $strTemplateListID);
        $arrTemplate["nrOfElementsText"] = class_carrier::getInstance()->getObjLang()->getLang("pageview_total", "system");
        $arrTemplate["nrOfElements"] = $intNrOfElements;
        if ($intCurrentpage < $intNrOfPages) {
            $arrTemplate["linkForward"] = $this->objTemplate->fillTemplate(
                array(
                    "linkText" => class_carrier::getInstance()->getObjLang()->getLang("pageview_forward", "system"),
                    "href"     => class_link::getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage + 1))
                ),
                $strTemplateForwardID
            );
        }
        if ($intCurrentpage > 1) {
            $arrTemplate["linkBackward"] = $this->objTemplate->fillTemplate(
                array(
                    "linkText" => class_carrier::getInstance()->getObjLang()->getLang("commons_back", "commons"),
                    "href"     => class_link::getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage - 1))
                ),
                $strTemplateBackwardID
            );
        }

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateBodyID);
    }


    /**
     * Creates a pageview
     *
     * @param class_array_section_iterator $objArraySectionIterator
     * @param string $strModule
     * @param string $strAction
     * @param string $strLinkAdd
     *
     * @return mixed a two-dimensional array: ["elements"] and ["pageview"]
     * @since 3.3.0
     *
     * @deprecated use getPageview instead
     */
    public function getSimplePageview($objArraySectionIterator, $strModule, $strAction, $strLinkAdd = "")
    {
        $arrReturn = array();

        $intCurrentpage = $objArraySectionIterator->getPageNumber();
        $intNrOfPages = $objArraySectionIterator->getNrOfPages();
        $intNrOfElements = $objArraySectionIterator->getNumberOfElements();

        $arrReturn["elements"] = array();

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
        for ($intI = 1; $intI <= $intNrOfPages; $intI++) {
            $bitDisplay = false;
            if ($intCounter2 <= 2) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intNrOfPages - 1)) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intCurrentpage - 2) && $intCounter2 <= ($intCurrentpage + 2)) {
                $bitDisplay = true;
            }


            if ($bitDisplay) {
                $arrLinkTemplate = array();
                $arrLinkTemplate["href"] = class_link::getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".$intI);
                $arrLinkTemplate["pageNr"] = $intI;

                if ($intI == $intCurrentpage) {
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemActiveID);
                }
                else {
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemID);
                }
            }
            $intCounter2++;
        }
        $arrTemplate["pageList"] = $this->objTemplate->fillTemplate(array("pageListItems" => $strListItems), $strTemplateListID);
        $arrTemplate["nrOfElementsText"] = class_carrier::getInstance()->getObjLang()->getLang("pageview_total", "system");
        $arrTemplate["nrOfElements"] = $intNrOfElements;
        if ($intCurrentpage < $intNrOfPages) {
            $arrTemplate["linkForward"] = $this->objTemplate->fillTemplate(
                array(
                    "linkText" => class_carrier::getInstance()->getObjLang()->getLang("pageview_forward", "system"),
                    "href"     => class_link::getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage + 1))
                ),
                $strTemplateForwardID
            );
        }
        if ($intCurrentpage > 1) {
            $arrTemplate["linkBackward"] = $this->objTemplate->fillTemplate(
                array(
                    "linkText" => class_carrier::getInstance()->getObjLang()->getLang("commons_back", "commons"),
                    "href"     => class_link::getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage - 1))
                ),
                $strTemplateBackwardID
            );
        }


        $arrReturn["pageview"] = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateBodyID);
        $arrReturn["elements"] = $objArraySectionIterator->getArrayExtended(true);
        return $arrReturn;
    }


    /*"*****************************************************************************************************/
    // --- Adminwidget / Dashboard --------------------------------------------------------------------------


    public function getMainDashboard(array $arrColumns)
    {
        return $this->objTemplate->fillTemplate(
            array("entries" => implode("", $arrColumns)),
            $this->objTemplate->readTemplate("/elements.tpl", "dashboard_wrapper")
        );
    }

    /**
     * Generates the header for a column on the dashboard.
     * Inits the ajax-componentes for this list
     *
     * @param string $strColumnId
     *
     * @return string
     */
    public function getDashboardColumnHeader($strColumnId)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dashboard_column_header");
        return $this->objTemplate->fillTemplate(array("column_id" => $strColumnId), $strTemplateID);
    }

    /**
     * The footer of a dashboard column.
     *
     * @return string
     */
    public function getDashboardColumnFooter()
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dashboard_column_footer");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * The widget-enclose is the code-fragment to be built around the widget itself.
     * Used to handle the widget on the current column.
     *
     * @param string $strDashboardEntryId
     * @param string $strWidgetContent
     *
     * @return string
     */
    public function getDashboardWidgetEncloser($strDashboardEntryId, $strWidgetContent)
    {
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
     * @param string $strWidgetNameAdditionalContent
     * @param string $strEditLink
     * @param string $strDeleteLink
     * @param string $strLayoutSection
     *
     * @return string
     */
    public function getAdminwidget($strSystemid, $strName, $strWidgetNameAdditionalContent, $strEditLink = "", $strDeleteLink = "", $strLayoutSection = "adminwidget_widget")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", $strLayoutSection);
        $arrTemplate = array();
        $arrTemplate["widget_name"] = $strName;
        $arrTemplate["widget_name_additional_content"] = $strWidgetNameAdditionalContent;
        $arrTemplate["widget_id"] = $strSystemid;
        $arrTemplate["widget_edit"] = $strEditLink;
        $arrTemplate["widget_delete"] = $strDeleteLink;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Generates a text-row in a widget
     *
     * @param string $strText
     *
     * @return string
     */
    public function adminwidgetText($strText)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "adminwidget_text");
        return $this->objTemplate->fillTemplate(array("text" => $strText), $strTemplateID);
    }

    /**
     * Generate a separator / divider in a widget
     *
     * @return string
     */
    public function adminwidgetSeparator()
    {
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
     *
     * @return string
     *
     * @deprecated no longer required, available by the skin by default
     */
    public function jsDialog($intDialogType)
    {
        return "";
    }


    //--- misc ----------------------------------------------------------------------------------------------

    /**
     * Sets the users browser focus to the element with the given id
     *
     * @param string $strElementId
     *
     * @return string
     */
    public function setBrowserFocus($strElementId)
    {
        $strReturn = "
            <script type=\"text/javascript\">
                KAJONA.util.setBrowserFocus(\"".$strElementId."\");
            </script>";
        return $strReturn;
    }

    /**
     * Create a tree-view UI-element.
     * The nodes are loaded via AJAX by calling the url passed as the first arg.
     * The optional third param is an ordered list of systemid identifying the nodes to expand initially.
     * The tree may be wrapped into a two-column view.
     *
     * @param string $strLoadNodeDataUrl , systemid is appended automatically
     * @param string $strRootNodeSystemid
     * @param array $arrNodesToExpand
     * @param string $strSideContent
     *
     * @return string
     */
    public function getTreeview($strLoadNodeDataUrl, $strRootNodeSystemid = "", $arrNodesToExpand = array(), $strSideContent = "")
    {
        $arrTemplate = array();
        $arrTemplate["sideContent"] = $strSideContent;
        $arrTemplate["treeContent"] = $this->getTree($strLoadNodeDataUrl, $strRootNodeSystemid, $arrNodesToExpand);
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "treeview");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Creates a tree-view with an button which can be used inside an mode dialog
     *
     * @param $strLoadNodeDataUrl
     * @param string $strRootNodeSystemid
     * @param array $arrNodesToExpand
     *
     * @return string
     * @throws class_exception
     */
    public function getTreeModalCheckbox($strLoadNodeDataUrl, $strRootNodeSystemid = "", $arrNodesToExpand = array())
    {
        $arrTemplate = array();
        $arrTemplate["treeContent"] = $this->getTreeCheckbox($strLoadNodeDataUrl, $strRootNodeSystemid, $arrNodesToExpand);
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "treeview_modal");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Create a tree-view UI-element.
     * The nodes are loaded via AJAX by calling the url passed as the first arg.
     * The optional third param is an ordered list of systemid identifying the nodes to expand initially.
     * Renders only the tree, so no other content
     *
     * @param string $strLoadNodeDataUrl , systemid is appended automatically
     * @param string $strRootNodeSystemid
     * @param array $arrNodesToExpand
     * @param bool $bitOrderingEnabled
     * @param bool $bitHierachicalSortEnabled
     *
     *
     * @return string
     */
    public function getTree($strLoadNodeDataUrl, $strRootNodeSystemid = "", $arrNodesToExpand = array(), $bitOrderingEnabled = false, $bitHierachicalSortEnabled = false)
    {
        $arrTemplate = array();
        $arrTemplate["rootNodeSystemid"] = $strRootNodeSystemid;
        $arrTemplate["loadNodeDataUrl"] = $strLoadNodeDataUrl;
        $arrTemplate["treeId"] = generateSystemid();
        $arrTemplate["orderingEnabled"] = $bitOrderingEnabled ? "true" : "false";
        $arrTemplate["hierarchialSortEnabled"] = $bitHierachicalSortEnabled ? "true" : "false";
        $arrTemplate["treeviewExpanders"] = "";
        for ($intI = 0; $intI < count($arrNodesToExpand); $intI++) {
            $arrTemplate["treeviewExpanders"] .= "\"".$arrNodesToExpand[$intI]."\"";
            if ($intI < count($arrNodesToExpand) - 1) {
                $arrTemplate["treeviewExpanders"] .= ",";
            }
        }
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tree");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns an checkbox tree-view
     *
     * @param $strLoadNodeDataUrl
     * @param string $strRootNodeSystemid
     * @param array $arrNodesToExpand
     *
     * @return string
     * @throws class_exception
     */
    public function getTreeCheckbox($strLoadNodeDataUrl, $strRootNodeSystemid = "", $arrNodesToExpand = array())
    {
        $arrTemplate = array();
        $arrTemplate["rootNodeSystemid"] = $strRootNodeSystemid;
        $arrTemplate["loadNodeDataUrl"] = $strLoadNodeDataUrl;
        $arrTemplate["treeId"] = generateSystemid();
        $arrTemplate["treeviewExpanders"] = "";
        for ($intI = 0; $intI < count($arrNodesToExpand); $intI++) {
            $arrTemplate["treeviewExpanders"] .= "\"".$arrNodesToExpand[$intI]."\"";
            if ($intI < count($arrNodesToExpand) - 1) {
                $arrTemplate["treeviewExpanders"] .= ",";
            }
        }
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tree_checkbox");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Renderes the quickhelp-button and the quickhelp-text passed
     *
     * @param string $strText
     *
     * @return string
     */
    public function getQuickhelp($strText)
    {
        $strReturn = "";
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "quickhelp");
        $arrTemplate = array();
        $arrTemplate["title"] = class_carrier::getInstance()->getObjLang()->getLang("quickhelp_title", "system");
        $arrTemplate["text"] = uniStrReplace(array("\r", "\n"), "", addslashes($strText));
        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

        //and the button
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "quickhelp_button");
        $arrTemplate = array();
        $arrTemplate["text"] = class_carrier::getInstance()->getObjLang()->getLang("quickhelp_title", "system");
        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

        return $strReturn;
    }

    /**
     * Generates the wrapper required to render the list of tags.
     *
     * @param string $strWrapperid
     * @param string $strTargetsystemid
     * @param string $strAttribute
     *
     * @return string
     */
    public function getTaglistWrapper($strWrapperid, $strTargetsystemid, $strAttribute)
    {
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
     * @param class_module_tags_tag $objTag
     * @param string $strTargetid
     * @param string $strAttribute
     *
     * @return string
     */
    public function getTagEntry(class_module_tags_tag $objTag, $strTargetid, $strAttribute)
    {

        if (class_carrier::getInstance()->getParam("delete") != "false") {
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tags_tag_delete");
        }
        else {
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tags_tag");
        }

        $strFavorite = "";
        if ($objTag->rightRight1()) {

            $strJs = "<script type='text/javascript'>KAJONA.admin.loader.loadFile('".class_resourceloader::getInstance()->getCorePathForModule("module_tags")."/module_tags/admin/scripts/tags.js', function() {
                    KAJONA.admin.tags.createFavoriteEnabledIcon = '".addslashes(class_adminskin_helper::getAdminImage("icon_favorite", class_carrier::getInstance()->getObjLang()->getLang("tag_favorite_remove", "tags")))."';
                    KAJONA.admin.tags.createFavoriteDisabledIcon = '".addslashes(class_adminskin_helper::getAdminImage("icon_favoriteDisabled", class_carrier::getInstance()->getObjLang()->getLang("tag_favorite_add", "tags")))."';
                });</script>";

            $strImage = class_module_tags_favorite::getAllFavoritesForUserAndTag(class_carrier::getInstance()->getObjSession()->getUserID(), $objTag->getSystemid()) != null ?
                class_adminskin_helper::getAdminImage("icon_favorite", class_carrier::getInstance()->getObjLang()->getLang("tag_favorite_remove", "tags")) :
                class_adminskin_helper::getAdminImage("icon_favoriteDisabled", class_carrier::getInstance()->getObjLang()->getLang("tag_favorite_add", "tags"));

            $strFavorite = $strJs."<a href=\"#\" onclick=\"KAJONA.admin.tags.createFavorite('".$objTag->getSystemid()."', this); return false;\">".$strImage."</a>";
        }

        $arrTemplate = array();
        $arrTemplate["tagname"] = $objTag->getStrDisplayName();
        $arrTemplate["strTagId"] = $objTag->getSystemid();
        $arrTemplate["strTargetSystemid"] = $strTargetid;
        $arrTemplate["strAttribute"] = $strAttribute;
        $arrTemplate["strFavorite"] = $strFavorite;
        $arrTemplate["strDelete"] = class_adminskin_helper::getAdminImage("icon_delete", class_carrier::getInstance()->getObjLang()->getLang("commons_delete", "tags"));;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Returns a regular text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strClass
     *
     * @return string
     */
    public function formInputTagSelector($strName, $strTitle = "", $strClass = "inputText")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_tagselector");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;

        $arrTemplate["ajaxScript"] = "
	        <script type=\"text/javascript\">
                    $(function() {
                        function split( val ) {
                            return val.split( /,\s*/ );
                        }

                        function extractLast( term ) {
                            return split( term ).pop();
                        }

                        var objConfig = new KAJONA.v4skin.defaultAutoComplete();
                        objConfig.source = function(request, response) {
                            $.ajax({
                                url: '".getLinkAdminXml("tags", "getTagsByFilter")."',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    filter:  extractLast( request.term )
                                },
                                success: response
                            });
                        };

                        objConfig.select = function( event, ui ) {
                            var terms = split( this.value );
                            terms.pop();
                            terms.push( ui.item.value );
                            terms.push( '' );
                            this.value = terms.join( ', ' );
                            return false;
                        };

                        KAJONA.admin.".$strName." = $('#".uniStrReplace(array("[", "]"), array("\\\[", "\\\]"), $strName)."').autocomplete(objConfig);
                    });
	        </script>
        ";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    /**
     * Renders the list of aspects available
     *
     * @param string $strLastModule
     * @param string $strLastAction
     * @param string $strLastSystemid
     *
     * @return string
     * @todo param handling? remove params?
     */
    public function getAspectChooser($strLastModule, $strLastAction, $strLastSystemid)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "aspect_chooser");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "aspect_chooser_entry");

        $arrTemplate = array();
        $arrTemplate["options"] = "";

        //process rows
        $strCurrentId = class_module_system_aspect::getCurrentAspectId();
        $arrAspects = class_module_system_aspect::getActiveObjectList();

        $intNrOfAspects = 0;
        foreach ($arrAspects as $objSingleAspect) {
            if ($objSingleAspect->rightView()) {
                $arrSubtemplate = array();
                //start on dashboard since the current module may not be visible in another aspect
                $arrSubtemplate["value"] = getLinkAdminHref("dashboard", "", "&aspect=".$objSingleAspect->getSystemid());
                $arrSubtemplate["name"] = $objSingleAspect->getStrDisplayName();
                $arrSubtemplate["selected"] = $strCurrentId == $objSingleAspect->getSystemid() ? "selected=\"selected\"" : "";

                $arrTemplate["options"] .= $this->objTemplate->fillTemplate($arrSubtemplate, $strTemplateRowID);
                $intNrOfAspects++;
            }
        }

        if ($arrTemplate["options"] == "" || $intNrOfAspects < 2) {
            return "";
        }

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Creates a tooltip shown on hovering the passed text.
     * If both are the same, text and tooltip, only the plain text is returned.
     *
     * @param string $strText
     * @param string $strTooltip
     *
     * @return string
     * @since 3.4.0
     */
    public function getTooltipText($strText, $strTooltip)
    {
        if ($strText == $strTooltip) {
            return $strText;
        }

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tooltip_text");
        return $this->objTemplate->fillTemplate(array("text" => $strText, "tooltip" => $strTooltip), $strTemplateID);
    }

    // --- Calendar Fields ----------------------------------------------------------------------------------

    /**
     * Renders a legend below the current calendar in order to illustrate the different event-types.
     *
     * @param array $arrEntries
     *
     * @return string
     */
    public function getCalendarLegend(array $arrEntries)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_legend");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_legend_entry");

        $strEntries = "";
        foreach ($arrEntries as $strName => $strClass) {
            $strEntries .= $this->objTemplate->fillTemplate(array("name" => $strName, "class" => $strClass), $strTemplateEntryID);
        }

        return $this->objTemplate->fillTemplate(array("entries" => $strEntries), $strTemplateID);
    }

    /**
     * Renders a legend below the current calendar in order to illustrate the different event-types.
     *
     * @param array $arrEntries
     *
     * @return string
     */
    public function getCalendarFilter(array $arrEntries)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_filter");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_filter_entry");

        $strEntries = "";
        foreach ($arrEntries as $strId => $strName) {
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
     *
     * @return string
     * @since 3.4
     */
    public function getCalendarPager($strBackwards, $strCenter, $strForwards)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_pager");
        return $this->objTemplate->fillTemplate(array("backwards" => $strBackwards, "forwards" => $strForwards, "center" => $strCenter), $strTemplateID);
    }

    /**
     * Renders a container used to place the calender via ajax into.
     *
     * @param string $strContainerId
     *
     * @return string
     * @since 3.4
     */
    public function getCalendarContainer($strContainerId)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_container");
        return $this->objTemplate->fillTemplate(array("containerid" => $strContainerId), $strTemplateID);
    }

    /**
     * Creates the wrapper to embedd the calendar.
     *
     * @param string $strContent
     *
     * @return string
     * @since 3.4
     */
    public function getCalendarWrapper($strContent)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_wrapper");
        return $this->objTemplate->fillTemplate(array("content" => $strContent), $strTemplateID);
    }

    /**
     * Renders the header-row of the calendar. In general those are the days.
     *
     * @param array $arrHeader
     *
     * @return string
     * @since 3.4
     */
    public function getCalendarHeaderRow(array $arrHeader)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_header_row");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_header_entry");

        $strEntries = "";
        foreach ($arrHeader as $strOneHeader) {
            $strEntries .= $this->objTemplate->fillTemplate(array("name" => $strOneHeader), $strTemplateEntryID);
        }

        return $this->objTemplate->fillTemplate(array("entries" => $strEntries), $strTemplateID);
    }

    /**
     * Renders a complete row of days.
     *
     * @param string $strContent
     *
     * @return string
     * @since 3.4
     */
    public function getCalendarRow($strContent)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_row");
        return $this->objTemplate->fillTemplate(array("entries" => $strContent), $strTemplateID);
    }

    /**
     * Renders a single entry within the calendar. In most cases this is a single day.
     *
     * @param string $strContent
     * @param string $strDate
     * @param string $strClass
     *
     * @return string
     * @since 3.4
     */
    public function getCalendarEntry($strContent, $strDate, $strClass = "calendarEntry")
    {
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
     *
     * @return string
     * @since 3.4
     */
    public function getCalendarEvent($strContent, $strId = "", $strHighlightId = "", $strClass = "calendarEvent")
    {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_event");
        if ($strId == "") {
            $strId = generateSystemid();
        }
        return $this->objTemplate->fillTemplate(array("content" => $strContent, "class" => $strClass, "systemid" => $strId, "highlightid" => $strHighlightId), $strTemplateID);
    }

    //---contect menues ---------------------------------------------------------------------------------

    /**
     * Creates the markup to render a js-based contex-menu.
     * Each entry is an array with the keys
     *   array("name" => "xx", "link" => "xx", "submenu" => array());
     * The support of submenus depends on the current implementation, so may not be present everywhere!
     *
     * @since 3.4.1
     *
     * @param string $strIdentifier
     * @param string[] $arrEntries
     *
     * @return string
     */
    public function registerMenu($strIdentifier, array $arrEntries)
    {
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_entry");
        $strTemplateFullEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_entry_full");
        $strDividerTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_divider_entry");
        $strSubmenuTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_submenucontainer_entry");
        $strSubmenuTemplateFullEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_submenucontainer_entry_full");
        $strEntries = "";
        foreach ($arrEntries as $arrOneEntry) {


            if (!isset($arrOneEntry["link"])) {
                $arrOneEntry["link"] = "";
            }
            if (!isset($arrOneEntry["name"])) {
                $arrOneEntry["name"] = "";
            }
            if (!isset($arrOneEntry["onclick"])) {
                $arrOneEntry["onclick"] = "";
            }
            if (!isset($arrOneEntry["fullentry"])) {
                $arrOneEntry["fullentry"] = "";
            }

            $arrTemplate = array(
                "elementName"          => $arrOneEntry["name"],
                "elementAction"        => $arrOneEntry["onclick"],
                "elementLink"          => $arrOneEntry["link"],
                "elementActionEscaped" => uniStrReplace("'", "\'", $arrOneEntry["onclick"]),
                "elementFullEntry"     => $arrOneEntry["fullentry"]
            );

            if ($arrTemplate["elementFullEntry"] != "") {
                $strCurTemplate = $strTemplateFullEntryID;
            }
            else {
                $strCurTemplate = $strTemplateEntryID;
            }


            if (isset($arrOneEntry["submenu"]) && count($arrOneEntry["submenu"]) > 0) {
                $strSubmenu = "";
                foreach ($arrOneEntry["submenu"] as $arrOneSubmenu) {
                    $strCurSubTemplate = $strTemplateEntryID;

                    if (!isset($arrOneEntry["link"])) {
                        $arrOneEntry["link"] = "";
                    }
                    if (!isset($arrOneEntry["name"])) {
                        $arrOneEntry["name"] = "";
                    }
                    if (!isset($arrOneEntry["onclick"])) {
                        $arrOneEntry["onclick"] = "";
                    }
                    if (!isset($arrOneEntry["fullentry"])) {
                        $arrOneEntry["fullentry"] = "";
                    }

                    if ($arrOneSubmenu["name"] == "") {
                        $arrSubTemplate = array();
                        $strCurSubTemplate = $strDividerTemplateEntryID;
                    }
                    else {
                        $arrSubTemplate = array(
                            "elementName"          => $arrOneSubmenu["name"],
                            "elementAction"        => $arrOneSubmenu["onclick"],
                            "elementLink"          => $arrOneSubmenu["link"],
                            "elementActionEscaped" => uniStrReplace("'", "\'", $arrOneSubmenu["onclick"]),
                            "elementFullEntry"     => $arrOneEntry["fullentry"]
                        );

                        if ($arrSubTemplate["elementFullEntry"] != "") {
                            $strCurSubTemplate = $strTemplateFullEntryID;
                        }

                    }

                    $strSubmenu .= $this->objTemplate->fillTemplate($arrSubTemplate, $strCurSubTemplate);
                }
                $arrTemplate["entries"] = $strSubmenu;


                if ($arrTemplate["elementFullEntry"] != "") {
                    $strCurTemplate = $strSubmenuTemplateFullEntryID;
                }
                else {
                    $strCurTemplate = $strSubmenuTemplateEntryID;
                }
            }


            $strEntries .= $this->objTemplate->fillTemplate($arrTemplate, $strCurTemplate);
        }

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_wrapper");
        $arrTemplate = array();
        $arrTemplate["id"] = $strIdentifier;
        $arrTemplate["entries"] = uniSubstr($strEntries, 0, -1);
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }
}
