<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * List of possible http-responsecodes. Can be returned to the client.
 *
 * @package module_system
 */
class class_http_responsetypes {

    /**
     * Default xml response type
     * @var string
     */
    public static $STR_TYPE_XML = "Content-Type: text/xml; charset=utf-8";

    /**
     * Default json response type
     * @var string
     */
    public static $STR_TYPE_JSON = "Content-Type: application/json; charset=utf-8";



}
