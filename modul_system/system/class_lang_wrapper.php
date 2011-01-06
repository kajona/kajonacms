<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/


/**
 * Class managing access to textfiles
 *
 * @package modul_system
 */
class class_lang_wrapper {
    /**
     * the wrapped object
     *
     * @var class_texte
     */
    private $objLang;

    private $strArea = "";

    private $strModule = "";


    /**
     * Creates a new instance, identified by the area / module combination
     *
     * @param class_texte $objLang
     * @param string $strArea
     * @param string $strModule
     */
    public function __construct($objLang, $strArea, $strModule) {
        $this->objLang = $objLang;
        $this->strArea = $strArea;
        $this->strModule = $strModule;
    }

    /**
     * Tries to load a lang-entry using the current area & module setup
     *
     * @param string $strKey
     * @return string
     */
    public function getLang($strKey) {
        return $this->objLang->getText($strKey, $this->strModule, $this->strArea);
    }

}


?>