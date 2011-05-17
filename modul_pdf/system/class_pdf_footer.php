<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_user_user.php 2796 2009-06-19 14:59:47Z jschroeter $                                   *
********************************************************************************************************/

/**
 * Sample implementation of a footer.
 *
 * @author sidler
 * @package modul_system
 * @since 3.3.0
 */
class class_pdf_footer implements interface_pdf_footer {


	/**
     * Writes the footer for a single page.
     * Use the passed $objPdf to access the pdf.
     *
     * @param class_pdf_tcpdf $objPdf
     */
    public function writeFooter($objPdf) {

		// Position at 1.5 cm from bottom
		$objPdf->SetY(-10);
		// Set font
		$objPdf->SetFont('helvetica', 'I', 8);
		// Page number
		$objPdf->Cell(0, 0, $objPdf->getAliasNumPage().'/'.$objPdf->getAliasNbPages(), 'T', 0, 'R');

        $objPdf->SetY(-10);

        //date
        $objPdf->Cell(0, 0, ''.timeToString(time(), false), '0', 0, 'L');

	}



}
?>