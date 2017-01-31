<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                           *
********************************************************************************************************/

namespace Kajona\Pages\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\System\System\StringUtil;


/**
 * Admin class to handle the richtext
 *
 * @author jschroeter@kajona.de
 *
 * @targetTable element_universal.content_id
 */
class ElementRichtextAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_universal.text
     * @tableColumnDatatype text
     * @blockEscaping
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryWysiwyg
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
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     * @fieldTemplateDir /element_richtext
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

        if ($this->getStrText() != "") {
            return StringUtil::truncate(htmlStripTags($this->getStrText()), 120);
        }
        else {
            return parent::getContentTitle();
        }
    }

    /**
     * @inheritDoc
     */
    public function generateDummyContent()
    {
        parent::generateDummyContent();
        $this->setStrText("Lorem <b>ipsum</b> dolor");
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
     * @param string $strText
     */
    public function setStrText($strText)
    {
        $this->strText = $strText;
    }

    /**
     * @return string
     */
    public function getStrText()
    {
        return $this->strText;
    }


}
