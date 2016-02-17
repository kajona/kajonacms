<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;


/**
 * A dropdown used to select a single file out of a mapped folder.
 * Therefore the path of the folder to be scanned for files may be passed by an annotation "@fieldSourceDir", too.
 * Useful for selecting an image out of a folder or s.th. similar.
 *
 * @author sidler@mulchprod.de
 * @since 4.7
 * @package module_formgenerator
 */
class FormentryFiledropdown extends class_formentry_base implements interface_formentry {

    const STR_SOURCEDIR_ANNOTATION = "@fieldSourceDir";



    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new class_file_validator($this->getSourceDir()));
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());


        $arrFiles = array();

        if($this->getSourceDir() !== null) {
            $objFilesystem = new class_filesystem();
            $arrPlainFiles = $objFilesystem->getFilelist($this->getSourceDir());
            $arrFiles = array_combine($arrPlainFiles, $arrPlainFiles);
        }


        $strReturn .=  $objToolkit->formInputDropdown($this->getStrEntryName(), $arrFiles, $this->getStrLabel(), $this->getStrValue(), "", !$this->getBitReadonly());
        return $strReturn;
    }


    private function getSourceDir() {
        if($this->getObjSourceObject() != null && $this->getStrSourceProperty() != "") {
            $objReflection = new class_reflection($this->getObjSourceObject());

            //try to find the matching source property
            $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_SOURCEDIR_ANNOTATION);
            $strSourceProperty = null;
            foreach($arrProperties as $strPropertyName => $strValue) {
                if(uniSubstr(uniStrtolower($strPropertyName), (uniStrlen($this->getStrSourceProperty())) * -1) == $this->getStrSourceProperty()) {
                    $strSourceProperty = $strPropertyName;
                }
            }

            if($strSourceProperty != null) {
                $strDir = $objReflection->getAnnotationValueForProperty($strSourceProperty, self::STR_SOURCEDIR_ANNOTATION);

                if($strDir !== null && $strDir != "") {
                    return $strDir;
                }
            }
        }

        return null;
    }
}
