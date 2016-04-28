<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Userlist\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\System\System\UserGroup;


/**
 * Class to handle the admin-stuff of the userlist-element
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementUserlistAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_userlist
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldLabel userlist_group
     */
    private $strChar2;


    /**
     * @var string
     * @tableColumn element_universal.int1
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldLabel userlist_status
     * @fieldDDValues [0 =>  userlist_status_all],[1 => userlist_status_active],[2 => userlist_status_inactive]
     */
    private $intInt1;


    public function getAdminForm()
    {

        $arrGroups = UserGroup::getObjectListFiltered();
        $arrGroupsDD = array();
        $arrGroupsDD[0] = $this->getLang("userlist_all");
        if (count($arrGroups) > 0) {
            foreach ($arrGroups as $objOneGroup) {
                $arrGroupsDD[$objOneGroup->getSystemid()] = $objOneGroup->getStrName();
            }
        }

        $objForm = parent::getAdminForm();
        $objForm->getField("char2")->setArrKeyValues($arrGroupsDD);
        return $objForm;
    }

    /**
     * @param string $strChar2
     */
    public function setStrChar2($strChar2)
    {
        $this->strChar2 = $strChar2;
    }

    /**
     * @return string
     */
    public function getStrChar2()
    {
        return $this->strChar2;
    }

    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1)
    {
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1()
    {
        return $this->strChar1;
    }

    /**
     * @param string $intInt1
     */
    public function setIntInt1($intInt1)
    {
        $this->intInt1 = $intInt1;
    }

    /**
     * @return string
     */
    public function getIntInt1()
    {
        return $this->intInt1;
    }


}
