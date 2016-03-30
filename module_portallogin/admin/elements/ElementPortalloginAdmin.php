<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Portallogin\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Class to handle the admin-stuff of the portallogin-element
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_plogin.content_id
 */
class ElementPortalloginAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_template
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_portallogin
     */
    private $strTemplate;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_error
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel portallogin_error
     */
    private $strError;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_success
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel commons_page_success
     */
    private $strSuccess;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_logout_success
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel portallogin_logout_success
     */
    private $strLogout;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_profile
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel portallogin_profile
     */
    private $strProfile;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_pwdforgot
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel portallogin_pwdforgot
     * @fieldDDValues [0=>portallogin_editmode_0],[1=>portallogin_editmode_1]
     */
    private $strPwdForgot;

    /**
     * @var string
     * @tableColumn element_plogin.portallogin_editmode
     * @tableColumnDatatype int
     *
     * @fieldType dropdown
     * @fieldLabel portallogin_editmode
     * @fieldDDValues [0=>portallogin_editmode_0],[1=>portallogin_editmode_1]
     */
    private $strEditmode;




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
     * @param string $strPwdForgot
     */
    public function setStrPwdForgot($strPwdForgot) {
        $this->strPwdForgot = $strPwdForgot;
    }

    /**
     * @return string
     */
    public function getStrPwdForgot() {
        return $this->strPwdForgot;
    }

    /**
     * @param string $strProfile
     */
    public function setStrProfile($strProfile) {
        $this->strProfile = $strProfile;
    }

    /**
     * @return string
     */
    public function getStrProfile() {
        return $this->strProfile;
    }

    /**
     * @param string $strLogout
     */
    public function setStrLogout($strLogout) {
        $this->strLogout = $strLogout;
    }

    /**
     * @return string
     */
    public function getStrLogout() {
        return $this->strLogout;
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

    /**
     * @param string $strEditmode
     */
    public function setStrEditmode($strEditmode) {
        $this->strEditmode = $strEditmode;
    }

    /**
     * @return string
     */
    public function getStrEditmode() {
        return $this->strEditmode;
    }






}
