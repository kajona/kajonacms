<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Packageserver\System;

use class_carrier;
use \Kajona\System\System\Date;
use \Kajona\System\System\Model;
use \Kajona\System\System\ModelInterface;


/**
 * Model for the packageserver-log
 *
 * @author sidler@mulchprod.de
 *
 * @module mediamanager
 * @moduleId _packagemanager_module_id_
 */
class PackageserverLog extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface {

    /**
     * Generates an entry in the logtable
     *
     * @param $strQueryParams
     * @param $strIp
     * @param $strHostname
     */
    public static function generateDlLog($strQueryParams, $strIp, $strHostname) {
        $objDB = class_carrier::getInstance()->getObjDB();
        $strQuery = "INSERT INTO "._dbprefix_."packageserver_log
	                   (log_id, log_query, log_ip, log_hostname, log_date) VALUES
	                   (?, ?, ?, ?, ?)";

        $objDB->_pQuery(
            $strQuery,
            array(
                generateSystemid(),
                $strQueryParams,
                $strIp,
                $strHostname,
                \Kajona\System\System\Date::getCurrentTimestamp()
            )
        );
    }

    /**
     * Loads the records of the packageserver-log
     *
     * @static
     *
     * @param null $intStart
     * @param null $intEnd
     *
     * @return mixed AS ARRAY
     */
    public static function getLogData($intStart = null, $intEnd = null) {
        $strQuery = "SELECT *
					  FROM "._dbprefix_."packageserver_log
					  ORDER BY log_date DESC";
        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);
    }

    /**
     * Counts the number of logs available
     *
     * @return int
     */
    public function getLogDataCount() {
        $strQuery = "SELECT COUNT(*)
					  FROM "._dbprefix_."packageserver_log";
        $arrTemp = $this->objDB->getPRow($strQuery, array());
        return $arrTemp["COUNT(*)"];

    }


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }
}

