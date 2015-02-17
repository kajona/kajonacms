<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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

    /**
     * Define the context the scriptlet is applied to.
     * A combination of contexts is allowed using an or-concatenation.
     * Examples:
     *   return interface_scriptlet::BIT_CONTEXT_ADMIN
     *   return interface_scriptlet::BIT_CONTEXT_ADMIN | BIT_CONTEXT_ADMIN::BIT_CONTEXT_PORTAL_ELEMENT
     * @return mixed
     */
    public function getProcessingContext();


    /**
     * Scriptlet is applied to admin-views
     */
    const BIT_CONTEXT_ADMIN = 2;

    /**
     * Scriptlet is applied to portal-elements (before saving them to
     * the cache)
     */
    const BIT_CONTEXT_PORTAL_ELEMENT = 4;

    /**
     * Scriptlet is applied to generated porta-pages right before passing
     * them back to the browser - so no caching applies
     */
    const BIT_CONTEXT_PORTAL_PAGE = 8;
}
