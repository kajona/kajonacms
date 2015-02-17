<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * Class to handle the admin-stuff of the directorybrowser-element
 *
 * @package element_directorybrowser
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_directorybrowser_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /element_directorybrowser
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType text
     * @fieldLabel directory
     * @fieldMandatory
     *
     * @elementContentTitle
     */
    private $strChar2;

    public function getAdminForm() {
        $objForm = parent::getAdminForm();


        $strOpener = getLinkAdminDialog(
            "mediamanager",
            "folderListFolderview",
            "&form_element=char2",
            $this->getLang("commons_open_browser"),
            $this->getLang("commons_open_browser"),
            "icon_externalBrowser",
            $this->getLang("commons_open_browser")
        );

        $objForm->getField("char2")->setStrOpener($strOpener);

        return $objForm;
    }




    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1) {
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1() {
        return $this->strChar1;
    }

    /**
     * @param string $strChar2
     */
    public function setStrChar2($strChar2) {
        $this->strChar2 = $strChar2;
    }

    /**
     * @return string
     */
    public function getStrChar2() {
        return $this->strChar2;
    }


}
