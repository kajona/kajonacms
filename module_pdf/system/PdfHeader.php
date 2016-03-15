<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Pdf\System;



/**
 * Sample implementation of a footer.
 *
 * @author sidler
 * @package module_pdf
 * @since 3.3.0
 */
class PdfHeader implements PdfHeaderInterface {


    /**
     * Writes the header for a single page.
     * Use the passed $objPdf to access the pdf.
     *
     * @param PdfTcpdf $objPdf
     */
    public function writeHeader($objPdf) {

        $objPdf->SetY(3);

        $objPdf->SetFont('helvetica', '', 8);

        // Title
        $objPdf->MultiCell(0, 0, $objPdf->getTitle(), "B", "C");


        // Line break
        $objPdf->Ln(30);

    }

}
