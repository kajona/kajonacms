<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Eventmanager\Installer;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\SamplecontentInstallerInterface;


/**
 * Installer of the eventmanagers samplecontenht
 *
 */
class InstallerSamplecontentEventmanager implements SamplecontentInstallerInterface  {

    /**
     * @var \Kajona\System\System\Database
     */
    private $objDB;
    private $strContentLanguage;

    public function install() {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();


        $strReturn .= "Creating event\n";
        $objEvent = new EventmanagerEvent();
        
        $objEvent->setObjStartDate(new \Kajona\System\System\Date());
        $objEvent->setObjEndDate(new \Kajona\System\System\Date(time()+3600));
        $objEvent->setIntRegistrationRequired(1);
        $objEvent->setIntEventStatus(2);

        if($this->strContentLanguage == "de") {
        	$objEvent->setStrTitle("Start der neuen Webseite mit Kajona");
        	$objEvent->setStrDescription("Die neue Webseite ist online!<br />Als Basis dafür kommt das freie Open Source Content Management System <a href=\"http://www.kajona.de\">Kajona</a>, zum Einsatz.");
        }
        else {
            $objEvent->setStrTitle("Launch of the new website");
        	$objEvent->setStrDescription("The new website is available!<br />The page is based on <a href=\"http://www.kajona.de\">Kajona</a>, a free open source content management system.");
        }

        $strReturn .= "Saving event...\n";
        $objEvent->updateObjectToDb();


        $strReturn .= "Creating events-page\n";
        $objPage = new PagesPage();
        $objPage->setStrName("events");
        $objPage->setStrBrowsername("Events");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strEventsPageId = $objPage->getSystemid();

        $strReturn .= "ID of new page: ".$strEventsPageId."\n";
        $strReturn .= "Adding eventmanager-element to new page\n";
        if(PagesElement::getElement("eventmanager") != null) {
            $objPagelement = new PagesPageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("eventmanager");
            $objPagelement->updateObjectToDb($strEventsPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_universal
                            SET char1 = ?,
                                ".$this->objDB->encloseColumnName("int1")." = ?,
                                ".$this->objDB->encloseColumnName("int2")." = ?
                          WHERE content_id = ? ";
            if($this->objDB->_pQuery($strQuery, array("eventmanager.tpl", 0, 0, $strElementId)))
                $strReturn .= "eventmanger-element created.\n";
            else
                $strReturn .= "Error creating eventmanager-element.\n";
        }

        $strReturn .= "Adding headline-element to new page\n";
        
        if(PagesElement::getElement("row") != null) {
            $objPagelement = new PagesPageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strEventsPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                             SET paragraph_title = ?
                           WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Events", $strElementId)))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";
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
        return "eventmanager";
    }

}
