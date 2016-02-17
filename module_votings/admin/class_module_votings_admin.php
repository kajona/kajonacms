<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/
use Kajona\System\System\ModelInterface;


/**
 * Admin class of the votings-module. Responsible for editing votings and organizing them.
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 * 
 * @objectList class_module_votings_voting
 * @objectNew class_module_votings_voting
 * @objectEdit class_module_votings_voting
 * @objectListAnswers class_module_votings_answer
 * @objectNewAnswers class_module_votings_answer
 * @objectEditAnswers class_module_votings_answer
 *
 * @autoTestable list,new,listAnswers,newAnswers
 *
 * @module votings
 * @moduleId _votings_module_id_
 */
class class_module_votings_admin extends class_admin_evensimpler implements interface_admin {


    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    } 

    protected function getOutputNaviEntry(ModelInterface $objInstance) {
        if($objInstance instanceof class_module_votings_answer) {
            return $objInstance->getStrDisplayName();
        }
        else if($objInstance instanceof class_module_votings_voting) {
            return getLinkAdmin($this->getArrModule("modul"), "listAnswers", "&systemid=" . $objInstance->getSystemid(), $objInstance->getStrDisplayName());
        }
        
        return parent::getOutputNaviEntry($objInstance);
    }

    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry) {

        if($objListEntry->rightEdit() && $objListEntry instanceof class_module_votings_voting) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "listAnswers", "&systemid=" . $objListEntry->getSystemid(), "", $this->getLang("action_list_answers"), "icon_folderActionOpen"))
            );
        }

        return parent::renderAdditionalActions($objListEntry);
    }
}
