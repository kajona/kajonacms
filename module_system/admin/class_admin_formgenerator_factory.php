<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * Form generator factory which creates class_admin_formgenerator instances based on an model. The formgenerator class
 * can be specified by a @formGenerator annotation on the model
 *
 * @author christoph.kappestein@gmail.com
 * @since  4.8
 * @module module_system
 */
class class_admin_formgenerator_factory {

    const STR_FORMGENERATOR_ANNOTATION = "@formGenerator";

    /**
     * Cache for all created form generator objects
     *
     * @var class_admin_formgenerator[]
     */
    protected static $arrForms = array();

    /**
     * Returns the fitting form generator for the model. The result is cached so that the a model returns always the
     * same instance
     *
     * @param interface_model $objInstance
     * @return class_admin_formgenerator
     * @throws class_exception
     */
    public static function createByModel(interface_model $objInstance) {
        // check whether the form was already generated
        $objForm = self::getFormForModel($objInstance);
        if($objForm !== null) {
            return $objForm;
        }

        // check whether a specific form generator class was specified per annotation
        $objReflection = new class_reflection($objInstance);
        $arrValues = $objReflection->getAnnotationValuesFromClass(self::STR_FORMGENERATOR_ANNOTATION);

        if(!empty($arrValues)) {
            $strClass = current($arrValues);
            if(class_exists($strClass)) {
                $objForm = new $strClass($objInstance->getArrModule("module"), $objInstance);
            }
            else {
                throw new class_exception("Provided form generator class does not exist", class_exception::$level_ERROR);
            }
        }
        else {
            $objForm = new class_admin_formgenerator($objInstance->getArrModule("module"), $objInstance);
        }

        // check whether we have an correct instance
        if($objForm instanceof class_admin_formgenerator) {
            $objForm->generateFieldsFromObject();

            return self::$arrForms[self::getKeyByModel($objInstance)] = $objForm;
        }
        else {
            throw new class_exception("Provided form generator must be an instance of class_admin_formgenerator", class_exception::$level_ERROR);
        }
    }

    /**
     * Returns whether a form exists in the cache
     *
     * @return boolean
     */
    public static function hasModel(interface_model $objInstance) {
        return self::getFormForModel($objInstance) !== null;
    }

    /**
     * Returns the form generator from the internal cache or null if not available
     *
     * @param interface_model $objInstance
     * @return class_admin_formgenerator|null
     */
    public static function getFormForModel(interface_model $objInstance) {
        $strKey = self::getKeyByModel($objInstance);
        return isset(self::$arrForms[$strKey]) ? self::$arrForms[$strKey] : null;
    }

    /**
     * Returns the cache key for the model
     *
     * @param interface_model $objInstance
     * @return string
     */
    public static function getKeyByModel(interface_model $objInstance) {
        return get_class($objInstance) . $objInstance->getSystemid();
    }

}
