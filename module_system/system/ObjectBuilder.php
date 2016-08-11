<?php

namespace Kajona\System\System;

use Pimple\Container;

/**
 * Class which can create new objects and resolves all properties with an @inject annotation. So you get an object
 * containing all needed services without the need to manually use constructor or setter injection
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 4.6
 */
class ObjectBuilder
{
    const ANNOTATION_INJECT = "@inject";

    /**
     * @var Container
     */
    protected $objContainer;

    /**
     * @param Container $objContainer
     */
    public function __construct(Container $objContainer)
    {
        $this->objContainer = $objContainer;
    }

    /**
     * Creates a new object and resolves all properties with an inject annotation from the DI container. Through this
     * method you can use the dependencies also in the constructor. If you create the object otherwise you can NOT use
     * the dependencies inside the constructor
     *
     * @param string $strClass
     * @param array $arrArguments
     *
     * @return object
     *
     * @todo the constructor call should get obsolete. by convention the constructor shouldn't require access to properties injected by the dependency container. Currently used e.g. in AdminController.
     *       We need to scan all classes making use of the DI and update them accordingly, afterwards the constructor call will be removed
     *
     */
    public function factory($strClass, array $arrArguments = array())
    {
        // create new instance without constructor
        $objReflection = new Reflection($strClass);
        $objObject = $objReflection->newInstanceWithoutConstructor();

        // inject dependencies
        $this->resolveDependencies($objObject);

        // call the constructor after the dependencies are added because the constructor probably uses them
        if (is_callable(array($objObject, "__construct"))) {

            if(empty($arrArguments)) {
                $objObject->__construct();
            }
            else {
                call_user_func_array(array($objObject, "__construct"), $arrArguments);

            }
        }

        return $objObject;
    }

    /**
     * Can be used to inject the properties if you have already an object. Normally you want to use the factory method
     *
     * @param object $objObject
     */
    public function resolveDependencies($objObject)
    {
        // read inject annotations
        $objReflection = new Reflection($objObject);
        $arrValues = $objReflection->getPropertiesWithAnnotation(self::ANNOTATION_INJECT);

        // inject dependencies
        foreach ($arrValues as $strPropertyName => $strValue) {
            $objService = $this->objContainer->offsetGet($strValue);
            $objReflection->setObjectProperty($objObject, $strPropertyName, $objService);
        }
    }
}
