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

        if(!class_module_system_module::getModuleByName("packagemanager")->rightEdit())
            return $this->getLang("commons_error_permissions");

        $objStarDate = new class_date();
        $objEndDate = new class_date(strtotime("+1 month"));
        $arrTodos = class_todo_entry::getOpenTodos($objStarDate, $objEndDate);

        $strReturn .= "<br>";
        $strReturn .= "<table>";
        foreach ($arrTodos as $objTodo) {
            $strReturn .= "<tr>";
            $strReturn .= "<td>" . $objTodo->getStrDisplayName() . "</td>";
            $strReturn .= "</tr>";
        }
        $strReturn .= "</table>";

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
