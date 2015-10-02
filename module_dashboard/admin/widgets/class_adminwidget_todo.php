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
        $arrCategories = class_todo_repository::getAllCategories();
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
        $arrCategories = class_todo_repository::getAllCategories();
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

        $arrCategories = class_todo_repository::getAllCategories();

        if (empty($arrCategories)) {
            $strReturn .= $this->objToolkit->warningBox($this->getLang("no_tasks_available"), "alert-info");
            return $strReturn;
        }

        $bitConfiguration = $this->hasConfiguration();
        $bitHasEntries = false;
        $arrValues = array();

        foreach ($arrCategories as $strProviderName => $arrTaskCategories) {
            if (empty($arrTaskCategories)) {
                continue;
            }

            // check whether the category is enabled for the user. If the user has not configured the widget all
            // categories are displayed
            if ($bitConfiguration && !$this->getFieldValue(md5($strProviderName))) {
                continue;
            }

            $bitHasEntries = true;

            foreach ($arrTaskCategories as $strKey => $strCategoryName) {
                $arrTodos = class_todo_repository::getOpenTodos($strKey);

                if (count($arrTodos) > 0) {
                    $strLink = class_link::getLinkAdmin("dashboard", "todo", "listfilter_category=" . $strKey, count($arrTodos));
                    $arrValues[] = array($strProviderName, $strCategoryName, $strLink);
                } else {
                    $strIcon = class_adminskin_helper::getAdminImage("icon_accept");
                    $arrValues[] = array($strProviderName, $strCategoryName, $strIcon);
                }
            }
        }

        $strReturn .= $this->objToolkit->dataTable(array(), $arrValues);

        if (!$bitHasEntries) {
            $strReturn .= $this->objToolkit->warningBox($this->getLang("no_tasks_available"), "alert-info");
            return $strReturn;
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
        $arrCategories = class_todo_repository::getAllCategories();
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
