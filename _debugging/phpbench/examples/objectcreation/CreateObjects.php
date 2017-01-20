<?php

namespace AGP\_Phpbench\Benchmarks\Examples\Objectcreation;

use AGP\Prozessverwaltung\System\ProzessverwaltungProzess;
use Kajona\System\System\Objectfactory;

require_once("ObjectsBase.php");

/**
 * Class CreateObjects
 */
class CreateObjects extends ObjectsBase
{
    protected $strProzId = null;

    /** @var ProzessverwaltungProzess */
    protected $objProcess = null;

    /** @var ProzessverwaltungProzess[]  */
    protected $arrRatingsCreated = array();

    /**
     * Executed before each subject iteration
     */
    public function setupMethod()
    {
        //special breaks for the user demodata
        $this->strProzId = ProzessverwaltungProzess::getProzesseByName("dwp Bank")[0]->getStrSystemid();
        $this->objProcess = Objectfactory::getInstance()->getObject($this->strProzId);
    }

    /**
     * Executed after each subject iteration
     */
    public function teardownMethod()
    {
        foreach($this->arrRatingsCreated as $objRating) {
            $objRating->deleteObjectFromDatabase();
        }
        $this->arrRatingsCreated = array();
    }

    /**
     * Creates Leistungsschein objects
     *
     * @Revs({1})
     * @Iterations(5)
     * @Warmup(2)
     */
    public function benchCreateLeistungsscheine()
    {
        $objRating = $this->createLeistungsschein($this->objProcess);
        $this->arrRatingsCreated[] = $objRating;
    }
}