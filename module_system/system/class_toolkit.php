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

		$arrMime["doc"] 		= array("application/msword", "doc", "icon_word.png");
		$arrMime["xls"] 		= array("application/msexcel", "xls", "icon_excel.png");
		$arrMime["bin"] 		= array("application/octet-stream", "bin", "icon_binary.png");
		$arrMime["dms"] 		= array("application/octet-stream", "dms", "icon_binary.png");
		$arrMime["lha"] 		= array("application/octet-stream", "lha", "icon_binary.png");
		$arrMime["lzh"] 		= array("application/octet-stream", "lzh", "icon_binary.png");
		$arrMime["exe"] 		= array("application/octet-stream", "exe", "icon_binary.png");
		$arrMime["class"] 		= array("application/octet-stream", "class", "icon_binary.png");
		$arrMime["so"] 			= array("application/octet-stream", "so", "icon_binary.png");
		$arrMime["dll"] 		= array("application/octet-stream", "dll", "icon_binary.png");
		$arrMime["dmg"] 		= array("application/octet-stream", "dmg", "icon_binary.png");
		$arrMime["oda"] 		= array("application/oda", "oda", "icon_binary.png");
		$arrMime["ogg"] 		= array("application/ogg", "ogg", "icon_binary.png");
		$arrMime["pdf"] 		= array("application/pdf", "pdf", "icon_binary.png");
		$arrMime["ai"] 			= array("application/postscript", "ai", "icon_binary.png");
		$arrMime["eps"]			= array("application/postscript", "eps", "icon_binary.png");
		$arrMime["ps"] 			= array("application/postscript", "ps", "icon_binary.png");
		$arrMime["rdf"]			= array("application/rdf+xml", "rdf", "icon_binary.png");
		$arrMime["vxml"] 		= array("application/voicexml+xml", "vxml", "icon_binary.png");
		$arrMime["vcd"] 		= array("application/x-cdlink", "vcd", "icon_binary.png");
		$arrMime["dcr"] 		= array("application/x-director", "dcr", "icon_binary.png");
		$arrMime["dir"] 		= array("application/x-director", "dir", "icon_binary.png");
		$arrMime["dxr"] 		= array("application/x-director", "dxr", "icon_binary.png");
		$arrMime["dvi"] 		= array("application/x-dvi", "dvi", "icon_binary.png");
		$arrMime["js"] 			= array("application/x-javascript", "js", "icon_binary.png");
		$arrMime["latex"] 		= array("application/x-latex", "latex", "icon_binary.png");
		$arrMime["swf"] 		= array("application/x-shockwave-flash", "swf", "icon_binary.png");
		$arrMime["sit"] 		= array("application/x-stuffit", "sit", "icon_binary.png");
		$arrMime["tar"] 		= array("application/x-tar", "tar", "icon_binary.png");
		$arrMime["tcl"] 		= array("application/x-tcl", "tcl", "icon_binary.png");
		$arrMime["tex"] 		= array("application/x-tex", "tex", "icon_binary.png");
		$arrMime["texinfo"] 	= array("application/x-texinfo", "texinfo", "icon_binary.png");
		$arrMime["texi"] 		= array("application/x-texinfo", "texi", "icon_binary.png");
		$arrMime["xhtml"] 		= array("application/xhtml+xml", "xhtml", "icon_binary.png");
		$arrMime["xht"] 		= array("application/xhtml+xml", "xht", "icon_binary.png");
		$arrMime["xslt"] 		= array("application/xslt+xml", "xslt", "icon_binary.png");
		$arrMime["xml"] 		= array("application/xml", "xml", "icon_binary.png");
		$arrMime["xsl"] 		= array("application/xml", "xsl", "icon_binary.png");
		$arrMime["dtd"] 		= array("application/xml-dtd", "dtd", "icon_binary.png");
		$arrMime["zip"] 		= array("application/zip", "zip", "icon_binary.png");
		$arrMime["mid"] 		= array("audio/midi", "mid", "icon_sound.png");
		$arrMime["midi"] 		= array("audio/midi", "midi", "icon_sound.png");
		$arrMime["kar"] 		= array("audio/midi", "kar", "icon_sound.png");
		$arrMime["mpga"] 		= array("audio/mpeg", "mpga", "icon_sound.png");
		$arrMime["mp2"] 		= array("audio/mpeg", "mp2", "icon_sound.png");
		$arrMime["mp3"] 		= array("audio/mpeg", "mp3", "icon_sound.png");
		$arrMime["aif"] 		= array("audio/x-aiff", "aif", "icon_sound.png");
		$arrMime["aiff"] 		= array("audio/x-aiff", "aiff", "icon_sound.png");
		$arrMime["aifc"] 		= array("audio/x-aiff", "aifc", "icon_sound.png");
		$arrMime["m3u"] 		= array("audio/x-mpegurl", "m3u", "icon_sound.png");
		$arrMime["ram"] 		= array("audio/x-pn-realaudio", "ram", "icon_sound.png");
		$arrMime["ra"] 			= array("audio/x-pn-realaudio", "ra", "icon_sound.png");
		$arrMime["rm"] 			= array("application/vnd.rn-realmedia", "rm", "icon_sound.png");
		$arrMime["wav"] 		= array("audio/x-wav", "wav", "icon_sound.png");
		$arrMime["bmp"] 		= array("image/bmp", "bmp", "icon_image.png");
		$arrMime["cgm"] 		= array("image/cgm", "cgm", "icon_image.png");
		$arrMime["gif"] 		= array("image/gif", "gif", "icon_image.png");
		$arrMime["ief"] 		= array("image/ief", "ief", "icon_image.png");
		$arrMime["jpeg"] 		= array("image/jpeg", "jpeg", "icon_image.png");
		$arrMime["jpg"] 		= array("image/jpeg", "jpg", "icon_image.png");
		$arrMime["jpe"] 		= array("image/jpeg", "jpe", "icon_image.png");
		$arrMime["png"] 		= array("image/png", "png", "icon_image.png");
		$arrMime["svg"] 		= array("image/svg+xml", "svg", "icon_image.png");
		$arrMime["tiff"] 		= array("image/tiff", "tiff", "icon_image.png");
		$arrMime["tif"] 		= array("image/tiff", "tif", "icon_image.png");
		$arrMime["djvu"] 		= array("image/vnd.djvu", "djvu", "icon_image.png");
		$arrMime["djv"] 		= array("image/vnd.djvu", "djv", "icon_image.png");
		$arrMime["wbmp"] 		= array("image/vnd.wap.wbmp", "wbmp", "icon_image.png");
		$arrMime["pnm"] 		= array("image/x-portable-anymap", "pnm", "icon_image.png");
		$arrMime["pbm"] 		= array("image/x-portable-bitmap", "pbm", "icon_image.png");
		$arrMime["pgm"] 		= array("image/x-portable-graymap", "pgm", "icon_image.png");
		$arrMime["ppm"] 		= array("image/x-portable-pixmap", "ppm", "icon_image.png");
		$arrMime["rgb"] 		= array("image/x-rgb", "rgb", "icon_image.png");
		$arrMime["xbm"] 		= array("image/x-xbitmap", "xbm", "icon_image.png");
		$arrMime["xpm"] 		= array("image/x-xpixmap", "xpm", "icon_image.png");
		$arrMime["xwd"] 		= array("image/x-xwindowdump", "xwd", "icon_image.png");
		$arrMime["ics"] 		= array("text/calendar", "ics", "icon_text.png");
		$arrMime["ifb"] 		= array("text/calendar", "ifb", "icon_text.png");
		$arrMime["css"] 		= array("text/css", "css", "icon_text.png");
		$arrMime["html"]		= array("text/html", "html", "icon_text.png");
		$arrMime["htm"] 		= array("text/html", "htm", "icon_text.png");
		$arrMime["asc"] 		= array("text/plain", "asc", "icon_text.png");
		$arrMime["txt"] 		= array("text/plain", "txt", "icon_text.png");
		$arrMime["php"] 		= array("text/php", "php", "icon_text.png");
		$arrMime["rtx"] 		= array("text/richtext", "rtx", "icon_text.png");
		$arrMime["rtf"] 		= array("text/rtf", "rtf", "icon_text.png");
		$arrMime["sgml"] 		= array("text/sgml", "sgml", "icon_text.png");
		$arrMime["sgm"] 		= array("text/sgml", "sgm", "icon_text.png");
		$arrMime["tsv"] 		= array("text/tab-separated-values", "tsv", "icon_text.png");
		$arrMime["wml"] 		= array("text/vnd.wap.wml", "wml", "icon_text.png");
		$arrMime["wmls"] 		= array("text/vnd.wap.wmlscript", "wmls", "icon_text.png");
		$arrMime["etx"] 		= array("text/x-setext", "etx", "icon_text.png");
		$arrMime["mpeg"] 		= array("video/mpeg", "mpeg", "icon_movie.png");
		$arrMime["mpg"] 		= array("video/mpeg", "mpg", "icon_movie.png");
		$arrMime["mpe"] 		= array("video/mpeg", "mpe", "icon_movie.png");
		$arrMime["qt"] 			= array("video/quicktime", "qt", "icon_movie.png");
		$arrMime["mov"] 		= array("video/quicktime", "mov", "icon_movie.png");
		$arrMime["mxu"] 		= array("video/vnd.mpegurl", "mxu", "icon_movie.png");
		$arrMime["m4u"] 		= array("video/vnd.mpegurl", "m4u", "icon_movie.png");
		$arrMime["avi"] 		= array("video/x-msvideo", "avi", "icon_movie.png");
		$arrMime["movie"] 		= array("video/x-sgi-movie", "movie", "icon_movie.png");

		$arrMime["default"] 	=array("application/octet-stream", "", "icon_binary.png");

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


