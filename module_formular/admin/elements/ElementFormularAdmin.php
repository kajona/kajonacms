<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Formular\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\System\System\Resourceloader;


/**
 * Class to handle the admin-stuff of the formular-element
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_formular.content_id
 */
class ElementFormularAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * @var string
     * @tableColumn element_formular.formular_class
     * @tableColumnDatatype char254
     * @fieldType dropdown
     * @fieldLabel formular_class
     * @fieldMandatory
     *
     * @elementContentTitle
     */
    private $strClass;

    /**
     * @var string
     * @tableColumn element_formular.formular_email
     * @tableColumnDatatype char254
     * @fieldType text
     * @fieldLabel formular_email
     * @fieldValidator Kajona\System\System\Validators\EmailValidator
     * @fieldMandatory
     */
    private $strEmail;

    /**
     * @var string
     * @tableColumn element_formular.formular_success
     * @tableColumnDatatype text
     *
     * @fieldType text
     * @fieldLabel formular_success
     */
    private $strSuccess;

    /**
     * @var string
     * @tableColumn element_formular.formular_error
     * @tableColumnDatatype text
     *
     * @fieldType text
     * @fieldLabel formular_error
     */
    private $strError;

    /**
     * @var string
     * @tableColumn element_formular.formular_template
     * @tableColumnDatatype char254
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_form
     */
    private $strTemplate;

    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrClassesDD = array();
        foreach(Resourceloader::getInstance()->getFolderContent("/portal/forms", array(".php")) as $strClass) {
            $arrClassesDD[$strClass] = $strClass;
        }

        $objForm->getField("class")->setArrKeyValues($arrClassesDD);
        return $objForm;
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

    /**
     * @param string $strEmail
     */
    public function setStrEmail($strEmail) {
        $this->strEmail = $strEmail;
    }

    /**
     * @return string
     */
    public function getStrEmail() {
        return $this->strEmail;
    }

    /**
     * @param string $strClass
     */
    public function setStrClass($strClass) {
        $this->strClass = $strClass;
    }

    /**
     * @return string
     */
    public function getStrClass() {
        return $this->strClass;
    }





}
