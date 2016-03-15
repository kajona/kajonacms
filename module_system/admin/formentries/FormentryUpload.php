<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\Mediamanager\System\MediamanagerFile;
use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Reflection;
use Kajona\System\System\Validators\DummyValidator;


/**
 * @author stefan.meyer1@yahoo.de
 * @since 4.4
 * @package module_formgenerator
 */
class FormentryUpload extends FormentryBase implements FormentryPrintableInterface {

    protected $arrFile;

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null)
    {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new DummyValidator());
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField()
    {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";

        if ($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        list($strFile, $strFileHref) = $this->getFileNameAndHref();

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
        $objReflection = new Reflection($objRecord);
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
        list($strFile, $strFileHref) = $this->getFileNameAndHref();

        if (!empty($strFile)) {
            return '<a href="' . $strFileHref . '">' . $strFile . '</a>';
        } else {
            return '-';
        }
    }

    private function getFileNameAndHref()
    {
        $strData = $this->getStrValue();

        if (!is_array($strData)) {
            if (validateSystemid($strData)) {
                $strData = new MediamanagerFile($strData);
            } else {
                $strData = json_decode($strData, true);
            }
        }

        if ($strData instanceof MediamanagerFile) {
            $strFile = $strData->getStrDisplayName();
        } else {
            $strFile = isset($strData['name']) && $strData['name'] != "" ? urldecode($strData['name']) : null;
        }

        if ($strData instanceof MediamanagerFile) {
            $strFileHref = _webpath_ . "/download.php?systemid=" . $strData->getSystemid();
        } else {
            $strFileHref = "#";
        }

        return array($strFile, $strFileHref);
    }

    /**
     * @param array $arrData
     *
     * @return array|null
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
