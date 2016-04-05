<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

namespace Kajona\Rating\Installer;

use Kajona\Rating\System\RatingRate;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;


/**
 * Class providing an installer for the rating module
 *
 * @package module_rating
 * @moduleId _rating_modul_id_
 */
class InstallerRating extends InstallerBase implements InstallerRemovableInterface
{

    public function install()
    {
        $strReturn = "";
        $objManager = new OrmSchemamanager();

        $strReturn .= "Installing table rating...\n";
        $objManager->createTable("Kajona\\Rating\\System\\RatingRate");

        $strReturn .= "Installing table rating_history...\n";

        $arrFields = array();
        $arrFields["rating_history_id"] = array("char20", false);
        $arrFields["rating_history_rating"] = array("char20", true);
        $arrFields["rating_history_user"] = array("char20", true);
        $arrFields["rating_history_timestamp"] = array("int", true);
        $arrFields["rating_history_value"] = array("double", true);

        if (!$this->objDB->createTable("rating_history", $arrFields, array("rating_history_id"))) {
            $strReturn .= "An error occurred! ...\n";
        }


        //register the module
        $strSystemID = $this->registerModule(
            "rating",
            _rating_modul_id_,
            "RatingPortal.php",
            "",
            $this->objMetadata->getStrVersion(),
            false,
            "RatingPortalXml.php"
        );

        $strReturn .= "Module registered. Module-ID: ".$strSystemID." \n";

        $strReturn .= "Setting aspect assignments...\n";
        if (SystemAspect::getAspectByName("content") != null) {
            $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(SystemAspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        return $strReturn;

    }

    /**
     * Validates whether the current module/element is removable or not.
     * This is the place to trigger special validations and consistency checks going
     * beyond the common metadata-dependencies.
     *
     * @return bool
     */
    public function isRemovable()
    {
        return true;
    }

    /**
     * Removes the elements / modules handled by the current installer.
     * Use the reference param to add a human readable logging.
     *
     * @param string &$strReturn
     *
     * @return bool
     */
    public function remove(&$strReturn)
    {

        /** @var RatingRate $objOneObject */
        foreach (RatingRate::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if (!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
        if (!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach (array("rating", "rating_history") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if (!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }

        }

        return true;
    }


    public function update()
    {
        $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("rating", "4.7");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "4.7") {
            $strReturn .= "Updating to 5.0...\n";
            $this->updateModuleVersion("rating", "5.0");
        }

        return $strReturn."\n\n";
    }


}
