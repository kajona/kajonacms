<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$										    *
********************************************************************************************************/


/**
 * BaseClass for admin / portal toolkits
 * Toolkits are there to serve small patterns used time by time
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_toolkit {
    protected $arrModul = array();
    protected $strSystemid = 0; //Current Systemid
    /**
     * Instance of class_template
     *
     * @var class_template
     */
    protected $objTemplate;

    /**
     * Constructor
     */
    public function __construct() {
        $this->objTemplate = class_carrier::getInstance()->getObjTemplate();
    }


    /**
     * Looks up the MIME-Type fot the passed filename
     *
     * @param string $strFilename
     *
     * @return array[type, suffix, icon]
     */
    public function mimeType($strFilename) {
        $arrMime = array();

        $arrMime["doc"] = array("application/msword", "doc", "icon_word");
        $arrMime["xls"] = array("application/msexcel", "xls", "icon_excel");
        $arrMime["bin"] = array("application/octet-stream", "bin", "icon_binary");
        $arrMime["dms"] = array("application/octet-stream", "dms", "icon_binary");
        $arrMime["lha"] = array("application/octet-stream", "lha", "icon_binary");
        $arrMime["lzh"] = array("application/octet-stream", "lzh", "icon_binary");
        $arrMime["exe"] = array("application/octet-stream", "exe", "icon_binary");
        $arrMime["class"] = array("application/octet-stream", "class", "icon_binary");
        $arrMime["so"] = array("application/octet-stream", "so", "icon_binary");
        $arrMime["dll"] = array("application/octet-stream", "dll", "icon_binary");
        $arrMime["dmg"] = array("application/octet-stream", "dmg", "icon_binary");
        $arrMime["oda"] = array("application/oda", "oda", "icon_binary");
        $arrMime["ogg"] = array("application/ogg", "ogg", "icon_binary");
        $arrMime["pdf"] = array("application/pdf", "pdf", "icon_binary");
        $arrMime["ai"] = array("application/postscript", "ai", "icon_binary");
        $arrMime["eps"] = array("application/postscript", "eps", "icon_binary");
        $arrMime["ps"] = array("application/postscript", "ps", "icon_binary");
        $arrMime["rdf"] = array("application/rdf+xml", "rdf", "icon_binary");
        $arrMime["vxml"] = array("application/voicexml+xml", "vxml", "icon_binary");
        $arrMime["vcd"] = array("application/x-cdlink", "vcd", "icon_binary");
        $arrMime["dcr"] = array("application/x-director", "dcr", "icon_binary");
        $arrMime["dir"] = array("application/x-director", "dir", "icon_binary");
        $arrMime["dxr"] = array("application/x-director", "dxr", "icon_binary");
        $arrMime["dvi"] = array("application/x-dvi", "dvi", "icon_binary");
        $arrMime["js"] = array("application/x-javascript", "js", "icon_binary");
        $arrMime["latex"] = array("application/x-latex", "latex", "icon_binary");
        $arrMime["swf"] = array("application/x-shockwave-flash", "swf", "icon_binary");
        $arrMime["sit"] = array("application/x-stuffit", "sit", "icon_binary");
        $arrMime["tar"] = array("application/x-tar", "tar", "icon_binary");
        $arrMime["tcl"] = array("application/x-tcl", "tcl", "icon_binary");
        $arrMime["tex"] = array("application/x-tex", "tex", "icon_binary");
        $arrMime["texinfo"] = array("application/x-texinfo", "texinfo", "icon_binary");
        $arrMime["texi"] = array("application/x-texinfo", "texi", "icon_binary");
        $arrMime["xhtml"] = array("application/xhtml+xml", "xhtml", "icon_binary");
        $arrMime["xht"] = array("application/xhtml+xml", "xht", "icon_binary");
        $arrMime["xslt"] = array("application/xslt+xml", "xslt", "icon_binary");
        $arrMime["xml"] = array("application/xml", "xml", "icon_binary");
        $arrMime["xsl"] = array("application/xml", "xsl", "icon_binary");
        $arrMime["dtd"] = array("application/xml-dtd", "dtd", "icon_binary");
        $arrMime["zip"] = array("application/zip", "zip", "icon_binary");
        $arrMime["mid"] = array("audio/midi", "mid", "icon_sound");
        $arrMime["midi"] = array("audio/midi", "midi", "icon_sound");
        $arrMime["kar"] = array("audio/midi", "kar", "icon_sound");
        $arrMime["mpga"] = array("audio/mpeg", "mpga", "icon_sound");
        $arrMime["mp2"] = array("audio/mpeg", "mp2", "icon_sound");
        $arrMime["mp3"] = array("audio/mpeg", "mp3", "icon_sound");
        $arrMime["aif"] = array("audio/x-aiff", "aif", "icon_sound");
        $arrMime["aiff"] = array("audio/x-aiff", "aiff", "icon_sound");
        $arrMime["aifc"] = array("audio/x-aiff", "aifc", "icon_sound");
        $arrMime["m3u"] = array("audio/x-mpegurl", "m3u", "icon_sound");
        $arrMime["ram"] = array("audio/x-pn-realaudio", "ram", "icon_sound");
        $arrMime["ra"] = array("audio/x-pn-realaudio", "ra", "icon_sound");
        $arrMime["rm"] = array("application/vnd.rn-realmedia", "rm", "icon_sound");
        $arrMime["wav"] = array("audio/x-wav", "wav", "icon_sound");
        $arrMime["bmp"] = array("image/bmp", "bmp", "icon_image");
        $arrMime["cgm"] = array("image/cgm", "cgm", "icon_image");
        $arrMime["gif"] = array("image/gif", "gif", "icon_image");
        $arrMime["ief"] = array("image/ief", "ief", "icon_image");
        $arrMime["jpeg"] = array("image/jpeg", "jpeg", "icon_image");
        $arrMime["jpg"] = array("image/jpeg", "jpg", "icon_image");
        $arrMime["jpe"] = array("image/jpeg", "jpe", "icon_image");
        $arrMime["png"] = array("image/png", "png", "icon_image");
        $arrMime["svg"] = array("image/svg+xml", "svg", "icon_image");
        $arrMime["tiff"] = array("image/tiff", "tiff", "icon_image");
        $arrMime["tif"] = array("image/tiff", "tif", "icon_image");
        $arrMime["djvu"] = array("image/vnd.djvu", "djvu", "icon_image");
        $arrMime["djv"] = array("image/vnd.djvu", "djv", "icon_image");
        $arrMime["wbmp"] = array("image/vnd.wap.wbmp", "wbmp", "icon_image");
        $arrMime["pnm"] = array("image/x-portable-anymap", "pnm", "icon_image");
        $arrMime["pbm"] = array("image/x-portable-bitmap", "pbm", "icon_image");
        $arrMime["pgm"] = array("image/x-portable-graymap", "pgm", "icon_image");
        $arrMime["ppm"] = array("image/x-portable-pixmap", "ppm", "icon_image");
        $arrMime["rgb"] = array("image/x-rgb", "rgb", "icon_image");
        $arrMime["xbm"] = array("image/x-xbitmap", "xbm", "icon_image");
        $arrMime["xpm"] = array("image/x-xpixmap", "xpm", "icon_image");
        $arrMime["xwd"] = array("image/x-xwindowdump", "xwd", "icon_image");
        $arrMime["ics"] = array("text/calendar", "ics", "icon_text");
        $arrMime["ifb"] = array("text/calendar", "ifb", "icon_text");
        $arrMime["css"] = array("text/css", "css", "icon_text");
        $arrMime["html"] = array("text/html", "html", "icon_text");
        $arrMime["htm"] = array("text/html", "htm", "icon_text");
        $arrMime["asc"] = array("text/plain", "asc", "icon_text");
        $arrMime["txt"] = array("text/plain", "txt", "icon_text");
        $arrMime["php"] = array("text/php", "php", "icon_text");
        $arrMime["rtx"] = array("text/richtext", "rtx", "icon_text");
        $arrMime["rtf"] = array("text/rtf", "rtf", "icon_text");
        $arrMime["sgml"] = array("text/sgml", "sgml", "icon_text");
        $arrMime["sgm"] = array("text/sgml", "sgm", "icon_text");
        $arrMime["tsv"] = array("text/tab-separated-values", "tsv", "icon_text");
        $arrMime["wml"] = array("text/vnd.wap.wml", "wml", "icon_text");
        $arrMime["wmls"] = array("text/vnd.wap.wmlscript", "wmls", "icon_text");
        $arrMime["etx"] = array("text/x-setext", "etx", "icon_text");
        $arrMime["mpeg"] = array("video/mpeg", "mpeg", "icon_movie");
        $arrMime["mpg"] = array("video/mpeg", "mpg", "icon_movie");
        $arrMime["mpe"] = array("video/mpeg", "mpe", "icon_movie");
        $arrMime["qt"] = array("video/quicktime", "qt", "icon_movie");
        $arrMime["mov"] = array("video/quicktime", "mov", "icon_movie");
        $arrMime["mxu"] = array("video/vnd.mpegurl", "mxu", "icon_movie");
        $arrMime["m4u"] = array("video/vnd.mpegurl", "m4u", "icon_movie");
        $arrMime["avi"] = array("video/x-msvideo", "avi", "icon_movie");
        $arrMime["movie"] = array("video/x-sgi-movie", "movie", "icon_movie");

        $arrMime["default"] = array("application/octet-stream", "", "icon_binary");

        //Determing the type
        $strType = "";
        if(uniStrpos($strFilename, ".") !== false) {
            $strType = uniSubstr($strFilename, uniStrrpos($strFilename, ".") + 1);
        }
        else {
            $strType = $strFilename;
        }

        $strType = uniStrtolower($strType);

        //Known Type?
        if(isset($arrMime[$strType])) {
            $arrReturn = $arrMime[$strType];
        }
        else {
            $arrReturn = $arrMime["default"];
            $arrReturn[1] = $strType;
        }


        return $arrReturn;
    }

}


