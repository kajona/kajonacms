<?php

namespace Kajona\System\System;

use Pimple\Container;

/**
 * ObjectBuilder
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 4.6
 */
class ObjectBuilder
{
    protected $objContainer;

    public function __construct(Container $objContainer)
    {
        $this->objContainer = $objContainer;
    }

    /**
     * Creates a new object. Resolves all properties with an inject annotation from the DI container
     *
     * @param string $strClass
     * @param array $arrArguments
     */
    public function factory($strClass, array $arrArguments = array())
    {
        // create new instance without constructor
        $objReflection = new \class_reflection($strClass);
        $objObject = $objReflection->newInstanceWithoutConstructor();

        // inject dependencies
        $this->resolveDependencies($objObject);

        // call the constructor after the dependencies are added because the constructor probably uses them
        call_user_func_array(array($objObject, "__construct"), $arrArguments);

        return $objObject;
    }

    public function resolveDependencies($objObject)
    {
        // read inject annotations
        $objReflection = new \class_reflection($objObject);
        $arrValues = $objReflection->getPropertiesWithAnnotation("@Inject");

        // inject dependencies
        foreach ($arrValues as $strPropertyName => $strValue) {
            $objService = $this->objContainer->offsetGet($strValue);
            $objReflection->setObjectProperty($objObject, $strPropertyName, $objService);
        }
    }
}
