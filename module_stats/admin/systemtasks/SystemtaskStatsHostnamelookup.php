<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                   *
********************************************************************************************************/

namespace Kajona\Stats\Admin\Systemtasks;

use Kajona\Stats\System\StatsWorker;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\SystemModule;


/**
 * Resolves the hostnames of given ips
 *
 * @package module_stats
 */
class SystemtaskStatsHostnamelookup extends SystemtaskBase implements AdminSystemtaskInterface
{


    /**
     * constructor to call the base constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setStrTextBase("stats");
    }

    /**
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "stats";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "statshostnamelookup";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_hostnamelookup_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("stats")->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        $strMessage = "";
        $objWorker = new StatsWorker("");

        //Load all IPs to lookup
        $arrIpToLookup = $objWorker->hostnameLookupIpsToLookup();

        if (count($arrIpToLookup) == 0) {
            return $this->objToolkit->getTextRow($this->getLang("worker_lookup_end"));
        }

        //check, if we did anything before
        if ($this->getParam("totalCount") == "") {
            $this->setParam("totalCount", count($arrIpToLookup));
        }

        $strMessage .= $this->objToolkit->getTextRow($this->getLang("intro_worker_lookup").$this->getParam("totalCount"));

        //Lookup 10 IPs an load the page again
        for ($intI = 0; $intI < 10; $intI++) {
            if (isset($arrIpToLookup[$intI])) {
                $strIP = $arrIpToLookup[$intI]["stats_ip"];
                $strHostname = gethostbyaddr($strIP);
                if ($strHostname != $strIP) {
                    //Hit. So save it to databse
                    $objWorker->hostnameLookupSaveHostname($strHostname, $strIP);
                }
                else {
                    //Mark the record as already touched
                    $objWorker->hostnameLookupSaveHostname("na", $strIP);
                }

            }
        }

        //and create a small progress-info
        $intTotal = $this->getParam("totalCount");
        $floatOnePercent = 100 / $intTotal;
        //and multiply it with the alredy looked up IPs
        $intLookupsDone = ((int)$intTotal - count($arrIpToLookup)) * $floatOnePercent;
        $intLookupsDone = round($intLookupsDone, 2);
        if ($intLookupsDone < 0) {
            $intLookupsDone = 0;
        }

        $this->setStrProgressInformation($strMessage);
        $this->setStrReloadParam("&totalCount=".$this->getParam("totalCount"));

        return $intLookupsDone;
    }

    /**
     * @inheritdoc
     */
    public function getAdminForm()
    {
        return "";
    }

}
