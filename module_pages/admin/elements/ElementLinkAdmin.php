<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

namespace Kajona\Pages\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\System\System\SystemSetting;


/**
 * Admin class to handle a simple link
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementLinkAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_universal.text
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     * @fieldLabel commons_title
     *
     * @elementContentTitle
     *
     * @addSearchIndex
     * @templateExport
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryPage
     * @fieldMandatory
     * @fieldLabel paragraph_link
     *
     * @addSearchIndex
     * @templateExport
     * @templatemapper pagelink
     */
    private $strPage = "";

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldTemplateDir /element_link
     * @fieldMandatory
     * @fieldLabel template
     *
     * @addSearchIndex
     */
    private $strTemplate = "";

    /**
     * @inheritDoc
     */
    public function generateDummyContent()
    {
        parent::generateDummyContent();
        $this->setStrTitle("Link Title");
        $this->setStrPage(SystemSetting::getConfigValue("_pages_indexpage_"));
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

    /**
     * @return string
     */
    public function getStrPage()
    {
        return $this->strPage;
    }

    /**
     * @param string $strPage
     */
    public function setStrPage($strPage)
    {
        $this->strPage = $strPage;
    }

    
    
}
