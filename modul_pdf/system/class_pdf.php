<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * This class provides access to a rudimentary pdf-generation.
 *
 * @author sidler
 * @since 3.3.0
 * @package modul_pdf
 */
class class_pdf {

    public static $PAGE_ORIENTATION_PORTRAIT = "P";
    public static $PAGE_ORIENTATION_LANDSCAPE = "L";
    public static $PAGE_FORMAT_A4 = "A4";
    
    public static $PDF_UNIT = "mm";

    public static $TEXT_ALIGN_CENTER = "C";
    public static $TEXT_ALIGN_RIGHT = "R";
    public static $TEXT_ALIGN_LEFT = "L";

    public static $FONT_STYLE_REGULAR = "";
    public static $FONT_STYLE_BOLD = "B";
    public static $FONT_STYLE_ITALIC = "I";
    public static $FONT_STYLE_UNDERLINE = "U";
    public static $FONT_STYLE_LINE_TROUGH = "D";



	/**
	 * @var class_pdf_tcpdf
	 */
	private $objPdf;
	
	public function __construct() {
        
		$this->objPdf = new class_pdf_tcpdf(self::$PAGE_ORIENTATION_PORTRAIT, self::$PDF_UNIT, self::$PAGE_FORMAT_A4);
		
		//document meta data
		$this->objPdf->SetCreator("Kajona V3");
		$this->objPdf->SetAuthor('Kajona PDF Engine');
		$this->objPdf->SetTitle('Kajona PDF');
		$this->objPdf->SetSubject('Kajona - Free Content Management');
		$this->objPdf->SetKeywords('Kajona, PDF, CMS');

        $this->objPdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        //this is the default, so not set excplicitly
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $this->setHeader(new class_pdf_header());
        $this->setFooter(new class_pdf_footer());
	}

    public function setStrTitle($strTitle) {
        $this->objPdf->SetTitle($strTitle);
    }

    public function setStrSubject($strSubject) {
        $this->objPdf->SetSubject($strSubject);
    }

    public function setStrKeywords($strKeywords) {
        $this->objPdf->SetKeywords($strKeywords);
    }

	public function getBitHeader() {
        return $this->objPdf->getBitHeader();
    }

    public function setBitHeader($bitHeader) {
        $this->objPdf->setBitHeader($bitHeader);
    }

    public function getBitFooter() {
        return $this->objPdf->getBitFooter();
    }

    public function setBitFooter($bitFooter) {
        $this->objPdf->setBitFooter($bitFooter);
    }

    public function setHeader($objHeader) {
        $this->objPdf->setObjHeader($objHeader);
    }

    public function setFooter($objFooter) {
        $this->objPdf->setObjFooter($objFooter);
    }

    public function setNumberOfColumns($intNrOfColumns, $intColumnWidth = 0) {
        $this->objPdf->setEqualColumns($intNrOfColumns, $intColumnWidth);
    }

    public function selectColumn($intColumn = 0) {
        $this->objPdf->selectColumn($intColumn);
    }

    public function addBookmark($strTitle, $intLevel = 0) {
        $this->objPdf->Bookmark($strTitle, $intLevel);
    }

    public function addLineBreak() {
        $this->objPdf->Ln();
    }


    /**
     * Finalizes the current page and starts a new one
     * 
     * @param string $PAGE_ORIENTATION one of self::$PAGE_ORIENTATION_PORTRAIT
     * @param string $PAGE_FORMAT one of self::$PAGE_ORIENTATION_LANDSCAPE, self::$PAGE_FORMAT_A4
     */
	public function addPage($PAGE_ORIENTATION = "", $PAGE_FORMAT = "") {
        if($PAGE_ORIENTATION == "")
            $PAGE_ORIENTATION = self::$PAGE_ORIENTATION_PORTRAIT;

        if($PAGE_FORMAT == "")
            $PAGE_FORMAT = self::$PAGE_FORMAT_A4;
            
		$this->objPdf->AddPage($PAGE_ORIENTATION, $PAGE_FORMAT);
	}



    /**
     * Creates a single cell using NO automatic wrapping at the end of the cell.
     * In most cases, addMultiCell is the element you may want to use instead.
     *
     * @see class_pdf::addMultiCell()
     * @param string $strContent
     * @param int $intWidth
     * @param int $intHeight
     * @param array $bitBorders array of boolean: array(top, right, bottom, left)
     * @param string $strAlign one of self::$TEXT_ALIGN_CENTER, self::$TEXT_ALIGN_RIGHT, self::$TEXT_ALIGN_LEFT
     * @param int $bitFill fill the cell with the color set before via setFillColor()
     */
	public function addCell($strContent = '', $intWidth = 0, $intHeight = 0, $bitBorders = array(false, false, false, false), $strAlign = "L", $bitFill = 0 ) {

        $strBorders = "";
        if($bitBorders[0]) $strBorders .= "T";
        if($bitBorders[1]) $strBorders .= "R";
        if($bitBorders[2]) $strBorders .= "B";
        if($bitBorders[3]) $strBorders .= "L";

        if($strBorders == "")
            $strBorders = 0;

		$this->objPdf->Cell($intWidth, $intHeight, $strContent, $strBorders, 1, $strAlign, $bitFill);
	}

    /**
     * Creates a single cell using automatic wrapping at the end of the cell.
     *
     * @param string $strContent
     * @param int $intWidth
     * @param int $intHeight
     * @param array $bitBorders array of boolean: array(top, right, bottom, left)
     * @param string $strAlign one of self::$TEXT_ALIGN_CENTER, self::$TEXT_ALIGN_RIGHT, self::$TEXT_ALIGN_LEFT
     */
    public function addMultiCell($strContent = '', $intWidth = 0, $intHeight = 0, $bitBorders = array(false, false, false, false), $strAlign = "L" ) {

        $strBorders = "";
        if($bitBorders[0]) $strBorders .= "T";
        if($bitBorders[1]) $strBorders .= "R";
        if($bitBorders[2]) $strBorders .= "B";
        if($bitBorders[3]) $strBorders .= "L";

        if($strBorders == "")
            $strBorders = 0;

		$this->objPdf->MultiCell( $intWidth, $intHeight, $strContent, $strBorders, $strAlign );
	}

    /**
     * Adds a single paragraph to the pdf
     *
     * @param string $strText
     * @param string $strAlgin
     */
    public function addParagraph($strText, $strAlgin = "L") {
        $this->addMultiCell($strText, 0, 0, array(false, false, false, false), $strAlgin);
    }

    /**
     * Add a table of contents.
     * To specifiy the position within the document, use the second param.
     * Every bookmark added via addBookmark() will be added.
     *
     * @param string $strTitle
     * @param int $intTargetPage
     * @see class_pdf::addBookmark()
     */
    public function addTableOfContents($strTitle, $intTargetPage = 2) {

        
        // add a new page for TOC
        $this->objPdf->addTOCPage();
        $this->objPdf->selectColumn();

        $this->addMultiCell($strTitle);
        $this->objPdf->Ln();

        // add table of content at page 1
        $this->objPdf->addTOC($intTargetPage, $this->objPdf->getFontFamily(), ' . ', $strTitle);
        // end of TOC page
        $this->objPdf->endTOCPage();
    }

    /**
     * Sets the fon to be used up from the current position.
     *
     * @param string $strFont one of courier, helvetica, symbol, times
     * @param int $intSize
     * @param string $strStyle one of self::$FONT_STYLE_REGULAR, self::$FONT_STYLE_BOLD, self::$FONT_STYLE_ITALIC, self::$FONT_STYLE_UNDERLINE, self::$FONT_STYLE_LINE_TROUGH
     */
    public function setFont($strFont = "helvetica", $intSize = 10, $strStyle = "") {
        $this->objPdf->SetFont($strFont, $strStyle, $intSize);
    }

    /**
     * Adds an image to the current page.
     * @param string $strImage
     * @param int $intX
     * @param int $intY
     * @param int $intWidth
     * @param int $intHeight
     */
    public function addImage($strImage, $intX, $intY, $intWidth = 0, $intHeight = 0) {
        $strFilename = uniStrtolower(basename($strImage));

        if(uniStrpos($strFilename, ".svg") !== false) {
            $this->objPdf->ImageSVG(_realpath_.$strImage, $intX, $intY, $intWidth, $intHeight);
        }
        else {
            $this->objPdf->Image(_realpath_.$strImage, $intX, $intY, $intWidth, $intHeight);
        }
    }


	
	/**
     * Sends the pdf directly to the browser
     *
     * @param string $strFilename
     */
	public function sendPdfToBrowser($strFilename = "kajonaPdf.pdf") {
		$this->objPdf->Output($strFilename, 'I');
	}

    /**
     * Saves the pdf to the filesystem
     *
     * @param string $strFilename
     */
    public function savePdf($strFilename) {
        $this->objPdf->Output(_realpath_.$strFilename, "F");
    }

    /**
     * Sets a fill color, e.g. to be used by addCell lateron
     */
    public function setFillColor($intR, $intG, $intB) {
        $this->objPdf->SetFillColor($intR, $intG, $intB);
    }

    /**
     * Sets a fill color, e.g. to be used by addCell to render the borders
     */
    public function setDrawColor($intR, $intG, $intB) {
        $this->objPdf->SetDrawColor($intR, $intG, $intB);
    }

    /**
     * Returns the current instance of the internal PDF-engine. Use this method
     * only if really really required.
     *
     * @return class_tcpdf
     */
    public function getObjPdf() {
        return $this->objPdf;
    }



}


?>