<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * Serializer class which can convert all table columns of an model into an string representation and vice versa
 *
 * @author christoph.kappestein@gmail.com
 * @since  4.8
 * @module module_system
 */
class class_admin_modelserializer {

    const CLASS_KEY = "strRecordClass";

    /**
     * Converts an model into an string representation
     *
     * @param interface_model $objModel
     * @return string
     */
    public static function serialize(interface_model $objModel, $strAnnotation = class_orm_base::STR_ANNOTATION_TABLECOLUMN) {
        $objReflection = new class_reflection(get_class($objModel));
        $arrProperties = $objReflection->getPropertiesWithAnnotation($strAnnotation);
        $arrJSON = array();

        foreach($arrProperties as $strAttributeName => $strAttributeValue) {
            $strGetter = $objReflection->getGetter($strAttributeName);
            if($strGetter != null) {
                $strValue = $objModel->$strGetter();
                if($strValue instanceof class_date) {
                    $strValue = $strValue->getLongTimestamp();
                }
                $arrJSON[$strAttributeName] = $strValue;
            }
        }

        $arrJSON[self::CLASS_KEY] = get_class($objModel);

        return json_encode($arrJSON);
    }

    /**
     * Creates an model based on an serialized string
     *
     * @return interface_model
     */
    public static function unserialize($strData, $strAnnotation = class_orm_base::STR_ANNOTATION_TABLECOLUMN) {
        $arrData = json_decode($strData, true);
        $objModel = self::getObjectFromJson($arrData);

        $objReflection = new class_reflection(get_class($objModel));
        $arrProperties = $objReflection->getPropertiesWithAnnotation($strAnnotation);

        foreach($arrProperties as $strAttributeName => $strAttributeValue) {
            $strSetter = $objReflection->getSetter($strAttributeName);
            if($strSetter != null && isset($arrData[$strAttributeName])) {
                $objModel->$strSetter($arrData[$strAttributeName]);
            }
        }

        return $objModel;
    }

    protected static function getObjectFromJson($arrData)
    {
        if(isset($arrData[self::CLASS_KEY])) {
            $strClassName = $arrData[self::CLASS_KEY];
            if(class_exists($strClassName)) {
                $objInstance = new $strClassName();
                if($objInstance instanceof interface_model) {
                    return $objInstance;
                }
            }
        }

        throw new class_exception("Could not determine object type", class_exception::$level_ERROR);
    }
}
