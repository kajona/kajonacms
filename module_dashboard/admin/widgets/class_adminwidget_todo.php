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
    public function __construct() {
        parent::__construct();

        //register the fields to be persisted and loaded
        $arrCategories = class_todo_entry::getAllCategories();
        $arrKeys = array();
        foreach ($arrCategories as $strTitle => $arrRows) {
            $arrKeys[] = md5($strTitle);
        }

        $this->setPersistenceKeys($arrKeys);
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $arrCategories = class_todo_entry::getAllCategories();
        foreach ($arrCategories as $strTitle => $arrRows) {
            $strKey = md5($strTitle);
            $strReturn .= $this->objToolkit->formInputCheckbox($strKey, $strTitle, $this->getFieldValue($strKey));
        }

        return $strReturn;
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

        $arrCategories = class_todo_entry::getAllCategories();

        if (empty($arrCategories)) {
            $strReturn .= $this->objToolkit->warningBox($this->getLang("no_tasks_available"), "alert-info");
            return $strReturn;
        }

        $bitConfiguration = $this->hasConfiguration();

        foreach ($arrCategories as $strProviderName => $arrTaskCategories) {
            // check whether the category is enabled for the user. If the user has not configured the widget all
            // categories are displayed
            if ($bitConfiguration && !$this->getFieldValue(md5($strProviderName))) {
                continue;
            }

            $strReturn .= $this->objToolkit->formHeadline($strProviderName);

            foreach ($arrTaskCategories as $strKey => $strCategoryName) {
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

                if (count($arrTodos) > 0) {
                    $arrFolder = $this->objToolkit->getLayoutFolderPic($strContent, $strCategoryName . " (" . count($arrTodos) . ")", "icon_folderOpen", "icon_folderClosed", false);
                } else {
                    $arrFolder = $this->objToolkit->getLayoutFolderPic("", $strCategoryName . " (0)", "icon_accept", "icon_accept", false);
                }

                $strReturn .= $this->objToolkit->getFieldset($arrFolder[1], $arrFolder[0]);
            }
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

    protected function hasConfiguration()
    {
        $arrCategories = class_todo_entry::getAllCategories();
        foreach ($arrCategories as $strTitle => $arrRows) {
            $strKey = md5($strTitle);
            $strValue = $this->getFieldValue($strKey);
            if ($strValue !== "") {
                return true;
            }
        }
        return false;
    }
}
