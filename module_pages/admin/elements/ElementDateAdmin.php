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


/**
 * Admin class to handle dates
 *
 * @author jschroeter@kajona.de
 *
 * @targetTable element_universal.content_id
 */
class ElementDateAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var int
     * @todo add long column to universal table
     * @tableColumn element_universal.char2
     * @fieldType date
     * @fieldLabel commons_date
     * @fieldMandatory
     *
     * @templateExport
     * @templateMapper date
     */
    private $objDate = 0;

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldTemplateDir /element_date
     * @fieldMandatory
     * @fieldLabel template
     *
     * @addSearchIndex
     */
    private $strTemplate = "";

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
     * @param string $objDate
     */
    public function setObjDate($objDate)
    {
        $this->objDate = $objDate;
    }

    /**
     * @return string
     */
    public function getObjDate()
    {
        return $this->objDate;
    }

}
