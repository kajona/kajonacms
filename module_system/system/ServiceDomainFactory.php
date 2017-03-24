<?php

namespace Kajona\System\System;

use Pimple\Container;

/**
 * ServiceDomainFactory
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 5.2
 */
class ServiceDomainFactory
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
        $arrValues = $objReflection->getAnnotationValuesFromClass(ServiceDomainInterface::STR_SERVICE_ANNOTATION);
        $strServiceName = reset($arrValues);

        if ($this->objContainer->offsetExists($strServiceName)) {
            return $this->objContainer->offsetGet($strServiceName);
        } else {
            return new ServiceDomainImpl();
        }
    }
}
