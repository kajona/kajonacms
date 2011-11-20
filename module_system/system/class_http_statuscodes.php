<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * List of possible http-statuSC_odes. Can be returned to the client.
 *
 * @package modul_system
 */
class class_http_statuscodes {

    
    /**
     * Status code (400) indicating a malformed request
     *
     * @var int
     */
    public static  $strSC_BADREQUEST = "HTTP/1.0 400 Bad Request";
    
    /**
     * Status code (401) indicating authentication is possible but has failed or not yet been provided.
     *
     * @var int
     */
    public static  $strSC_UNAUTHORIZED = "HTTP/1.0 401 Unauthorized";
    
    
    /**
     * Status code (403) indicating the server understood the request but refused to fulfill it.
     *
     * @var int
     */
    public static  $strSC_FORBIDDEN = "HTTP/1.0 403 Forbidden";
          
    
    
          
    /**
     * Status code (404) indicating that the requested resource is not available.
     *
     * @var int
     */
    public static  $strSC_NOT_FOUND = "HTTP/1.0 404 Not Found";
          
   
          
    /**
     * Status code (304) indicating that a conditional GET operation found that the resource was available and not modified.
     *
     * @var int
     */
    public static  $strSC_NOT_MODIFIED = "HTTP/1.0 304 Not Modified";
          
    
    
   
    /**
     * Status code (500) indicating an error on the serverside. The request was ok but the server encountered an error.
     *
     * @var int
     */
    public static  $strSC_INTERNAL_SERVER_ERROR = "HTTP/1.0 500 Internal Server Error";
          

}
?>