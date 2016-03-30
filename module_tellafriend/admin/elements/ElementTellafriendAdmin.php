<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Tellafriend\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Class to handle the admin-stuff of the tellafriend-element
 *
 * @author sidler@mulchprod.de
 * @targetTable element_tellafriend.content_id
 */
class ElementTellafriendAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * @var string
     * @tableColumn element_tellafriend.tellafriend_template
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_tellafriend
     */
    private $strTemplate;

    /**
     * @var string
     * @tableColumn element_tellafriend.tellafriend_error
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel tellafriend_error
     */
    private $strError;

    /**
     * @var string
     * @tableColumn element_tellafriend.tellafriend_success
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel tellafriend_success
     */
    private $strSuccess;




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
     * @param string $strSuccess
     */
    public function setStrSuccess($strSuccess) {
        $this->strSuccess = $strSuccess;
    }

    /**
     * @return string
     */
    public function getStrSuccess() {
        return $this->strSuccess;
    }

    /**
     * @param string $strError
     */
    public function setStrError($strError) {
        $this->strError = $strError;
    }

    /**
     * @return string
     */
    public function getStrError() {
        return $this->strError;
    }


}
