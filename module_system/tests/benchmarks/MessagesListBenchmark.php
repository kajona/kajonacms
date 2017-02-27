<?php

namespace Kajona\System\Tests\Benchmarks;

use Kajona\System\System\Carrier;
use Kajona\System\System\Messageproviders\MessageproviderPersonalmessage;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\Objectfactory;

/**
 * Benchmark Date vs DateTime
 *
 * Class CompareDateBench
 * 
 * @BeforeClassMethods({"cleanDatabase"})
 * @AfterClassMethods({"cleanDatabase"})
 *
 * @BeforeMethods({"initDatabase", "flushCaches"})
 */
class MessagesListBenchmark
{

    private static $arrIds = [];


    public function flushCaches()
    {
        Carrier::getInstance()->flushCache(
            Carrier::INT_CACHE_TYPE_DBSTATEMENTS |
            Carrier::INT_CACHE_TYPE_DBQUERIES |
            Carrier::INT_CACHE_TYPE_APC |
            Carrier::INT_CACHE_TYPE_CHANGELOG |
            Carrier::INT_CACHE_TYPE_CLASSLOADER |
            Carrier::INT_CACHE_TYPE_MODULES |
            Carrier::INT_CACHE_TYPE_OBJECTFACTORY |
            Carrier::INT_CACHE_TYPE_ORMCACHE
        );
    }

    public function initDatabase()
    {

        foreach(MessagingMessage::getObjectListFiltered() as $objOneMessage) {
            $objOneMessage->deleteObjectFromDatabase();
        }

        for($intI = 0; $intI < 10; $intI++) {
            $objMessage = new MessagingMessage();
            $objMessage->setStrUser(Carrier::getInstance()->getObjSession()->getUserID());
            $objMessage->setStrMessageProvider(MessageproviderPersonalmessage::class);
            $objMessage->updateObjectToDb();

            self::$arrIds[] = $objMessage->getSystemid();
        }

    }


    public static function cleanDatabase()
    {
        foreach(MessagingMessage::getObjectListFiltered() as $objOneMessage) {
            $objOneMessage->deleteObjectFromDatabase();
        }
    }

    /**
     * @Revs({1})
     * @Iterations(5)
     */
    public function benchMessagesList()
    {
        foreach(MessagingMessage::getObjectListFiltered() as $objOneMessage) {
            $objOneMessage->deleteObjectFromDatabase();
        }
    }

    /**
     * @Revs({1})
     * @Iterations(5)
     */
    public function benchMessagesInit()
    {
        foreach(self::$arrIds as $strOneId) {
            Objectfactory::getInstance()->getObject($strOneId);
        }
    }



}