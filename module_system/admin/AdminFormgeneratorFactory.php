<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\System\Exception;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Reflection;


/**
 * Form generator factory which creates AdminFormgenerator instances based on an model. The formgenerator class
 * can be specified by a @formGenerator annotation on the model
 *
 * @author christoph.kappestein@gmail.com
 * @since  4.8
 * @module module_system
 */
class AdminFormgeneratorFactory {

    const STR_FORMGENERATOR_ANNOTATION = "@formGenerator";

    /**
     * Cache for all created form generator objects
     *
     * @var AdminFormgenerator[]
     */
    protected static $arrForms = array();

    /**
     * Returns the fitting form generator for the model. The result is cached so that the a model returns always the
     * same instance
     *
     * @param ModelInterface $objInstance
     * @return AdminFormgenerator
     * @throws Exception
     */
    public static function createByModel(ModelInterface $objInstance) {
        // check whether the form was already generated
        $objForm = self::getFormForModel($objInstance);
        if($objForm !== null) {
            return $objForm;
        }

        // check whether a specific form generator class was specified per annotation
        $objReflection = new Reflection($objInstance);
        $arrValues = $objReflection->getAnnotationValuesFromClass(self::STR_FORMGENERATOR_ANNOTATION);

        if(!empty($arrValues)) {
            $strClass = current($arrValues);
            if(class_exists($strClass)) {
                $objForm = new $strClass($objInstance->getArrModule("module"), $objInstance);
            }
            else {
                throw new Exception("Provided form generator class does not exist", Exception::$level_ERROR);
            }
        }
        else {
            $objForm = new AdminFormgenerator($objInstance->getArrModule("module"), $objInstance);
        }

        // check whether we have an correct instance
        if($objForm instanceof AdminFormgenerator) {
            $objForm->generateFieldsFromObject();

            return self::$arrForms[self::getKeyByModel($objInstance)] = $objForm;
        }
        else {
            throw new Exception("Provided form generator must be an instance of AdminFormgenerator", Exception::$level_ERROR);
        }
    }

    /**
     * Returns whether a form exists in the cache
     *
     * @return boolean
     */
    public static function hasModel(ModelInterface $objInstance) {
        return self::getFormForModel($objInstance) !== null;
    }

    /**
     * Returns the form generator from the internal cache or null if not available
     *
     * @param ModelInterface $objInstance
     * @return AdminFormgenerator|null
     */
    public static function getFormForModel(ModelInterface $objInstance) {
        $strKey = self::getKeyByModel($objInstance);
        return isset(self::$arrForms[$strKey]) ? self::$arrForms[$strKey] : null;
    }

    /**
     * Returns the cache key for the model
     *
     * @param ModelInterface $objInstance
     * @return string
     */
    public static function getKeyByModel(ModelInterface $objInstance) {
        return get_class($objInstance) . $objInstance->getSystemid();
    }

}
