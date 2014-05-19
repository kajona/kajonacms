<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
        $strReturn = "";
        $objWorker = new class_module_search_indexwriter();
        $intTimeStart = microtime(true);

        $objWorker->clearIndex();
        $objWorker->indexRebuild();

        $intTimeEnd = microtime(true);
        $intTime = $intTimeEnd - $intTimeStart;

        $strReturn .= $this->objToolkit->getTextRow($this->getLang("worker_indexrebuild_end", array($objWorker->getNumberOfDocuments(), $objWorker->getNumberOfContentEntries(), sprintf('%f', $intTime))));
        return $strReturn;
    }

    /**
     * @see interface_admin_systemtast::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
        return "";
    }

}
