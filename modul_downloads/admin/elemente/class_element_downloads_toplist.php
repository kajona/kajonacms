<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                         *
********************************************************************************************************/


//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");
//needed classes
include_once(_systempath_."/class_modul_downloads_archive.php");

/**
 * Class representing the admin-part of the downloadstoplist element
 *
 * @package modul_downloads
 */
class class_element_downloads_toplist extends class_element_admin implements interface_admin_element {

    /**
     * Constructor
     *
     */
    public function __construct() {
        $arrModule = array();
        $arrModule["name"]          = "element_downloads_toplist";
        $arrModule["author"]        = "sidler@mulchprod.de";
        $arrModule["moduleId"]      = _pages_elemente_modul_id_;
        $arrModule["table"]         = _dbprefix_."element_universal";
        $arrModule["modul"]         = "elemente";

        $arrModule["tableColumns"]  = "char1|char,char2|char,int1|number";

        parent::__construct($arrModule);
    }


   /**
     * Returns a form to edit the element-data
     *
     * @param mixed $arrElementData
     * @return string
     */
    public function getEditForm($arrElementData)    {
        $strReturn = "";
        //Load all archives
        $arrObjArchs = class_modul_downloads_archive::getAllArchives();
        $arrArchives = array();
        foreach ($arrObjArchs as $objOneArchive)
            $arrArchives[$objOneArchive->getSystemid()] = $objOneArchive->getTitle();

        //Build the form
        $strReturn .= $this->objToolkit->formInputDropdown("char1", $arrArchives, $this->getText("dl_toplist_archive"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));
        //Load the available templates
        include_once(_systempath_."/class_filesystem.php");
        $objFilesystem = new class_filesystem();
        $arrTemplates = $objFilesystem->getFilelist("/templates/element_downloads_toplist", ".tpl");
        $arrTemplatesDD = array();
        if(count($arrTemplates) > 0) {
            foreach($arrTemplates as $strTemplate) {
                $arrTemplatesDD[$strTemplate] = $strTemplate;
            }
        }
        $strReturn .= $this->objToolkit->formInputDropdown("char2", $arrTemplatesDD, $this->getText("dl_toplist_template"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" ));
        
        $strReturn .= $this->objToolkit->formInputText("int1", $this->getText("dl_toplist_amount"), (isset($arrElementData["int1"]) ? $arrElementData["int1"] : "5" ));

        return $strReturn;
    }


} //class_element_downloadstoplist.php
?>