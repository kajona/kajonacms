<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Userlist\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\Csv;
use Kajona\System\System\UserGroup;
use Kajona\System\System\Usersources\UsersourcesUserKajona;
use Kajona\System\System\UserUser;


/**
 * Creates a table-based list of all users including the option to export the list as csv.
 *
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementUserlistPortal extends ElementPortal implements PortalElementInterface
{


    public function loadData()
    {
        $strReturn = "";

        if ($this->getAction() == "exportToCsv") {
            $strReturn .= $this->export2csv();
        }
        else {
            $strReturn .= $this->getUserlist();
        }


        return $strReturn;
    }


    private function export2csv()
    {
        $arrUser = $this->loadUserlist();

        $objCsv = new Csv(";");
        $objCsv->setArrMapping(
            array(
                $this->getLang("userlistName"),
                $this->getLang("userlistForename"),
                $this->getLang("userlistEmail"),
                $this->getLang("userlistStreet"),
                $this->getLang("userlistPostal"),
                $this->getLang("userlistCity"),
                $this->getLang("userlistPhone"),
                $this->getLang("userlistMobile"),
                $this->getLang("userlistBirthday")
            )
        );

        $arrCsvValues = array();
        foreach ($arrUser as $objOneUser) {
            $objTargetUser = $objOneUser->getObjSourceUser();
            if ($objTargetUser instanceof UsersourcesUserKajona) {
                $arrRow = array();
                $arrRow[] = $objTargetUser->getStrName();
                $arrRow[] = $objTargetUser->getStrForename();
                $arrRow[] = $objTargetUser->getStrEmail();
                $arrRow[] = $objTargetUser->getStrStreet();
                $arrRow[] = $objTargetUser->getStrPostal();
                $arrRow[] = $objTargetUser->getStrCity();
                $arrRow[] = $objTargetUser->getStrTel();
                $arrRow[] = $objTargetUser->getStrMobile();
                $arrRow[] = uniStrlen($objTargetUser->getLongDate()) > 5 ? dateToString(new \Kajona\System\System\Date($objTargetUser->getLongDate()), false) : "";

                $arrCsvValues[] = $arrRow;
            }
        }

        $objCsv->setArrData($arrCsvValues);

        $objCsv->setStrFilename("userlist.csv");
        $objCsv->writeArrayToFile(true);

    }

    /**
     * @return string
     */
    private function getUserlist()
    {
        $strReturn = "";
        $arrUserFinal = $this->loadUserlist();
        $strRows = "";

        foreach ($arrUserFinal as $objOneUser) {
            $objTargetUser = $objOneUser->getObjSourceUser();
            if ($objTargetUser instanceof UsersourcesUserKajona) {
                $arrRow = array();
                $arrRow["userName"] = $objTargetUser->getStrName();
                $arrRow["userForename"] = $objTargetUser->getStrForename();
                $arrRow["userStreet"] = $objTargetUser->getStrStreet();
                $arrRow["userEmail"] = $objTargetUser->getStrEmail();
                $arrRow["userPostal"] = $objTargetUser->getStrPostal();
                $arrRow["userCity"] = $objTargetUser->getStrCity();
                $arrRow["userPhone"] = $objTargetUser->getStrTel();
                $arrRow["userMobile"] = $objTargetUser->getStrMobile();
                $arrRow["userBirthday"] = uniStrlen($objTargetUser->getLongDate()) > 5 ? dateToString(new \Kajona\System\System\Date($objTargetUser->getLongDate()), false) : "";
                $strRows .= $this->objTemplate->fillTemplateFile($arrRow, "/module_userlist/".$this->arrElementData["char1"], "userlist_row");
            }

        }

        $strLink = getLinkPortalHref($this->getPagename(), "", "exportToCsv");
        $strReturn .= $this->objTemplate->fillTemplateFile(array("userlist_rows" => $strRows, "csvHref" => $strLink), "/module_userlist/".$this->arrElementData["char1"], "userlist_wrapper");

        return $strReturn;
    }

    /**
     * @return UserUser[]
     */
    private function loadUserlist()
    {
        //load all users given
        $arrUser = array();
        if (validateSystemid($this->arrElementData["char2"])) {
            $objGroup = new UserGroup($this->arrElementData["char2"]);
            $arrUserId = $objGroup->getObjSourceGroup()->getUserIdsForGroup();
            foreach ($arrUserId as $strOneUser) {
                $arrUser[] = new UserUser($strOneUser);
            }
        }
        else {
            $arrUser = UserUser::getObjectListFiltered();
        }

        //filter against inactive?
        $arrUserFinal = array();
        if ($this->arrElementData["int1"] == "1") {
            foreach ($arrUser as /** @var UserUser */
                     $objOneUser) {
                if ($objOneUser->getIntRecordStatus() == "1") {
                    $arrUserFinal[] = $objOneUser;
                }
            }
        }
        elseif ($this->arrElementData["int1"] == "2") {
            foreach ($arrUser as /** @var UserUser */
                     $objOneUser) {
                if ($objOneUser->getIntRecordStatus() == "0") {
                    $arrUserFinal[] = $objOneUser;
                }
            }
        }
        else {
            $arrUserFinal = $arrUser;
        }

        return $arrUserFinal;
    }
}
