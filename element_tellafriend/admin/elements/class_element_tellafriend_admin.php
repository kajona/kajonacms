<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * Class to handle the admin-stuff of the tellafriend-element
 *
 * @package element_tellafriend
 * @author sidler@mulchprod.de
 * @targetTable element_tellafriend.content_id
 */
class class_element_tellafriend_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_tellafriend.tellafriend_template
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /element_tellafriend
     */
    private $strTemplate;

    /**
     * @var string
     * @tableColumn element_tellafriend.tellafriend_error
     *
     * @fieldType page
     * @fieldLabel tellafriend_error
     */
    private $strError;

    /**
     * @var string
     * @tableColumn element_tellafriend.tellafriend_success
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
