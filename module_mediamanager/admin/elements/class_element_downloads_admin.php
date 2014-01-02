<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Class representing the admin-part of the downloads element
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @targetTable element_downloads.content_id
 */
class class_element_downloads_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_downloads.download_id
     * @fieldType dropdown
     * @fieldLabel download_id
     */
    private $strRepo;

    /**
     * @var string
     * @tableColumn element_downloads.download_template
     * @fieldType template
     * @fieldLabel template
     * @fieldTemplateDir /module_mediamanager
     */
    private $strTemplate;

    /**
     * @var int
     * @tableColumn element_downloads.download_amount
     * @fieldType text
     * @fieldLabel download_amount
     */
    private $intAmount;


    public function getAdminForm() {
        //Load all archives
        $arrObjArchs = class_module_mediamanager_repo::getObjectList();
        $arrArchives = array();
        foreach($arrObjArchs as $objOneArchive) {
            $arrArchives[$objOneArchive->getSystemid()] = $objOneArchive->getStrDisplayName();
        }

        $objForm = parent::getAdminForm();
        $objForm->getField("repo")->setArrKeyValues($arrArchives);
        return $objForm;
    }

    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate) {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate() {
        return $this->strTemplate;
    }

    /**
     * @param string $strRepo
     */
    public function setStrRepo($strRepo) {
        $this->strRepo = $strRepo;
    }

    /**
     * @return string
     */
    public function getStrRepo() {
        return $this->strRepo;
    }

    /**
     * @param int $intAmount
     */
    public function setIntAmount($intAmount) {
        $this->intAmount = $intAmount;
    }

    /**
     * @return int
     */
    public function getIntAmount() {
        return $this->intAmount;
    }


    

}
