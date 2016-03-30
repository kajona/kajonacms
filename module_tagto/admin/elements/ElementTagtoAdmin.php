<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Tagto\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Class to handle the admin-stuff of the tagto-element
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementTagtoAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_tagto
     */
    private $strChar1;

    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1) {
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1() {
        return $this->strChar1;
    }





}
