<?php

namespace Kajona\System\System;

use Pimple\Container;

/**
 * ServiceLifeCycleFactory
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 6.2
 */
class ServiceLifeCycleFactory
{
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
     * @param string $strClass
     */
    public function factory($strClass)
    {
        $objReflection = new Reflection($strClass);
        $arrValues = $objReflection->getAnnotationValuesFromClass(ServiceLifeCycleInterface::STR_SERVICE_ANNOTATION);
        $strServiceName = reset($arrValues);

        if ($this->objContainer->offsetExists($strServiceName)) {
            return $this->objContainer->offsetGet($strServiceName);
        } else {
            return $this->objContainer->offsetGet(ServiceProvider::STR_LIFE_CYCLE_DEFAULT);
        }
    }
}
