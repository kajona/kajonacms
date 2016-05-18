<?php

namespace Kajona\Pdf\Tests;

use Kajona\Pdf\System\Pdf;
use Kajona\System\Tests\Testbase;

class PdfTest extends Testbase
{

    public function test()
    {

        //test code
        $strFile = "/files/public/testPdf.pdf";

        $objPdf = new Pdf();
        $objPdf->setStrTitle("Kajona Test PDF ");
        $objPdf->setStrSubject("Testing the pdf classes");

        $objPdf->setBitFooter(false);
        $objPdf->setBitHeader(false);

        $objPdf->addPage();
        $objPdf->addCell("", 0, 100);
        $objPdf->addCell("Sample PDF", 0, 0, array(false, false, false, false), Pdf::$TEXT_ALIGN_CENTER);
        $objPdf->setFont("helvetica", 8, Pdf::$FONT_STYLE_ITALIC);
        $objPdf->addCell("powered by Kajona & TCPDF", 0, 0, array(false, false, false, false), Pdf::$TEXT_ALIGN_CENTER);
        $objPdf->setFont("helvetica", 12, Pdf::$FONT_STYLE_REGULAR);

        $objPdf->setBitHeader(true);
        $objPdf->addPage(); //TOC page
        $objPdf->getObjPdf()->resetColumns();
        $objPdf->setBitFooter(true);

        $objPdf->addBookmark("page 2");
        $objPdf->addCell("Content A on page 2");
        $objPdf->addCell("Content B on page 2");
        $objPdf->addParagraph("This is a sample text. This is a sample text. This is a sample text. This is a sample text. This is a sample text. This is a sample text");

        $objPdf->addPage(Pdf::$PAGE_ORIENTATION_LANDSCAPE);

        $objPdf->addBookmark("page 3");
        $objPdf->addCell("Content A on page 3 in landscape");
        $objPdf->addCell("Content B on page 3 in landscape");

        $objPdf->addPage(Pdf::$PAGE_ORIENTATION_PORTRAIT);

        $objPdf->setFont("helvetica", 12, Pdf::$FONT_STYLE_REGULAR);
        $objPdf->addParagraph("Text in font helvetica");

        $objPdf->setFont("courier", 12, Pdf::$FONT_STYLE_REGULAR);
        $objPdf->addParagraph("Text in font courier");

        $objPdf->setFont("symbol", 12, Pdf::$FONT_STYLE_REGULAR);
        $objPdf->addParagraph("Text in font symbol");

        $objPdf->setFont("times", 12, Pdf::$FONT_STYLE_REGULAR);
        $objPdf->addParagraph("Text in font times");

        $objPdf->setFont("zapfdingbats", 12, Pdf::$FONT_STYLE_REGULAR);
        $objPdf->addParagraph("Text in font zapfdingbats");

        $objPdf->setFont("helvetica", 12, Pdf::$FONT_STYLE_REGULAR);


        $objPdf->addPage();
        $objPdf->addBookmark("multicolumn");
        $objPdf->setNumberOfColumns(2, 75);
        $objPdf->selectColumn(0);
        $objPdf->addCell("Content in Column 0");
        $objPdf->addParagraph("This is a sample text. This is a sample text. This is a sample text. This is a sample text. This is a sample text. This is a sample text");
        $objPdf->selectColumn(1);
        $objPdf->addCell("Content in Column 1");
        $objPdf->addParagraph("This is a sample text. This is a sample text. This is a sample text. This is a sample text. This is a sample text. This is a sample text");

        $objPdf->addPage();
        $objPdf->setNumberOfColumns(0);
        $objPdf->addBookmark("single column");
        $objPdf->addParagraph("This is a sample text. This is a sample text. This is a sample text. This is a sample text. This is a sample text. This is a sample text");

        $objPdf->addTableOfContents("Inhalt");

        $objPdf->savePdf($strFile);
        $this->assertFileExists(_realpath_.$strFile);
    }

}

