<?php

namespace Kajona\Jsonapi\Tests;

use Kajona\Jsonapi\System\ObjectSerializer;
use Kajona\News\System\NewsNews;
use Kajona\System\System\Date;

class ObjectSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testSerialize()
    {
        $objDate = new Date();
        $objNews = new NewsNews();
        $objNews->setObjDateStart($objDate);
        $objSerializer = new ObjectSerializer($objNews);

        $this->assertEquals(array('strTitle', 'strImage', 'intHits', 'strIntro', 'strText', 'objDateStart', 'objDateEnd'), $objSerializer->getPropertyNames());
        $this->assertEquals(array('strTitle' => '', 'strImage' => '', 'intHits' => 0, 'strIntro' => '', 'strText' => '', 'objDateStart' => date(\DateTime::ATOM, $objDate->getTimeInOldStyle()), 'objDateEnd' => null), $objSerializer->getArrMapping());
    }
}
