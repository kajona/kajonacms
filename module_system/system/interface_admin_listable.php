<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                          *
********************************************************************************************************/

/**
 * As soon as an entry should be listable in the administration (and makes use of the auto-generated lists),
 * the object should implement interface_admin_listable.
 * The interface enriches the object with additional methods required for a proper rendering.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
interface interface_admin_listable {

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @abstract
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon();


    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @abstract
     * @return string
     */
    public function getStrAdditionalInfo();


    /**
     * If not empty, the returned string is rendered below the common title.
     * @abstract
     * @return string
     */
    public function getStrLongDescription();
}
