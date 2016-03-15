<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                           *
********************************************************************************************************/

namespace Kajona\Pages\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Admin class to handle the paragraphs
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @targetTable element_paragraph.content_id
 */
class ElementParagraphAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_title
     * @tableColumnDatatype char254
     *
     * @fieldType text
     * @fieldLabel commons_title
     *
     * @elementContentTitle
     *
     * @addSearchIndex
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_content
     * @tableColumnDatatype text
     * @blockEscaping
     *
     * @fieldType wysiwyg
     * @fieldLabel paragraph_content
     *
     * @addSearchIndex
     */
    private $strTextContent = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_link
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel paragraph_link
     *
     * @addSearchIndex
     */
    private $strLink = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_image
     * @tableColumnDatatype char254
     *
     * @fieldType image
     * @fieldLabel commons_image
     *
     * @addSearchIndex
     */
    private $strImage = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_template
     * @tableColumnDatatype char254
     *
     * @fieldType template
     * @fieldLabel template
     * @fieldTemplateDir /element_paragraph
     */
    private $strTemplate = "";


    /**
     * Returns an abstract of the current element
     *
     * @return string
     */
    public function getContentTitle()
    {
        $this->loadElementData();

        if ($this->getStrTitle() != "") {
            return htmlStripTags($this->getStrTitle());
        }
        elseif ($this->getStrTextContent() != "") {
            return uniStrTrim(htmlStripTags($this->getStrTextContent()), 120);
        }
        else {
            return parent::getContentTitle();
        }
    }


    /**
     * @param string $strContent
     */
    public function setStrTextContent($strContent)
    {
        $this->strTextContent = $strContent;
    }

    /**
     * @return string
     */
    public function getStrTextContent()
    {
        return $this->strTextContent;
    }

    /**
     * @param string $strImage
     */
    public function setStrImage($strImage)
    {
        $this->strImage = $strImage;
    }

    /**
     * @return string
     */
    public function getStrImage()
    {
        return $this->strImage;
    }

    /**
     * @param string $strLink
     */
    public function setStrLink($strLink)
    {
        $this->strLink = $strLink;
    }

    /**
     * @return string
     */
    public function getStrLink()
    {
        return $this->strLink;
    }

    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate)
    {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate()
    {
        return $this->strTemplate;
    }

    /**
     * @param string $strTitle
     */
    public function setStrTitle($strTitle)
    {
        $this->strTitle = $strTitle;
    }

    /**
     * @return string
     */
    public function getStrTitle()
    {
        return $this->strTitle;
    }


}
