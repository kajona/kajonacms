<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\System\Date;
use Kajona\System\System\Exception;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\OrmBase;
use Kajona\System\System\Reflection;


/**
 * Serializer class which can convert all table columns of an model into an string representation and vice versa
 *
 * @author christoph.kappestein@gmail.com
 * @since  4.8
 * @module module_system
 */
class AdminModelserializer {

    const STR_ANNOTATION_SERIALIZABLE = "@serializable";

    const CLASS_KEY = "strRecordClass";

    /**
     * Converts an model into an string representation
     *
     * @param ModelInterface $objModel
     * @return string
     */
    public static function serialize(ModelInterface $objModel, $strAnnotation = OrmBase::STR_ANNOTATION_TABLECOLUMN) {
        $objReflection = new Reflection(get_class($objModel));
        $arrProperties = $objReflection->getPropertiesWithAnnotation($strAnnotation);
        $arrJSON = array();

        foreach($arrProperties as $strAttributeName => $strAttributeValue) {
            $strGetter = $objReflection->getGetter($strAttributeName);
            if($strGetter != null) {
                $strValue = $objModel->$strGetter();
                if($strValue instanceof Date) {
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
     * @return ModelInterface
     */
    public static function unserialize($strData, $strAnnotation = OrmBase::STR_ANNOTATION_TABLECOLUMN) {
        $arrData = json_decode($strData, true);
        $objModel = self::getObjectFromJson($arrData);

        $objReflection = new Reflection(get_class($objModel));
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
                if($objInstance instanceof ModelInterface) {
                    return $objInstance;
                }
            }
        }

        throw new Exception("Could not determine object type", Exception::$level_ERROR);
    }
}
