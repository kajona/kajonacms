<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * @author christoph.kappestein@gmail.com
 * @since  5.0
 * @module module_formgenerator
 */
class class_admin_formgenerator_filter extends class_admin_formgenerator
{
    /**
     * @param string $strFormname
     * @param class_filter_base $objSourceobject
     */
    public function __construct($strFormname, $objSourceobject)
    {
        if (!$objSourceobject instanceof class_filter_base) {
            throw new class_exception("Source object must be an instance of class_filter_base object", class_exception::$level_ERROR);
        }

        parent::__construct($strFormname, $objSourceobject);
    }

    /**
     * @return \class_filter_base
     */
    public function getObjSourceobject()
    {
        return parent::getObjSourceobject();
    }

    /**
     * Renders a filter including session handling for the given filter
     *
     * @param string $strTargetURI
     * @param int $intButtonConfig
     * @return string
     * @throws class_exception
     */
    public function renderForm($strTargetURI, $intButtonConfig = 2)
    {
        $objCarrier = class_carrier::getInstance();
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $objLang = class_carrier::getInstance()->getObjLang();
        $objFilter = $this->getObjSourceobject();

        //1. Check if post request was send?
        if ($objCarrier->getParam("{$objFilter->getFilterId()}_setcontentfilter") == "true") {
            $objCarrier->setParam("pv", "1");
        } else {
            // Get the values from the session
            $objSessionObject = class_session::getInstance()->getSession($objFilter->getFilterId());
            if ($objSessionObject instanceof class_filter_base) {
                $this->setObjSourceobject($objSessionObject);
            }
        }

        // 2. Check if filter was reset?
        if ($objCarrier->getParam("reset") != "") {
            $this->resetParams();
        }

        // 3. Init the form
        $this->generateFieldsFromObject();
        $this->updateSourceObject();
        $this->addField(new class_formentry_hidden($this->getStrFormname(), "setcontentfilter"))->setStrValue("true");
        $objFilter->updateFilterForm($this);

        // 4. Keep filter object in separate variable
        $objFilter = $this->getObjSourceobject();

        // 5. Update session with filter object
        class_session::getInstance()->setSession($objFilter->getFilterId(), $objFilter);

        // 6. Set form method to GET
        $this->setStrMethod(self::STR_METHOD_GET);

        // 7. Render filter form.
        $strReturn = parent::renderForm($strTargetURI, class_admin_formgenerator::BIT_BUTTON_SUBMIT | class_admin_formgenerator::BIT_BUTTON_RESET);

        // 8. Display filter active/inactive
        $bitFilterActive = false;
        foreach ($this->getArrFields() as $objOneField) {
            if (!$objOneField instanceof class_formentry_hidden) {
                $bitFilterActive = $bitFilterActive || $objOneField->getStrValue() != "";
            }
        }

        // 9. Render folder toggle
        $arrFolder = $objToolkit->getLayoutFolderPic($strReturn, $objLang->getLang("filter_show_hide", "agp_commons").($bitFilterActive ? $objLang->getLang("commons_filter_active", "system") : ""), "icon_folderOpen", "icon_folderClosed", false);
        $strReturn = $objToolkit->getFieldset($arrFolder[1], $arrFolder[0]);

        return $strReturn;
    }

    /**
     * Removes all parameters so that all form fields are empty
     */
    protected function resetParams()
    {
        $objCarrier = class_carrier::getInstance();
        $objFilter = $this->getObjSourceobject();

        // we must work on a new formgenerator since we must initialize the fields before the reset
        $objFormgenerator = new self($objFilter->getFilterId(), $objFilter);
        $objFormgenerator->generateFieldsFromObject();

        class_session::getInstance()->sessionUnset($objFilter->getFilterId());
        $arrParamsSuffix = array_keys($objFormgenerator->getArrFields());

        // clear params
        foreach($arrParamsSuffix as $strSuffix) {
            $objCarrier->setParam("{$this->getStrFormname()}_{$strSuffix}", "");
        }
    }

    /**
     * Generates a filter based on the given filter object.
     *
     * @param class_filter_base $objFilter
     * @param string $strAction
     * @return string
     */
    public static function generateFilterForm(class_filter_base $objFilter, $strAction = "list")
    {
        $objFilterForm = new class_admin_formgenerator_filter($objFilter->getFilterId(), $objFilter);
        $strTarget = class_link::getLinkAdminHref($objFilter->getArrModule(), $strAction);

        $strFilter = $objFilterForm->renderForm($strTarget);

        return $strFilter;
    }

}
