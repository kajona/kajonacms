<?php

namespace Kajona\Jsonapi\Tests;

use Kajona\Jsonapi\Admin\JsonapiAdmin;
use Kajona\News\System\NewsNews;
use Kajona\System\System\Carrier;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Session;
use Kajona\System\System\SystemModule;
use Kajona\System\System\UserUser;

class JsonapiAdminTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $objAdmin = $this->getAdminMock("GET");

        Carrier::getInstance()->setParam("class", NewsNews::class);

        $strResult = $objAdmin->action("dispatch");

        // we must remove several date values
        $strResult = preg_replace("|[a-f0-9]{20}|ims", "", $strResult);
        $strResult = preg_replace("|[0-9]{2}\\\\/[0-9]{2}\\\\/[0-9]{4}|ims", "", $strResult);
        $strResult = preg_replace("|[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\+[0-9]{2}:[0-9]{2}|ims", "", $strResult);

        $strExpect = <<<'JSON'
{
    "totalCount": "2",
    "startIndex": 0,
    "filter": null,
    "entries": [
        {
            "_id": "",
            "_class": "Kajona\\News\\System\\NewsNews",
            "_icon": "icon_news",
            "_displayName": "Installation successful",
            "_additionalInfo": "0 Hits",
            "_longDescription": "S: ",
            "strTitle": "Installation successful",
            "strImage": "",
            "intHits": "0",
            "strIntro": "Kajona installed successfully...",
            "strText": "Another installation of Kajona was successful. For further information, support or proposals, please visit our website: www.kajona.de",
            "objDateStart": "",
            "objDateEnd": null
        },
        {
            "_id": "",
            "_class": "Kajona\\News\\System\\NewsNews",
            "_icon": "icon_news",
            "_displayName": "Sed non enim est",
            "_additionalInfo": "0 Hits",
            "_longDescription": "S: ",
            "strTitle": "Sed non enim est",
            "strImage": "",
            "intHits": "0",
            "strIntro": "Quisque sagittis egestas tortor",
            "strText": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non enim est, id hendrerit metus. Sed tempor quam sed ante viverra porta. Quisque sagittis egestas tortor, in euismod sapien iaculis at. Nullam vitae nunc tortor. Mauris justo lectus, bibendum et rutrum id, fringilla eget ipsum. Nullam volutpat sodales mollis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Duis tempor ante eget justo blandit imperdiet. Praesent ut risus tempus metus sagittis fermentum eget eu elit. Mauris consequat ornare massa, a rhoncus enim sodales auctor. Duis lacinia dignissim eros vel mollis. Etiam metus tortor, pellentesque eu ultricies sit amet, elementum et dolor. Proin tincidunt nunc id magna volutpat lobortis. Vivamus metus quam, accumsan eget vestibulum vel, rutrum sit amet mauris. Phasellus lectus leo, vulputate eget molestie et, consectetur nec urna. ",
            "objDateStart": "",
            "objDateEnd": null
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($strExpect, $strResult, $strResult);
    }

    public function testGetNoClass()
    {
        Carrier::getInstance()->setParam("class", "");
        Carrier::getInstance()->setParam("news_title", "");
        Carrier::getInstance()->setParam("news_datestart", "");

        $objAdmin = $this->getAdminMock("GET");

        $strResult = $objAdmin->action("dispatch");
        $strExpect = <<<JSON
{
  "success": false,
  "message": "Invalid class name"
}
JSON;

        $this->assertEquals('HTTP/1.1 400 Bad Request', ResponseObject::getInstance()->getStrStatusCode(), $strResult);
        $this->assertJsonStringEqualsJsonString($strExpect, $strResult, $strResult);
    }

    public function testPost()
    {
        $arrUsers = UserUser::getAllUsersByName("admin");
        Session::getInstance()->loginUser($arrUsers[0]);

        Carrier::getInstance()->setParam("class", NewsNews::class);
        Carrier::getInstance()->setParam("news_title", "");
        Carrier::getInstance()->setParam("news_datestart", "");

        $arrData = array(
            "news_title" => "lorem ipsum",
            "news_datestart" => date('m/d/Y'),
        );

        $objAdmin = $this->getAdminMock("POST", $arrData);

        $strResult = $objAdmin->action("dispatch");
        $strExpect = <<<JSON
{
    "success": true,
    "message": "Create entry successful"
}
JSON;

        Session::getInstance()->logout();

        $this->assertEquals('HTTP/1.1 200 OK', ResponseObject::getInstance()->getStrStatusCode(), $strResult);
        $this->assertJsonStringEqualsJsonString($strExpect, $strResult, $strResult);
    }

    public function testPostInvalidData()
    {
        $arrUsers = UserUser::getAllUsersByName("admin");
        Session::getInstance()->loginUser($arrUsers[0]);

        Carrier::getInstance()->setParam("class", NewsNews::class);
        Carrier::getInstance()->setParam("news_title", "");
        Carrier::getInstance()->setParam("news_datestart", "");

        $arrData = array(
            "bar" => "foo"
        );

        $objAdmin = $this->getAdminMock("POST", $arrData);

        $strResult = $objAdmin->action("dispatch");
        $strExpect = <<<JSON
{
    "success": false,
    "errors": {
        "news_title": [
            "'Title' is empty"
        ],
        "news_datestart": [
            "'Start date' is empty"
        ]
    }
}
JSON;

        Session::getInstance()->logout();

        $this->assertEquals('HTTP/1.1 400 Bad Request', ResponseObject::getInstance()->getStrStatusCode(), $strResult);
        $this->assertJsonStringEqualsJsonString($strExpect, $strResult, $strResult);
    }

    /**
     * @param string $strMethod
     * @param array|null $strBody
     * @return JsonapiAdmin
     */
    protected function getAdminMock($strMethod, array $strBody = null)
    {
        $objAdmin = $this->getMockBuilder(JsonapiAdmin::class)
            ->setMethods(array("getRequestMethod", "getRawInput"))
            ->getMock();

        $objAdmin->setArrModuleEntry("modul", "jsonapi");
        $objAdmin->setArrModuleEntry("module", "jsonapi");
        $objAdmin->setArrModuleEntry("moduleId", _jsonapi_module_id_);

        $objAdmin->expects($this->once())
            ->method("getRequestMethod")
            ->will($this->returnValue(strtolower($strMethod)));

        $objAdmin->expects($this->any())
            ->method("getRawInput")
            ->will($this->returnValue($strBody ? json_encode($strBody) : ""));

        return $objAdmin;
    }
}
