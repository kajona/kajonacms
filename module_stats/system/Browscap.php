<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Stats\System;

use phpbrowscap\Exception;


/**
 * A simple wrapper to browscap-php
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 *
 * @module stats
 * @moduleId _stats_modul_id_
 */
class Browscap
{

    /**
     * @var \phpbrowscap\Browscap
     */
    private $objBrowscap;

    public function __construct()
    {
        require_once __DIR__."/../vendor/autoload.php";

        $this->objBrowscap = new \phpbrowscap\Browscap(_realpath_._projectpath_."/temp");
        $this->objBrowscap->doAutoUpdate = false;
        $this->objBrowscap->cacheFilename = "browscap.cache.php";
    }


    /**
     * Returns the browser and version (e.g. chrome 39) for
     * the given useragent
     *
     * @param $strUseragent
     *
     * @return mixed
     * @throws \phpbrowscap\Exception
     */
    public function getBrowserForUseragent($strUseragent)
    {
        $objInfo = $this->objBrowscap->getBrowser($strUseragent);
        return $objInfo->Browser." ".$objInfo->Version;
    }

    /**
     * Returns the platform and version (e.g. OsX 10.10) for
     * the given useragent
     *
     * @param $strUseragent
     *
     * @return mixed
     * @throws \phpbrowscap\Exception
     */
    public function getPlatformForUseragent($strUseragent)
    {
        $objInfo = $this->objBrowscap->getBrowser($strUseragent);
        return $objInfo->Platform." ".$objInfo->Platform_Version;
    }


    /**
     * Updates the internal browscap.ini and the cache file
     *
     * @throws \phpbrowscap\Exception
     */
    public function updateBrowscap()
    {
        try {
            $this->objBrowscap->updateCache();
        }
        catch (Exception $objE) {

        }
    }


}
