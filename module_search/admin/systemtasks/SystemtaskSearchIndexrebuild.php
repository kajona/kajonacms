<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                   *
********************************************************************************************************/

namespace Kajona\Search\Admin\Systemtasks;

use Kajona\Search\System\SearchIndexwriter;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\Carrier;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SystemModule;

/**
 * Rebuild the search index
 *
 * @package module_search
 */
class SystemtaskSearchIndexrebuild extends SystemtaskBase implements AdminSystemtaskInterface
{


    private $STR_SESSION_KEY = "SystemtaskSearchIndexrebuild";


    /**
     * constructor to call the base constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setStrTextBase("search");
    }

    /**
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "search";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "searchindexrebuild";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_search_indexrebuild");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("search")->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        $objWorker = new SearchIndexwriter();

        if (!Carrier::getInstance()->getObjSession()->sessionIsset($this->STR_SESSION_KEY)) {

            //fetch all records to be indexed
            $strQuery = "SELECT system_id FROM "._dbprefix_."system WHERE system_deleted = 0";
            $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

            $arrIds = array();
            foreach ($arrRows as $arrOneRow) {
                $arrIds[] = $arrOneRow["system_id"];
            }


            $objWorker->clearIndex();
            Carrier::getInstance()->getObjSession()->setSession($this->STR_SESSION_KEY, $arrIds);
            $this->setParam("totalCount", count($arrIds));
        }


        $arrIds = Carrier::getInstance()->getObjSession()->getSession($this->STR_SESSION_KEY);

        if (count($arrIds) == 0) {
            Carrier::getInstance()->getObjSession()->sessionUnset($this->STR_SESSION_KEY);
            return $this->objToolkit->getTextRow($this->getLang("worker_indexrebuild_end", array($objWorker->getNumberOfDocuments(), $objWorker->getNumberOfContentEntries())));
        }

        $intMax = 0;
        foreach ($arrIds as $intKey => $strOneValue) {

            $objObject = Objectfactory::getInstance()->getObject($strOneValue);

            if ($objObject != null) {
                $objWorker->indexObject($objObject);
            }

            unset($arrIds[$intKey]);

            if ($intMax++ > 500) {
                break;
            }
        }

        Carrier::getInstance()->getObjSession()->setSession($this->STR_SESSION_KEY, $arrIds);


        //and create a small progress-info
        $intTotal = $this->getParam("totalCount");
        $floatOnePercent = 100 / $intTotal;
        //and multiply it with the already processed records
        $intLookupsDone = ((int)$intTotal - count($arrIds)) * $floatOnePercent;
        $intLookupsDone = round($intLookupsDone, 2);
        if ($intLookupsDone < 0) {
            $intLookupsDone = 0;
        }

        $this->setStrProgressInformation($this->getLang("worker_indexrebuild", array($objWorker->getNumberOfDocuments(), $objWorker->getNumberOfContentEntries())));
        $this->setStrReloadParam("&totalCount=".$this->getParam("totalCount"));

        return $intLookupsDone;
    }

    /**
     * @inheritdoc
     */
    public function getAdminForm()
    {
        Carrier::getInstance()->getObjSession()->sessionUnset($this->STR_SESSION_KEY);
        return "";
    }

}
