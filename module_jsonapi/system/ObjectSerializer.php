<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Jsonapi\System;

use DateTime;
use Kajona\System\System\Reflection;
use Kajona\System\System\Root;

/**
 * The ObjectSerializer is based on the TemplateMapper. Returns
 * all properties of an class which has an @jsonExport annotation. These
 * properties are exposed through the json api
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @author christoph.kappestein@gmail.com
 * @since 4.5
 *
 * @todo this class requires some cleanup, e.g. the removal of unused methods
 */
class ObjectSerializer
{

    const STR_ANNOTATION_JSONEXPORT = "@jsonExport";

    /** @var Root */
    private $objObject = null;

    private $arrMapping = array();


    /**
     * @param null $objObject
     */
    function __construct($objObject = null)
    {
        $this->objObject = $objObject;

        if ($objObject !== null) {
            $this->readPropertiesFromObject();
        }
    }

    /**
     * Returns an array with all property names
     *
     * @return array
     */
    public function getPropertyNames()
    {
        $objReflection = new Reflection($this->objObject);
        $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_ANNOTATION_JSONEXPORT);

        return array_keys($arrProperties);
    }

    /**
     * Reads the properties marked with templateExport from the current object
     *
     * @return void
     */
    private function readPropertiesFromObject()
    {
        $objReflection = new Reflection($this->objObject);
        $properties = $this->getPropertyNames();

        foreach ($properties as $strOneProperty) {
            $strGetter = $objReflection->getGetter($strOneProperty);

            $strValue = $this->objObject->{$strGetter}();
            if ($strValue instanceof \Kajona\System\System\Date) {
                $strValue = date(DateTime::ATOM, $strValue->getTimeInOldStyle());
            }

            $this->arrMapping[$strOneProperty] = $strValue;
        }
    }

    /**
     * @param array $arrMapping
     *
     * @return void
     */
    public function setArrMapping($arrMapping)
    {
        $this->arrMapping = $arrMapping;
    }

    /**
     * @return array
     */
    public function getArrMapping()
    {
        return $this->arrMapping;
    }
}
