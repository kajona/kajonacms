<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/

namespace Kajona\Stats\Admin\Systemtasks;

use Kajona\Stats\System\StatsWorker;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\Exception;
use Kajona\System\System\Remoteloader;
use Kajona\System\System\SystemModule;


/**
 * Resolves the country for a given ip-adress
 *
 * @package module_stats
 */
class SystemtaskStatsIp2c extends SystemtaskBase implements AdminSystemtaskInterface
{

    private $strIp2cServer = "ip2c.kajona.de";


    /**
     * contructor to call the base constructor
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
        return "statsip2c";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_ip2c_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("stats")->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        $strReturn = "";

        $objWorker = new StatsWorker();

        //determin the number of ips to lookup
        $arrIpToLookup = $objWorker->getArrayOfIp2cLookups();

        if (count($arrIpToLookup) == 0) {
            return $this->objToolkit->getTextRow($this->getLang("worker_lookup_end"));
        }

        //check, if we did anything before
        if ($this->getParam("totalCount") == "") {
            $this->setParam("totalCount", $objWorker->getNumberOfIp2cLookups());
        }

        $strReturn .= $this->objToolkit->getTextRow($this->getLang("intro_worker_lookupip2c").$this->getParam("totalCount"));

        //Lookup 10 Ips an load the page again
        for ($intI = 0; $intI < 10; $intI++) {
            if (isset($arrIpToLookup[$intI])) {
                $strIP = $arrIpToLookup[$intI]["stats_ip"];

                try {
                    $objRemoteloader = new Remoteloader();
                    $objRemoteloader->setStrHost($this->strIp2cServer);
                    $objRemoteloader->setStrQueryParams("/ip2c.php?ip=".urlencode($strIP)."&domain=".urlencode(_webpath_)."&checksum=".md5(urldecode(_webpath_).$strIP));
                    $strCountry = $objRemoteloader->getRemoteContent();
                }
                catch (Exception $objExeption) {
                    $strCountry = "n.a.";
                }

                $objWorker->saveIp2CountryRecord($strIP, $strCountry);

            }
        }

        //and Create a small progress-info
        $intTotal = $this->getParam("totalCount");
        $floatOnePercent = 100 / $intTotal;
        //and multiply it with the alredy looked up ips
        $intLookupsDone = ((int)$intTotal - $objWorker->getNumberOfIp2cLookups()) * $floatOnePercent;
        $intLookupsDone = round($intLookupsDone, 2);
        if ($intLookupsDone < 0) {
            $intLookupsDone = 0;
        }

        $this->setStrProgressInformation($strReturn);
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
