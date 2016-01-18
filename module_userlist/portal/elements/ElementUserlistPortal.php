<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Userlist\Portal\Elements;

use class_csv;
use class_date;
use class_module_user_group;
use class_module_user_user;
use class_usersources_user_kajona;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * Creates a table-based list of all users including the option to export the list as csv.
 *
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementUserlistPortal extends ElementPortal implements PortalElementInterface {


    public function loadData() {
        $strReturn = "";

        if($this->getAction() == "exportToCsv") {
            $strReturn .= $this->export2csv();
        }
        else {
            $strReturn .= $this->getUserlist();
        }


        return $strReturn;
    }


    private function export2csv() {
        $arrUser = $this->loadUserlist();

        $objCsv = new class_csv(";");
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
        foreach($arrUser as $objOneUser) {
            $objTargetUser = $objOneUser->getObjSourceUser();
            if($objTargetUser instanceof class_usersources_user_kajona) {
                $arrRow = array();
                $arrRow[] = $objTargetUser->getStrName();
                $arrRow[] = $objTargetUser->getStrForename();
                $arrRow[] = $objTargetUser->getStrEmail();
                $arrRow[] = $objTargetUser->getStrStreet();
                $arrRow[] = $objTargetUser->getStrPostal();
                $arrRow[] = $objTargetUser->getStrCity();
                $arrRow[] = $objTargetUser->getStrTel();
                $arrRow[] = $objTargetUser->getStrMobile();
                $arrRow[] = uniStrlen($objTargetUser->getLongDate()) > 5 ? dateToString(new class_date($objTargetUser->getLongDate()), false) : "";

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
    private function getUserlist() {
        $strReturn = "";

        $strTemplateWrapperID = $this->objTemplate->readTemplate("/module_userlist/".$this->arrElementData["char1"], "userlist_wrapper");
        $strTemplateRowID = $this->objTemplate->readTemplate("/module_userlist/".$this->arrElementData["char1"], "userlist_row");

        $arrUserFinal = $this->loadUserlist();

        $strRows = "";

        foreach($arrUserFinal as $objOneUser) {
            $objTargetUser = $objOneUser->getObjSourceUser();
            if($objTargetUser instanceof class_usersources_user_kajona) {
                $arrRow = array();
                $arrRow["userName"] = $objTargetUser->getStrName();
                $arrRow["userForename"] = $objTargetUser->getStrForename();
                $arrRow["userStreet"] = $objTargetUser->getStrStreet();
                $arrRow["userEmail"] = $objTargetUser->getStrEmail();
                $arrRow["userPostal"] = $objTargetUser->getStrPostal();
                $arrRow["userCity"] = $objTargetUser->getStrCity();
                $arrRow["userPhone"] = $objTargetUser->getStrTel();
                $arrRow["userMobile"] = $objTargetUser->getStrMobile();
                $arrRow["userBirthday"] = uniStrlen($objTargetUser->getLongDate()) > 5 ? dateToString(new class_date($objTargetUser->getLongDate()), false) : "";
                $strRows .= $this->fillTemplate($arrRow, $strTemplateRowID);
            }

        }

        $strLink = getLinkPortalHref($this->getPagename(), "", "exportToCsv");

        $strReturn .= $this->fillTemplate(array("userlist_rows" => $strRows, "csvHref" => $strLink), $strTemplateWrapperID);

        return $strReturn;
    }

    /**
     * @return class_module_user_user[]
     */
    private function loadUserlist() {
        //load all users given
        $arrUser = array();
        if(validateSystemid($this->arrElementData["char2"])) {
            $objGroup = new class_module_user_group($this->arrElementData["char2"]);
            $arrUserId = $objGroup->getObjSourceGroup()->getUserIdsForGroup();
            foreach($arrUserId as $strOneUser) {
                $arrUser[] = new class_module_user_user($strOneUser);
            }
        }
        else {
            $arrUser = class_module_user_user::getObjectList();
        }

        //filter against inactive?
        $arrUserFinal = array();
        if($this->arrElementData["int1"] == "1") {
            foreach($arrUser as /** @var class_module_user_user */
                    $objOneUser) {
                if($objOneUser->getIntActive() == "1") {
                    $arrUserFinal[] = $objOneUser;
                }
            }
        }
        elseif($this->arrElementData["int1"] == "2") {
            foreach($arrUser as /** @var class_module_user_user */
                    $objOneUser) {
                if($objOneUser->getIntActive() == "0") {
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
