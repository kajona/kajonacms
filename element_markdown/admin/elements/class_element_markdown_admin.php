<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * Class to handle the admin-stuff of the markdown-element
 *
 * @package element_markdown
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_markdown_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /element_markdown
     */
    private $strTemplate = "";


    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType text
     *
     */
    private $strSourceUrl = "";



    /**
     * @return string
     */
    public function getStrSourceUrl() {
        return $this->strSourceUrl;
    }

    /**
     * @param string $strSourceUrl
     */
    public function setStrSourceUrl($strSourceUrl) {
        $this->strSourceUrl = $strSourceUrl;
    }

    /**
     * @return string
     */
    public function getStrTemplate() {
        return $this->strTemplate;
    }

    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate) {
        $this->strTemplate = $strTemplate;
    }






}
