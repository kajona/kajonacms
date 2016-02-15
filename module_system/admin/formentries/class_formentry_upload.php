<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_formentry_upload.php 6322 2014-01-02 08:31:49Z sidler $                               *
********************************************************************************************************/

/**
 * @author stefan.meyer1@yahoo.de
 * @since 4.4
 * @package module_formgenerator
 */
class class_formentry_upload extends class_formentry_base implements interface_formentry, interface_formentry_printable {

    protected $arrFile;

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null)
    {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new class_dummy_validator());
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField()
    {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $strData = $this->getStrValue();

        if (!is_array($strData)) {
            if (validateSystemid($strData)) {
                $strData = new class_module_mediamanager_file($strData);
            } else {
                $strData = json_decode($strData, true);
            }
        }

        if ($strData instanceof class_module_mediamanager_file) {
            $strFile = $strData->getStrDisplayName();
        } else {
            $strFile = isset($strData['name']) && $strData['name'] != "" ? urldecode($strData['name']) : null;
        }

        if ($strData instanceof class_module_mediamanager_file) {
            $strFileHref = _webpath_ . "/download.php?systemid=" . $strData->getSystemid();
        } else {
            $strFileHref = "#";
        }

        $strReturn .= $objToolkit->formInputUpload($this->getStrEntryName(), $this->getStrLabel(), "", $strFile, $strFileHref, !$this->getBitReadonly());

        return $strReturn;
    }

    public function setValueToObject()
    {
        if ($this->getObjSourceObject() == null) {
            return;
        }

        $arrData = $this->getStrValue();
        if (!is_array($arrData)) {
            $arrData = json_decode($arrData, true);
        }

        // upload only if we have a valid file upload
        if (!(is_array($arrData) && isset($arrData["tmp_name"]) && $arrData["tmp_name"] != "")) {
            return;
        }

        // handle file uploads
        $objRecord = $this->getObjSourceObject();
        $objReflection = new class_reflection($objRecord);
        $strSetter = $objReflection->getSetter($this->getStrSourceProperty());
        if (!empty($strSetter)) {
            if ($this->arrFile === null) {
                $arrUpload = self::handleFileUpload($arrData);
                if (!empty($arrUpload)) {
                    $this->arrFile = json_encode($arrUpload);
                }
            }

            if (!empty($this->arrFile)) {
                $objRecord->$strSetter($this->arrFile);
            }
        }
    }

    public function getValueAsText()
    {
        $strData = $this->getStrValue();
        if (!is_array($strData)) {
            $strData = json_decode($strData, true);
        }

        $strFile = isset($strData['name']) && $strData['name'] != "" ? urldecode($strData['name']) : null;

        return !empty($strFile) ? $strFile : "-";
    }

    /**
     * @param array $arrData
     */
    public static function handleFileUpload(array $arrData)
    {
        if (isset($arrData["tmp_name"]) && is_uploaded_file($arrData["tmp_name"])) {
            $strSystemid = generateSystemid();
            $strPath = _realpath_ . "/files/tmp";
            $strFile = $strPath . "/" . $strSystemid . ".tmp";

            if (!is_dir($strPath)) {
                mkdir($strPath, 0777, true);
            }

            if (move_uploaded_file($arrData["tmp_name"], $strFile)) {
                $arrData["file_path"] = $strFile;
            } else {
                $arrData["file_path"] = null;
            }

            return $arrData;
        }

        return null;
    }
}
