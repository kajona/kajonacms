<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Postacomment\Admin;

use Kajona\Pages\System\PagesPage;
use Kajona\Postacomment\System\PostacommentPost;
use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Exception;


/**
 * Admin class of the postacomment-module. Responsible for listing posts and organizing them
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @objectList Kajona\\Postacomment\\System\\PostacommentPost
 * @objectEdit Kajona\\Postacomment\\System\\PostacommentPost
 *
 * @module postacomment
 * @moduleId _postacomment_modul_id_
 */
class PostacommentAdmin extends AdminEvensimpler implements AdminInterface
{

    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    /**
     * Returns a list of all categories and all postacomment
     * The list could be filtered by categories
     *
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList()
    {

        //a small filter would be nice...
        $strReturn = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "list"));

        $arrPages = array();
        $arrPages[""] = "---";
        foreach (PagesPage::getAllPages() as $objOnePage) {
            $arrPages[$objOnePage->getSystemid()] = $objOnePage->getStrName();
        }

        $strReturn .= $this->objToolkit->formInputDropdown("filterId", $arrPages, $this->getLang("postacomment_filter"), $this->getParam("filterId"));
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("postacomment_dofilter"));
        $strReturn .= $this->objToolkit->formClose();

        $strReturn .= $this->objToolkit->divider();

        $objArraySectionIterator = new ArraySectionIterator(PostacommentPost::getNumberOfPostsAvailable(false, $this->getParam("filterId")));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(PostacommentPost::loadPostList(false, $this->getParam("filterId"), false, "", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objArraySectionIterator);
        return $strReturn;

    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        return "";
    }


    /**
     * Renders the form to create a new entry
     *
     * @throws Exception
     * @return string
     * @permissions edit
     */
    protected function actionNew()
    {
        throw new Exception("actioNew not supported by module postacomment", Exception::$level_ERROR);
    }


}

