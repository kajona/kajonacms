<?php

namespace AGP\_Phpbench\Benchmarks\Examples\Objectcreation;

use AGP\Leistungsscheine\System\LeistungsscheineLeistungsschein;
use AGP\Prozessverwaltung\System\ProzessverwaltungProzess;

/**
 * @BeforeClassMethods({"setupClass"})
 * @AfterClassMethods({"teardownClass"})
 *
 * @BeforeMethods({"setupMethod"})
 * @AfterMethods({"teardownMethod"})
 */
abstract class ObjectsBase
{

    //create possible dummy data
    protected static $arrDemoLieferverant = array("ABC, U. Mustermann", "Demo22, Group Ops", "ABC, N. Korb", "ABC, BG, L. Minzel", "ABC, MIS", "ABC, TSY");
    protected static $arrDemoGesamtzahl = array(1, 1, 1, 3, 1, 1, 5, 1, 1, 1, 7, 1);

    protected static $arrCheckboxValues = array("unchecked", "checked");
    protected static $arrAmpelValues = array(
        "_prozessverwaltung_wert_rot_",
        "_prozessverwaltung_wert_gelb_",
        "_prozessverwaltung_wert_gruen_",
        "_prozessverwaltung_wert_gruen_"
    );

    /**
     * Executed before executing benchmarks
     */
    public static function setupClass()
    {
        self::logFile("debug.txt", __METHOD__);
    }

    /**
     * Executed after all bencmarks have been executed
     */
    public static function teardownClass()
    {
        self::logFile("debug.txt", __METHOD__);
        self::deleteAllBewertungen();
    }

    /**
     * Executed before each subject iteration
     */
    public function setupMethod()
    {

    }

    /**
     * Executed after each subject iteration
     */
    public function teardownMethod()
    {

    }

    /**
     * Deletes all ratings
     */
    public static function deleteAllBewertungen()
    {
        $arrRatings = LeistungsscheineLeistungsschein::getObjectListFiltered(null);
        foreach ($arrRatings as $objRating) {
            $objRating->deleteObjectFromDatabase();
        }
    }

    /**
     * Creates a LeistungsscheineLeistungsschein for the given process
     *
     * @return LeistungsscheineLeistungsschein
     * @throws \Kajona\System\System\Exception
     */
    protected function createLeistungsschein(ProzessverwaltungProzess $objProcess)
    {
        $objRating = new LeistungsscheineLeistungsschein();
        $objRating->setStrProzessId($objProcess->getStrSystemid());
        $objRating->setStrLieferverantwortlicher(self::$arrDemoLieferverant[intval(rand(0, count(self::$arrDemoLieferverant) - 1))]);
        $objRating->setIntGesamtzahl(self::$arrDemoGesamtzahl[intval(rand(0, count(self::$arrDemoGesamtzahl) - 1))]);
        $objRating->updateObjectToDb();

        return $objRating;
    }


    /**
     * Methdd which just writes log entries to the given file
     *
     * @param $strFileName
     * @param $strTxt
     */
    protected static function logFile($strFileName, $strTxt)
    {
        $objDate = new DateTime();
        $strTime = $objDate->format('Y-m-d H:i:s');

        $myfile = fopen($strFileName, "a");
        fwrite($myfile, "- " . $strTime . " - " . $strTxt . "\n");
        fclose($myfile);
    }
}