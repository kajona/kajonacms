<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

namespace Kajona\Pages\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Admin class to handle the paragraphs
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_paragraph.content_id
 */
class ElementRowAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_title
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     *
     * @elementContentTitle
     *
     * @addSearchIndex
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn element_paragraph.paragraph_template
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldTemplateDir /element_row
     * @fieldMandatory
     * @fieldLabel template
     *
     * @addSearchIndex
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
