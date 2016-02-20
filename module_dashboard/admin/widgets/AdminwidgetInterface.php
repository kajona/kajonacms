<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                *
********************************************************************************************************/

namespace Kajona\Dashboard\Admin\Widgets;

/**
 * Interface to be implemented by all adminwidgets
 *
 * @package module_dashboard
 */
interface AdminwidgetInterface {

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     * If you don't need special fields, return null or an empty string instead.
     *
     * @return string
     */
    public function getEditForm();

    /**
     * This method is called, when the widget should generate its' content.
     * Return the complete content using the methods provided by the base class.
     * Do NOT use the toolkit right here!
     *
     * @return string
     */
    public function getWidgetOutput();

    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName();

    /**
     * This callback is triggered on a users' first login into the system.
     * You may use this method to install a widget as a default widget to
     * a users dashboard.
     *
     * @abstract
     *
     * @param $strUserid
     *
     * @return bool
     */
    public function onFistLogin($strUserid);
}


