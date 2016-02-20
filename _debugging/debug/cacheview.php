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
echo "| CACHEVIEW                                                                     |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";


$arrEntries = \Kajona\System\System\Carrier::getAllCacheEntries();

$arrData = array();
$arrHeader = array();

$objText = \Kajona\System\System\Carrier::getInstance()->getObjLang();

$arrHeader[] = "Leasetime";
$arrHeader[] = "Source";
$arrHeader[] = "Language";
$arrHeader[] = "Hash 1";
$arrHeader[] = "Hash 2";
$arrHeader[] = "Hits";
$arrHeader[] = "Size";

foreach ($arrEntries as $objOneEntry) {
    $arrRowData = array();

    $arrRowData[] = timeToString($objOneEntry->getIntLeasetime());
    $arrRowData[] = $objOneEntry->getStrSourceName();
    $arrRowData[] = $objOneEntry->getStrLanguage();
    $arrRowData[] = uniStrTrim($objOneEntry->getStrHash1(), 40);
    $arrRowData[] = uniStrTrim($objOneEntry->getStrHash2(), 40);
    if(_cache_ === true)
        $arrRowData[] = $objOneEntry->getIntEntryHits();
    else
        $arrRowData[] = "n.a.";
    $arrRowData[] = uniStrlen($objOneEntry->getStrContent());

    $arrData[] = $arrRowData;
}

echo "<table border=\"1\">";
echo "<tr>";
  foreach($arrHeader as $strOneHeader)
      echo "<th>".$strOneHeader."</th>";
echo "</tr>";

foreach($arrData as $arrOneRow) {
    echo "<tr>";
      foreach($arrOneRow as $strOneRow)
          echo "<td>".$strOneRow."</td>";
    echo "</tr>";
}

echo "</table>";


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";
