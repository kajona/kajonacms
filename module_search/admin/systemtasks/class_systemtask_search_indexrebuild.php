<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                   *
********************************************************************************************************/

/**
 * Rebuild the search index
 *
 * @package module_search
 */
class class_systemtask_search_indexrebuild extends class_systemtask_base implements interface_admin_systemtask {


    private $STR_SESSION_KEY = "class_systemtask_search_indexrebuild";


    /**
     * constructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setStrTextBase("search");
    }

    /**
     * @see interface_admin_systemtast::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "search";
    }

    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "searchindexrebuild";
    }

    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_search_indexrebuild");
    }

    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {

        $objWorker = new class_module_search_indexwriter();

        if(!class_carrier::getInstance()->getObjSession()->sessionIsset($this->STR_SESSION_KEY)) {

            //fetch all records to be indexed
            $strQuery = "SELECT system_id FROM " . _dbprefix_ . "system WHERE system_deleted = 0";
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

            $arrIds = array();
            foreach($arrRows as $arrOneRow)
                $arrIds[] = $arrOneRow["system_id"];


            $objWorker->clearIndex();
            class_carrier::getInstance()->getObjSession()->setSession($this->STR_SESSION_KEY, $arrIds);
            $this->setParam("totalCount", count($arrIds));
        }


        $arrIds = class_carrier::getInstance()->getObjSession()->getSession($this->STR_SESSION_KEY);

        if(count($arrIds) == 0) {
            class_carrier::getInstance()->getObjSession()->sessionUnset($this->STR_SESSION_KEY);
            return $this->objToolkit->getTextRow($this->getLang("worker_indexrebuild_end", array($objWorker->getNumberOfDocuments(), $objWorker->getNumberOfContentEntries())));
        }

        $intMax = 0;
        foreach($arrIds as $intKey => $strOneValue) {

            $objObject = class_objectfactory::getInstance()->getObject($strOneValue);

            if($objObject != null)
                $objWorker->indexObject($objObject);

            unset($arrIds[$intKey]);

            if($intMax++ > 500)
                break;
        }

        class_carrier::getInstance()->getObjSession()->setSession($this->STR_SESSION_KEY, $arrIds);


        //and create a small progress-info
        $intTotal = $this->getParam("totalCount");
        $floatOnePercent = 100 / $intTotal;
        //and multiply it with the already processed records
        $intLookupsDone = ((int)$intTotal - count($arrIds)) * $floatOnePercent;
        $intLookupsDone = round($intLookupsDone, 2);
        if($intLookupsDone < 0) {
            $intLookupsDone = 0;
        }

        $this->setStrProgressInformation($this->getLang("worker_indexrebuild", array($objWorker->getNumberOfDocuments(), $objWorker->getNumberOfContentEntries())));
        $this->setStrReloadParam("&totalCount=" . $this->getParam("totalCount"));

        return $intLookupsDone;
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
        class_carrier::getInstance()->getObjSession()->sessionUnset($this->STR_SESSION_KEY);
        return "";
    }

}
