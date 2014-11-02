<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                           *
********************************************************************************************************/

/**
 * Admin class to handle the richtext
 *
 * @package module_pages
 * @author jschroeter@kajona.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_richtext_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_universal.text
     * @tableColumnDatatype text
     * @blockEscaping
     *
     * @fieldType wysiwyg
     * @fieldLabel commons_text
     *
     * @addSearchIndex
     * @templateExport
     */
    private $strText = "";

    /**
     * @var string
     * @tableColumn element_universal.char1
     * @tableColumnDatatype char254
     *
     * @fieldType template
     * @fieldLabel template
     * @fieldTemplateDir /element_richtext
     */
    private $strTemplate = "";


    /**
     * Returns an abstract of the current element
     *
     * @return string
     */
    public function getContentTitle() {
        $this->loadElementData();

        if($this->getStrText() != "") {
            return uniStrTrim(htmlStripTags($this->getStrText()), 120);
        }
        else
            return parent::getContentTitle();
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
     * @param string $strText
     */
    public function setStrText($strText) {
        $this->strText = $strText;
    }

    /**
     * @return string
     */
    public function getStrText() {
        return $this->strText;
    }


}
