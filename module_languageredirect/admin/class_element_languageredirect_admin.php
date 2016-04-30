<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_element_downloads_toplist_admin.php 3577 2011-01-17 20:07:32Z sidler $                         *
********************************************************************************************************/


/**
 *
 * @package element_languageredirect
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_languageredirect_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_universal.char1
     * @fieldType dropdown
     * @fieldLabel char1
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType template
     * @fieldLabel template
     * @fieldTemplateDir /element_languageredirect
     */
    private $strChar2;

    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrLangDD = array();
        foreach(class_module_languages_language::getObjectList() as $objOneLang)
            $arrLangDD[$objOneLang->getSystemid()] = $objOneLang->getStrDisplayName();

        $objForm->getField("char1")->setArrKeyValues($arrLangDD);


        return $objForm;
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






}
