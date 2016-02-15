<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Searchindex analyzer                                                          |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";


$arrRow = class_db::getInstance()->getPRow("SELECT COUNT(*) as anz FROM "._dbprefix_."search_ix_document", array());
echo "Documents in doc table:                     ".$arrRow["anz"]."\n";

$arrRow = class_db::getInstance()->getPRow("SELECT COUNT(DISTINCT(search_ix_system_id)) as anz FROM "._dbprefix_."search_ix_document", array());
echo "Unique documents in doc table:              ".$arrRow["anz"]."\n";

$arrRow = class_db::getInstance()->getPRow("SELECT COUNT(*) as anz FROM "._dbprefix_."system", array());
echo "Records in system table:                    ".$arrRow["anz"]."\n";


$arrRow = class_db::getInstance()->getPRow("SELECT COUNT(*) as anz FROM "._dbprefix_."search_ix_content", array());
echo "Entries in content:                         ".$arrRow["anz"]."\n";

echo "\n";

$arrRow = class_db::getInstance()->getPRow("SELECT COUNT(DISTINCT(search_ix_content_document_id)) as anz FROM "._dbprefix_."search_ix_content", array());
echo "Unique documents in content table:          ".$arrRow["anz"]."\n";

$arrRow = class_db::getInstance()->getPRow(
    "SELECT COUNT(DISTINCT(search_ix_content_document_id)) as anz FROM "._dbprefix_."search_ix_content as con
  LEFT JOIN "._dbprefix_."search_ix_document as doc ON con.search_ix_content_document_id = doc.search_ix_document_id
  WHERE doc.search_ix_document_id IS NULL", array()
);
echo "Documents in content missing from document: ".$arrRow["anz"]."\n";


$arrRow = class_db::getInstance()->getPRow(
    "SELECT COUNT(*) as anz FROM "._dbprefix_."search_ix_content as con
  LEFT JOIN "._dbprefix_."search_ix_document as doc ON con.search_ix_content_document_id = doc.search_ix_document_id
  WHERE doc.search_ix_document_id IS NULL", array()
);
echo "Rows to be deleted from content:            ".$arrRow["anz"]."\n";



echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


