<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/


/**
 * Admin class to handle the paragraphs
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @targetTable element_paragraph.content_id
 */
class class_element_row_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_title
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     *
     * @elementContentTitle
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_template
     *
     * @fieldType template
     * @fieldTemplateDir /element_row
     * @fieldMandatory
     * @fieldLabel template
     */
    private $strTemplate = "";


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
     * @param string $strTitle
     */
    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    /**
     * @return string
     */
    public function getStrTitle() {
        return $this->strTitle;
    }



}
