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
 * List of possible http-responsecodes. Can be returned to the client.
 */
class HttpResponsetypes
{

    /**
     * Default xml response type
     */
    const STR_TYPE_XML = "Content-Type: text/xml; charset=utf-8";

    /**
     * Default json response type
     */
    const STR_TYPE_JSON = "Content-Type: application/json; charset=utf-8";

    /**
     * Default html response type
     */
    const STR_TYPE_HTML = "Content-Type: text/html; charset=utf-8";

    /**
     * Default csv response type
     */
    const STR_TYPE_CSV = "Content-type: text/csv";

    const STR_TYPE_JPEG = "Content-type: image/jpeg";

    const STR_TYPE_PNG = "Content-type: image/png";

    const STR_TYPE_GIF = "Content-type: image/gif";

    const STR_TYPE_PHAR = "Content-type: application/phar";

    /**
     * Transforms a reponse-type string into the matching http type
     * Used by the AbstractController class.
     *
     * @param $strType
     *
     * @return string
     * @since 6.2
     */
    public static function getTypeForString($strType)
    {
        switch ($strType) {
            case 'xml':
                return self::STR_TYPE_XML;
                break;
            case 'json':
                return self::STR_TYPE_JSON;
                break;
            case 'csv':
                return self::STR_TYPE_CSV;
                break;
            case 'jpeg':
                return self::STR_TYPE_JPEG;
                break;
            case 'png':
                return self::STR_TYPE_PNG;
                break;
            case 'gif':
                return self::STR_TYPE_GIF;
                break;
            case 'phar':
                return self::STR_TYPE_PHAR;
                break;
            default:
                return self::STR_TYPE_HTML;
        }
    }
}
