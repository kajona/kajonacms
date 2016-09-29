<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * List of possible http-statuscodes. Can be returned to the client.
 *
 * @todo if we require at leat php 7, the http/1.1 part should be replaced by $_SERVER['SERVER_PROTOCOL']
 * @package module_system
 */
class HttpStatuscodes
{

    /**
     * Status code (200) indicating all went ok
     */
    const SC_OK = "HTTP/1.1 200 OK";

    /**
     * Status code (302) indicating a redirect request
     */
    const SC_REDIRECT = "HTTP/1.1 302 Found";

    /**
     * Status code (400) indicating a malformed request
     */
    const SC_BADREQUEST = "HTTP/1.1 400 Bad Request";

    /**
     * Status code (401) indicating authentication is possible but has failed or not yet been provided.
     *
     */
    const SC_UNAUTHORIZED = "HTTP/1.1 401 Unauthorized";


    /**
     * Status code (403) indicating the server understood the request but refused to fulfill it.
     *
     */
    const SC_FORBIDDEN = "HTTP/1.1 403 Forbidden";

    /**
     * Status code (404) indicating that the requested resource is not available.
     *
     */
    const SC_NOT_FOUND = "HTTP/1.1 404 Not Found";

    /**
     * Status code (304) indicating that a conditional GET operation found that the resource was available and not modified.
     *
     */
    const SC_NOT_MODIFIED = "HTTP/1.1 304 Not Modified";


    /**
     * Status code (500) indicating an error on the serverside. The request was ok but the server encountered an error.
     *
     */
    const SC_INTERNAL_SERVER_ERROR = "HTTP/1.1 500 Internal Server Error";


}
