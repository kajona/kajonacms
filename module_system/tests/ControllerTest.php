<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Tests;

use Kajona\System\System\AbstractController;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\ResponseObject;

/**
 * Class CacheManagerTest
 *
 * @author sidler@mulchprod.de
 * @since 6.2
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testResponseTypeParsing()
    {
        $objController = new ControllerTestController();

        $strResult = $objController->action("xmlResponse");
        $this->assertEquals(HttpResponsetypes::STR_TYPE_XML, ResponseObject::getInstance()->getStrResponseType());
        $this->assertEquals("xml", $strResult);

        $strResult = $objController->action("defaultResponse");
        $this->assertEquals(HttpResponsetypes::STR_TYPE_HTML, ResponseObject::getInstance()->getStrResponseType());
        $this->assertEquals("default", $strResult);
    }
}

/**
 * Class ControllerTestController
 *
 * @since 6.2
 * @moduleId _system_modul_id_
 * @module system
 */
class ControllerTestController extends AbstractController
{

    /**
     * @responseType xml
     * @return string
     */
    protected function actionXmlResponse()
    {
        return "xml";
    }

    /**
     * @responseType abcdiekatze
     * @return string
     */
    protected function actionDefaultResponse()
    {
        return "default";
    }
}