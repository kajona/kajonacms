<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Postacomment\Installer;
use class_db;
use interface_sc_installer;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\SamplecontentInstallerInterface;

/**
 * Installer of the postacomment samplecontent
 *
 */
class InstallerSamplecontentPostacomment implements SamplecontentInstallerInterface  {

    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach($arrFolder as $objOneFolder) {
            if($objOneFolder->getStrName() == "mainnavigation") {
                $strNaviFolderId = $objOneFolder->getSystemid();
            }
        }


        $strReturn .= "Creating new postacomment page...\n";

        $objPage = new PagesPage();
        $objPage->setStrName("postacomment");
        $objPage->setStrBrowsername("Postacomment");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strPostacommentPageID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strPostacommentPageID."\n";
        $strReturn .= "Adding pagelement to new page\n";

        if(PagesElement::getElement("postacomment") != null) {
            $objPagelement = new PagesPageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("postacomment");
            $objPagelement->updateObjectToDb($strPostacommentPageID);
            $strElementId = $objPagelement->getSystemid();

            $strQuery = "UPDATE "._dbprefix_."element_universal
                                SET char1 = ?
                                WHERE content_id = ?";

            if($this->objDB->_pQuery($strQuery, array("postacomment_ajax.tpl", $strElementId))) {
                $strReturn .= "Postacomment element created.\n";
            }
            else {
                $strReturn .= "Error creating Postacomment element.\n";
            }
        }

        $strReturn .= "Adding headline-element to new page\n";
        if(PagesElement::getElement("row") != null) {
            $objPagelement = new PagesPageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strPostacommentPageID);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Postacomment Sample", $strElementId))) {
                $strReturn .= "Headline element created.\n";
            }
            else {
                $strReturn .= "Error creating headline element.\n";
            }
        }

        $strReturn .= "Adding paragraph-element to new page\n";
        if(PagesElement::getElement("paragraph") != null) {
            $objPagelement = new PagesPageelement();
            $objPagelement->setStrPlaceholder("text_paragraph");
            $objPagelement->setStrName("text");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($strPostacommentPageID);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "";
                $arrParams[] = "Über das unten stehende Formular kann dieser Seite ein Kommentar hinzugefügt werden.";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "";
                $arrParams[] = "By using the form below, comments may be added to the current page. ";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                    SET paragraph_title = ?,
                                        paragraph_content = ?
                                  WHERE content_id = ? ";

            if($this->objDB->_pQuery($strQuery, $arrParams)) {
                $strReturn .= "Paragraph element created.\n";
            }
            else {
                $strReturn .= "Error creating paragraph element.\n";
            }
        }


        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "postacomment";
    }

}
