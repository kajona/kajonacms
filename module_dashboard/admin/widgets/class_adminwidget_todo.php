<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$		                        *
********************************************************************************************************/


/**
 * @package module_dashboard
 */
class class_adminwidget_todo extends class_adminwidget implements interface_adminwidget
{
    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        return "";
    }

    /**
     * This method is called, when the widget should generate it's content.
     * Return the complete content using the methods provided by the base class.
     * Do NOT use the toolkit right here!
     *
     * @return string
     */
    public function getWidgetOutput() {
        $strReturn = "";
        $strReturn .= "<br>";

        if(!class_module_system_module::getModuleByName("packagemanager")->rightEdit())
            return $this->getLang("commons_error_permissions");

        $arrCategories = class_todo_entry::getAllCategories();
        foreach ($arrCategories as $strKey => $strLabel) {

            $arrTodos = class_todo_entry::getOpenTodos($strKey);
            $strContent = "";
            $strContent .= $this->objToolkit->listHeader();
            $intI = 0;
            foreach ($arrTodos as $objTodo) {
                $strActions = "";
                $arrModule = $objTodo->getArrModuleNavi();
                if (!empty($arrModule) && is_array($arrModule)) {
                    foreach ($arrModule as $strLink) {
                        $strActions.= $this->objToolkit->listButton($strLink);
                    }
                }
                $strContent .= $this->objToolkit->simpleAdminList($objTodo, $strActions, $intI++);
            }
            $strContent .= $this->objToolkit->listFooter();

            $arrFolder = $this->objToolkit->getLayoutFolderPic($strContent, $strLabel, "icon_folderOpen", "icon_folderClosed", false);
            $strReturn .= $this->objToolkit->getFieldset($arrFolder[1], $arrFolder[0]);
        }

        return $strReturn;
    }

    /**
     * This callback is triggered on a users' first login into the system.
     * You may use this method to install a widget as a default widget to
     * a users dashboard.
     *
     * @param string $strUserid
     *
     * @return bool
     */
    public function onFistLogin($strUserid) {
    }

    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName() {
        return $this->getLang("todo_name");
    }

}
