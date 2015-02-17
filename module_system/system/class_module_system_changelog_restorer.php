<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * The changelog-reverter / restorer is an extension to the changelog-handler is capable of pushing changes
 * back to an object.
 * Therefore the value of a property for a given timestamp is extracted from the changelog and written back to the
 * object.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @see class_logger
 * @since 4.5
 */
class class_module_system_changelog_restorer extends class_module_system_changelog implements interface_model {

    /**
     * Restores a single property marked as versionable
     *
     * @param interface_versionable|class_model $objObject
     * @param class_date $objTimestamp
     * @param $strProperty
     */
    public function restoreProperty(interface_versionable $objObject, class_date $objTimestamp, $strProperty) {

        //there are a few properties not to change
        if($strProperty == "intRecordStatus")
            return;

        //load the value from the changelog
        $strValue = $this->getValueForDate($objObject->getSystemid(), $strProperty, $objTimestamp);

        if($strValue === false)
            $strValue = null;

        //remove the system-id temporary to avoid callbacks and so on
        $strSystemid = $objObject->getSystemid();

        $objObject->unsetSystemid();

        //all prerequisites match, start creating query
        $objReflection = new class_reflection($objObject);
        $strSetter = $objReflection->getSetter($strProperty);
        if($strSetter !== null) {
            call_user_func(array($objObject, $strSetter), $strValue);
        }

        $objObject->setSystemid($strSystemid);

    }

    /**
     * Restores all properties marked as versionable
     *
     * @param interface_versionable $objObject
     * @param class_date $objTimestamp
     */
    public function restoreObject(interface_versionable $objObject, class_date $objTimestamp) {

        $objReflection = new class_reflection($objObject);
        $arrProperties = $objReflection->getPropertiesWithAnnotation(self::ANNOTATION_PROPERTY_VERSIONABLE);


        foreach($arrProperties as $strProperty => $strAnnotation) {

            $this->restoreProperty($objObject, $objTimestamp, $strProperty);

        }
    }



}
