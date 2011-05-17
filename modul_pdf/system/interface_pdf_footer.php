<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: interface_sc_installer.php 2353 2008-12-31 15:22:01Z sidler $                                     *
********************************************************************************************************/

/**
 * Interface for a single pdf footer element
 *
 * @author sidler
 * @package modul_system
 * @since 3.3.0
 */
interface interface_pdf_footer {

    /**
     * Writes the footer for a single page.
     * Use the passed $objPdf to access the pdf.
     *
     * @param class_pdf_tcpdf $objPdf
     */
    public function writeFooter($objPdf);

}
?>