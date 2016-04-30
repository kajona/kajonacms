<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Sourcecode\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Class to handle the admin-stuff of the sourcecode-element
 *
 * @package element_sourcecode
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementSourcecodeAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_sourcecode
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.text
     *
     * @fieldType textarea
     * @fieldLabel sourcecode_code
     * @blockEscaping
     *
     * @elementContentTitle
     */
    private $strText;


    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1)
    {
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1()
    {
        return $this->strChar1;
    }

    /**
     * @return string
     */
    public function getStrText()
    {
        return $this->strText;
    }

    /**
     * @param string $intText
     */
    public function setStrText($intText)
    {
        $this->strText = uniStrReplace("<br />", "\n", $intText);
    }


}
