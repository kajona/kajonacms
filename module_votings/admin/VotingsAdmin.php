<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Votings\Admin;

use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\Votings\System\VotingsAnswer;
use Kajona\Votings\System\VotingsVoting;

/**
 * Admin class of the votings-module. Responsible for editing votings and organizing them.
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 *
 * @objectList Kajona\Votings\System\VotingsVoting
 * @objectNew Kajona\Votings\System\VotingsVoting
 * @objectEdit Kajona\Votings\System\VotingsVoting
 * @objectListAnswers Kajona\Votings\System\VotingsAnswer
 * @objectNewAnswers Kajona\Votings\System\VotingsAnswer
 * @objectEditAnswers Kajona\Votings\System\VotingsAnswer
 *
 * @autoTestable list,new,listAnswers,newAnswers
 *
 * @module votings
 * @moduleId _votings_module_id_
 */
class VotingsAdmin extends AdminEvensimpler implements AdminInterface
{


    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    protected function getOutputNaviEntry(ModelInterface $objInstance)
    {
        if ($objInstance instanceof VotingsAnswer) {
            return $objInstance->getStrDisplayName();
        }
        elseif ($objInstance instanceof VotingsVoting) {
            return Link::getLinkAdmin($this->getArrModule("modul"), "listAnswers", "&systemid=".$objInstance->getSystemid(), $objInstance->getStrDisplayName());
        }

        return parent::getOutputNaviEntry($objInstance);
    }

    protected function renderAdditionalActions(Model $objListEntry)
    {

        if ($objListEntry->rightEdit() && $objListEntry instanceof VotingsVoting) {
            return array(
                $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "listAnswers", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_list_answers"), "icon_folderActionOpen"))
            );
        }

        return parent::renderAdditionalActions($objListEntry);
    }
}
