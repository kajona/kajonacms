<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Pdf\System;


use TCPDF;

require_once(__DIR__ . "/../vendor/autoload.php");

/**
 * Cache directory for temporary files (full path).
 */
define('K_PATH_CACHE', _realpath_ . '/project/temp');

/**
 * Extends the TCPDF class and is being used internally by Pdf.
 * In most cases you won't need the class, so just ignore it.
 *
 * @author sidler
 * @package module_pdf
 * @since 3.3.0
 */
class PdfTcpdf extends TCPDF
{


    protected $bitHeader = true;
    protected $bitFooter = true;

    /**
     *
     * @var PdfHeaderInterface
     */
    protected $objHeader = null;

    /**
     *
     * @var PdfFooterInterface
     */
    protected $objFooter = null;


    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
    }


    //Page header
    public function Header()
    {

        if (!$this->bitHeader) {
            return;
        }

        //save old font
        $strFont = $this->FontFamily;
        $intSize = $this->FontSize;
        $intStyle = $this->FontStyle;


        if ($this->objHeader != null && $this->objHeader instanceof PdfHeaderInterface) {
            $this->objHeader->writeHeader($this);
        }


        $this->SetFont($strFont, $intStyle, $intSize);
    }

    // Page footer
    public function Footer()
    {

        if (!$this->bitFooter) {
            return;
        }

        //save old font
        $strFont = $this->FontFamily;
        $intSize = $this->FontSize;
        $intStyle = $this->FontStyle;

        if ($this->objFooter != null && $this->objFooter instanceof PdfFooterInterface) {
            $this->objFooter->writeFooter($this);
        }

        $this->SetFont($strFont, $intStyle, $intSize);
    }


    public function getTitle()
    {
        return $this->title;
    }


    public function getBitHeader()
    {
        return $this->bitHeader;
    }

    public function setBitHeader($bitHeader)
    {
        $this->setPrintHeader($bitHeader);
        $this->bitHeader = $bitHeader;
    }

    public function getBitFooter()
    {
        return $this->bitFooter;
    }

    public function setBitFooter($bitFooter)
    {
        $this->setPrintFooter($bitFooter);
        $this->bitFooter = $bitFooter;
    }

    public function getObjHeader()
    {
        return $this->objHeader;
    }

    public function setObjHeader($objHeader)
    {
        $this->objHeader = $objHeader;
    }

    public function getObjFooter()
    {
        return $this->objFooter;
    }

    public function setObjFooter($objFooter)
    {
        $this->objFooter = $objFooter;
    }


}
