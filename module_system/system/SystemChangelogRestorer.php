<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * The changelog-reverter / restorer is an extension to the changelog-handler is capable of pushing changes
 * back to an object.
 * Therefore the value of a property for a given timestamp is extracted from the changelog and written back to the
 * object.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class SystemChangelogRestorer extends SystemChangelog
{

    /**
     * Restores a single property marked as versionable
     *
     * @param VersionableInterface|Model $objObject
     * @param Date $objTimestamp
     * @param $strProperty
     */
    public function restoreProperty(VersionableInterface $objObject, Date $objTimestamp, $strProperty)
    {

        //there are a few properties not to change
        if ($strProperty == "intRecordStatus") {
            return;
        }

        //load the value from the changelog
        $strValue = $this->getValueForDate($objObject->getSystemid(), $strProperty, $objTimestamp);

        if ($strValue === false) {
            $strValue = null;
        }

        //remove the system-id temporary to avoid callbacks and so on
        $strSystemid = $objObject->getSystemid();

        $objObject->unsetSystemid();

        //all prerequisites match, start creating query
        $objReflection = new Reflection($objObject);

        //check if the target property was an object-list. if given, the string from the database should be transformed to an array instead.
        $arrObjectlistProperties = $objReflection->getPropertiesWithAnnotation(OrmBase::STR_ANNOTATION_OBJECTLIST);

        if(in_array($strProperty, array_keys($arrObjectlistProperties))) {
            $strValue = array_map(function($strValue) { return Objectfactory::getInstance()->getObject($strValue); }, explode(",", $strValue));
        }

        $strSetter = $objReflection->getSetter($strProperty);
        if ($strSetter !== null) {
            $objObject->{$strSetter}($strValue);
        }

        $objObject->setSystemid($strSystemid);

    }

    /**
     * Restores all properties marked as versionable
     *
     * @param VersionableInterface $objObject
     * @param Date $objTimestamp
     */
    public function restoreObject(VersionableInterface $objObject, Date $objTimestamp)
    {

        $objReflection = new Reflection($objObject);
        $arrProperties = $objReflection->getPropertiesWithAnnotation(self::ANNOTATION_PROPERTY_VERSIONABLE);


        foreach ($arrProperties as $strProperty => $strAnnotation) {

            $this->restoreProperty($objObject, $objTimestamp, $strProperty);

        }
    }


}
