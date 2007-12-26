<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_stats_worker.php                                                                        *
* 	Model for the stat-workers                                                                          *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");

/**
 * Model for a stats-worker
 *
 * @package modul_stats
 */
class class_modul_stats_worker extends class_model implements interface_model  {

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_stats";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _stats_modul_id_;
		$arrModul["table"]       		= _dbprefix_."stats_data";
		$arrModul["modul"]				= "stats";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     * NOT IMPLEMENTED
     *
     */
    public function initObject() {

    }

    /**
     * Updates the current object to the database
     * NOT IMPELEMENTED
     *
     */
    public function updateObjectToDb() {

    }

    /**
     * Loads all ips to update hostnames for the worker "hostnameLookup"
     *
     * @return array
     */
    public function hostnameLookupIpsToLookup() {
        $strQuery = "SELECT stats_ip
                       FROM "._dbprefix_."stats_data
                      WHERE stats_hostname IS NULL
                            OR stats_hostname = ''
                        AND stats_hostname != 'na'
                      GROUP BY stats_ip";

        return $this->objDB->getArray($strQuery);
    }

    /**
     * Updates an record in the statstable. saves the hostname for a given ip
     *
     * @param string $strHostname
     * @param string $strIP
     * @return bool
     */
    public function hostnameLookupSaveHostname($strHostname, $strIP) {
        $strQuery = "UPDATE "._dbprefix_."stats_data
                                    SET stats_hostname = '".$this->objDB->dbsafeString($strHostname)."'
                                  WHERE stats_ip = '".$this->objDB->dbsafeString($strIP)."'";
        return $this->objDB->_query($strQuery);
    }

    /**
     * Resets all hostnames marked as not successfull resolved
     *
     * @return bool
     */
    public function hostnameLookupResetHostnames() {
        //Reset all na hostnames
        $strQuery = "UPDATE "._dbprefix_."stats_data
                        SET stats_hostname = ''
                      WHERE stats_hostname = 'na'";
        return $this->objDB->_query($strQuery);
    }

    /**
     * Creates a row in the stats-data table
     *
     * @param string $strIp
     * @param int $intDate
     * @param string $strPage
     * @param string $strReferer
     * @param string $strBrowser
     * @param string $strLanguage
     * @return bool
     */
    public function createStatsEntry($strIp, $intDate, $strPage, $strReferer, $strBrowser, $strLanguage = "", $strSession = "") {
        if($strSession == "")
            $strSession = $this->objSession->getSessionId();
        $strQuery =
        "INSERT INTO ".$this->arrModule["table"]."
		(stats_id, stats_ip, stats_date, stats_page, stats_referer, stats_browser, stats_session, stats_language) VALUES
		('".$this->generateSystemid()."', '".$this->objDB->dbsafeString($strIp)."', ".$this->objDB->dbsafeString($intDate).",
		 '".$this->objDB->dbsafeString($strPage)."', '".$this->objDB->dbsafeString($strReferer)."', '".$this->objDB->dbsafeString($strBrowser)."',
		 '".$this->objDB->dbsafeString($strSession)."', '".$this->objDB->dbsafeString($strLanguage)."')";

		return $this->objDB->_query($strQuery);
    }


    /**
     * Looks up the number of ips not yet resolved
     *
     * @return int
     */
    public function getNumberOfIpsToLookup() {
        $strQuery = "SELECT count(distinct stats_ip) as anzahl
                       FROM "._dbprefix_."stats_data
                      WHERE stats_hostname IS NULL
                            OR stats_hostname = ''
                        AND stats_hostname != 'na'";

        $arrTemp = $this->objDB->getRow($strQuery);
        return $arrTemp["anzahl"];
    }
    
    
    /**
     * Imports data into the database given as a csv-file
     *
     * @param string $strFilename
     * @return bool
     */
    public function importFromCSV($strFilename) {
        
        //create mapping-array
        $arrMapping = array(
            "stats_ip" => "stats_ip",
            "stats_hostname" => "stats_hostname",
            "stats_date" => "stats_date",
            "stats_page" => "stats_page",
            "stats_language" => "stats_language",
            "stats_referer" => "stats_referer",
            "stats_browser" => "stats_browser",
            "stats_session" => "stats_session",
        );
        
        //run the transformation
        include_once(_systempath_."/class_csv.php");
        try {
            $objCsv = new class_csv();
            $objCsv->setArrMapping($arrMapping);
            $objCsv->setStrFilename("/system/dbdumps/".$strFilename);
            $objCsv->createArrayFromFile();
            $arrData = $objCsv->getArrData();
            
            //insert data in table
            foreach($arrData as $arrOneRow) {
                $this->createStatsEntry($arrOneRow["stats_ip"], $arrOneRow["stats_date"], $arrOneRow["stats_page"], $arrOneRow["stats_referer"],
                                        $arrOneRow["stats_browser"], $arrOneRow["stats_language"], $arrOneRow["stats_session"]);
            }
            return true;
        }
        catch (class_exception $objException) {
            $objException->processException();
        }
        return false;
    }
    
    /**
     * Exports data from the database into a csv-file.
     * If the data was exported successfully, the rows
     * are deleted from the database.
     *
     * @param string $strFilename
     * @param int $intStart
     * @param int $intEnd
     * @return bool
     */
    public function exportDataToCsv($strFilename, $intStart, $intEnd) {
        
        $strFilename = uniStrReplace(".csv", "", $strFilename);
        
        //select data to export
        $strQuery = "SELECT * 
                     FROM ".$this->arrModule["table"]."
                     WHERE stats_date >= ".dbsafeString($intStart)."
                       AND stats_date <= ".dbsafeString($intEnd)."
                  ORDER BY stats_date ASC ";
        $arrRows = $this->objDB->getArray($strQuery);
        
        //create mapping-array
        $arrMapping = array(
            "stats_ip" => "stats_ip",
            "stats_hostname" => "stats_hostname",
            "stats_date" => "stats_date",
            "stats_page" => "stats_page",
            "stats_language" => "stats_language",
            "stats_referer" => "stats_referer",
            "stats_browser" => "stats_browser",
            "stats_session" => "stats_session",
        );
        
        include_once(_systempath_."/class_csv.php");
        try {
            $objCsv = new class_csv();
            $objCsv->setArrData($arrRows);
            $objCsv->setArrMapping($arrMapping);
            $objCsv->setStrFilename("/system/dbdumps/stats_".$strFilename.".csv");
            
            if($objCsv->writeArrayToFile()) {
                //export succeeded, delete rows from table
                $strQuery = " DELETE FROM ".$this->arrModule["table"]."
                                    WHERE stats_date >= ".dbsafeString($intStart)."
                                      AND stats_date <= ".dbsafeString($intEnd)."";
                if($this->objDB->_query($strQuery))
                    return true;
                else 
                    return false;    
            }
        }
        catch (class_exception $objException) {
            $objException->processException();
        }
        return false;
    }

}
?>