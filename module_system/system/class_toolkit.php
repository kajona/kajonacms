<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
class class_toolkit  {
	protected $arrModul = array();
	protected $strSystemid = 0;					//Current Systemid
	/**
	 * Instance of class_template
	 *
	 * @var class_template
	 */
	protected $objTemplate;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		$objCarrier = class_carrier::getInstance();
		$this->objTemplate = $objCarrier->getObjTemplate();
	}


	/**
	 * Looks up the MIME-Type fot the passed filename
	 *
	 * @param string $strFilename
	 * @return array[type, suffix, icon]
	 */
	public function mimeType($strFilename) {
		$arrMime = array();

		$arrMime["doc"] 		= array("application/msword", "doc", "icon_word.gif");
		$arrMime["xls"] 		= array("application/msexcel", "xls", "icon_excel.gif");
		$arrMime["bin"] 		= array("application/octet-stream", "bin", "icon_binary.gif");
		$arrMime["dms"] 		= array("application/octet-stream", "dms", "icon_binary.gif");
		$arrMime["lha"] 		= array("application/octet-stream", "lha", "icon_binary.gif");
		$arrMime["lzh"] 		= array("application/octet-stream", "lzh", "icon_binary.gif");
		$arrMime["exe"] 		= array("application/octet-stream", "exe", "icon_binary.gif");
		$arrMime["class"] 		= array("application/octet-stream", "class", "icon_binary.gif");
		$arrMime["so"] 			= array("application/octet-stream", "so", "icon_binary.gif");
		$arrMime["dll"] 		= array("application/octet-stream", "dll", "icon_binary.gif");
		$arrMime["dmg"] 		= array("application/octet-stream", "dmg", "icon_binary.gif");
		$arrMime["oda"] 		= array("application/oda", "oda", "icon_binary.gif");
		$arrMime["ogg"] 		= array("application/ogg", "ogg", "icon_binary.gif");
		$arrMime["pdf"] 		= array("application/pdf", "pdf", "icon_binary.gif");
		$arrMime["ai"] 			= array("application/postscript", "ai", "icon_binary.gif");
		$arrMime["eps"]			= array("application/postscript", "eps", "icon_binary.gif");
		$arrMime["ps"] 			= array("application/postscript", "ps", "icon_binary.gif");
		$arrMime["rdf"]			= array("application/rdf+xml", "rdf", "icon_binary.gif");
		$arrMime["vxml"] 		= array("application/voicexml+xml", "vxml", "icon_binary.gif");
		$arrMime["vcd"] 		= array("application/x-cdlink", "vcd", "icon_binary.gif");
		$arrMime["dcr"] 		= array("application/x-director", "dcr", "icon_binary.gif");
		$arrMime["dir"] 		= array("application/x-director", "dir", "icon_binary.gif");
		$arrMime["dxr"] 		= array("application/x-director", "dxr", "icon_binary.gif");
		$arrMime["dvi"] 		= array("application/x-dvi", "dvi", "icon_binary.gif");
		$arrMime["js"] 			= array("application/x-javascript", "js", "icon_binary.gif");
		$arrMime["latex"] 		= array("application/x-latex", "latex", "icon_binary.gif");
		$arrMime["swf"] 		= array("application/x-shockwave-flash", "swf", "icon_binary.gif");
		$arrMime["sit"] 		= array("application/x-stuffit", "sit", "icon_binary.gif");
		$arrMime["tar"] 		= array("application/x-tar", "tar", "icon_binary.gif");
		$arrMime["tcl"] 		= array("application/x-tcl", "tcl", "icon_binary.gif");
		$arrMime["tex"] 		= array("application/x-tex", "tex", "icon_binary.gif");
		$arrMime["texinfo"] 	= array("application/x-texinfo", "texinfo", "icon_binary.gif");
		$arrMime["texi"] 		= array("application/x-texinfo", "texi", "icon_binary.gif");
		$arrMime["xhtml"] 		= array("application/xhtml+xml", "xhtml", "icon_binary.gif");
		$arrMime["xht"] 		= array("application/xhtml+xml", "xht", "icon_binary.gif");
		$arrMime["xslt"] 		= array("application/xslt+xml", "xslt", "icon_binary.gif");
		$arrMime["xml"] 		= array("application/xml", "xml", "icon_binary.gif");
		$arrMime["xsl"] 		= array("application/xml", "xsl", "icon_binary.gif");
		$arrMime["dtd"] 		= array("application/xml-dtd", "dtd", "icon_binary.gif");
		$arrMime["zip"] 		= array("application/zip", "zip", "icon_binary.gif");
		$arrMime["mid"] 		= array("audio/midi", "mid", "icon_sound.gif");
		$arrMime["midi"] 		= array("audio/midi", "midi", "icon_sound.gif");
		$arrMime["kar"] 		= array("audio/midi", "kar", "icon_sound.gif");
		$arrMime["mpga"] 		= array("audio/mpeg", "mpga", "icon_sound.gif");
		$arrMime["mp2"] 		= array("audio/mpeg", "mp2", "icon_sound.gif");
		$arrMime["mp3"] 		= array("audio/mpeg", "mp3", "icon_sound.gif");
		$arrMime["aif"] 		= array("audio/x-aiff", "aif", "icon_sound.gif");
		$arrMime["aiff"] 		= array("audio/x-aiff", "aiff", "icon_sound.gif");
		$arrMime["aifc"] 		= array("audio/x-aiff", "aifc", "icon_sound.gif");
		$arrMime["m3u"] 		= array("audio/x-mpegurl", "m3u", "icon_sound.gif");
		$arrMime["ram"] 		= array("audio/x-pn-realaudio", "ram", "icon_sound.gif");
		$arrMime["ra"] 			= array("audio/x-pn-realaudio", "ra", "icon_sound.gif");
		$arrMime["rm"] 			= array("application/vnd.rn-realmedia", "rm", "icon_sound.gif");
		$arrMime["wav"] 		= array("audio/x-wav", "wav", "icon_sound.gif");
		$arrMime["bmp"] 		= array("image/bmp", "bmp", "icon_image.gif");
		$arrMime["cgm"] 		= array("image/cgm", "cgm", "icon_image.gif");
		$arrMime["gif"] 		= array("image/gif", "gif", "icon_image.gif");
		$arrMime["ief"] 		= array("image/ief", "ief", "icon_image.gif");
		$arrMime["jpeg"] 		= array("image/jpeg", "jpeg", "icon_image.gif");
		$arrMime["jpg"] 		= array("image/jpeg", "jpg", "icon_image.gif");
		$arrMime["jpe"] 		= array("image/jpeg", "jpe", "icon_image.gif");
		$arrMime["png"] 		= array("image/png", "png", "icon_image.gif");
		$arrMime["svg"] 		= array("image/svg+xml", "svg", "icon_image.gif");
		$arrMime["tiff"] 		= array("image/tiff", "tiff", "icon_image.gif");
		$arrMime["tif"] 		= array("image/tiff", "tif", "icon_image.gif");
		$arrMime["djvu"] 		= array("image/vnd.djvu", "djvu", "icon_image.gif");
		$arrMime["djv"] 		= array("image/vnd.djvu", "djv", "icon_image.gif");
		$arrMime["wbmp"] 		= array("image/vnd.wap.wbmp", "wbmp", "icon_image.gif");
		$arrMime["pnm"] 		= array("image/x-portable-anymap", "pnm", "icon_image.gif");
		$arrMime["pbm"] 		= array("image/x-portable-bitmap", "pbm", "icon_image.gif");
		$arrMime["pgm"] 		= array("image/x-portable-graymap", "pgm", "icon_image.gif");
		$arrMime["ppm"] 		= array("image/x-portable-pixmap", "ppm", "icon_image.gif");
		$arrMime["rgb"] 		= array("image/x-rgb", "rgb", "icon_image.gif");
		$arrMime["xbm"] 		= array("image/x-xbitmap", "xbm", "icon_image.gif");
		$arrMime["xpm"] 		= array("image/x-xpixmap", "xpm", "icon_image.gif");
		$arrMime["xwd"] 		= array("image/x-xwindowdump", "xwd", "icon_image.gif");
		$arrMime["ics"] 		= array("text/calendar", "ics", "icon_text.gif");
		$arrMime["ifb"] 		= array("text/calendar", "ifb", "icon_text.gif");
		$arrMime["css"] 		= array("text/css", "css", "icon_text.gif");
		$arrMime["html"]		= array("text/html", "html", "icon_text.gif");
		$arrMime["htm"] 		= array("text/html", "htm", "icon_text.gif");
		$arrMime["asc"] 		= array("text/plain", "asc", "icon_text.gif");
		$arrMime["txt"] 		= array("text/plain", "txt", "icon_text.gif");
		$arrMime["php"] 		= array("text/php", "php", "icon_text.gif");
		$arrMime["rtx"] 		= array("text/richtext", "rtx", "icon_text.gif");
		$arrMime["rtf"] 		= array("text/rtf", "rtf", "icon_text.gif");
		$arrMime["sgml"] 		= array("text/sgml", "sgml", "icon_text.gif");
		$arrMime["sgm"] 		= array("text/sgml", "sgm", "icon_text.gif");
		$arrMime["tsv"] 		= array("text/tab-separated-values", "tsv", "icon_text.gif");
		$arrMime["wml"] 		= array("text/vnd.wap.wml", "wml", "icon_text.gif");
		$arrMime["wmls"] 		= array("text/vnd.wap.wmlscript", "wmls", "icon_text.gif");
		$arrMime["etx"] 		= array("text/x-setext", "etx", "icon_text.gif");
		$arrMime["mpeg"] 		= array("video/mpeg", "mpeg", "icon_movie.gif");
		$arrMime["mpg"] 		= array("video/mpeg", "mpg", "icon_movie.gif");
		$arrMime["mpe"] 		= array("video/mpeg", "mpe", "icon_movie.gif");
		$arrMime["qt"] 			= array("video/quicktime", "qt", "icon_movie.gif");
		$arrMime["mov"] 		= array("video/quicktime", "mov", "icon_movie.gif");
		$arrMime["mxu"] 		= array("video/vnd.mpegurl", "mxu", "icon_movie.gif");
		$arrMime["m4u"] 		= array("video/vnd.mpegurl", "m4u", "icon_movie.gif");
		$arrMime["avi"] 		= array("video/x-msvideo", "avi", "icon_movie.gif");
		$arrMime["movie"] 		= array("video/x-sgi-movie", "movie", "icon_movie.gif");

		$arrMime["default"] 	=array("application/octet-stream", "", "icon_binary.gif");

		//Determing the type
		$strType = "";
		if(uniStrpos($strFilename, ".") !== false) {
			$strType = uniSubstr($strFilename, uniStrrpos($strFilename, ".")+1);
		}
		else
			$strType = $strFilename;

		$strType = uniStrtolower($strType);

		//Known Type?
		if(isset($arrMime[$strType]))
			$arrReturn = $arrMime[$strType];
		else {
			$arrReturn = $arrMime["default"];
			$arrReturn[1] = $strType;
		}


		return $arrReturn;
	}

}


