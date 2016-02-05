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
            $strData = json_decode($strData, true);
        }

        $strFile = isset($strData['name']) && $strData['name'] != "" ? urldecode($strData['name']) : null;

        if ($this->getBitReadonly()) {
            $strFileHref = $this->getFileHref($strData);
            $strReturn .= $objToolkit->formInputDownload($this->getStrEntryName(), $this->getStrLabel(), "", $strFile, $strFileHref);
        } else {
            $strReturn .= $objToolkit->formInputUpload($this->getStrEntryName(), $this->getStrLabel(), "", $strFile);
        }

        return $strReturn;
    }

    public function setValueToObject()
    {
        $strData = $this->getStrValue();
        if (!is_array($strData)) {
            $strData = json_decode($strData, true);
        }

        if (isset($strData['name']) && $strData['name'] != "") {
            // we set the value only if we have a valid upload
            parent::setValueToObject();
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

    protected function getFileHref($arrFile)
    {
        if (empty($arrFile)) {
            return '#';
        }

        // @TODO generate download link

        return '#';
    }
}
