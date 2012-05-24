<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Scriptlets are a way to realize common helper-methods, e.g. in order to convert placeholders
 * to "real" content.
 * Each scriptlet will be called right before passing the generated content to the browser.
 * Since this is done for each call, make sure to avoid complex operations.
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.0
 */
interface interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @abstract
     * @param string $strContent
     * @return string
     */
    public function processContent($strContent);
}
