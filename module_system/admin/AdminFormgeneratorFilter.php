<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\Admin\Formentries\FormentryButton;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\FilterBase;
use Kajona\System\System\Link;
use Kajona\System\System\Session;


/**
 * @author christoph.kappestein@gmail.com
 * @since  5.0
 * @module module_formgenerator
 */
class AdminFormgeneratorFilter extends AdminFormgenerator
{
    /**
     * Constants for filter form
     */
    const STR_FORM_PARAM_RESET = "reset";
    const STR_FORM_PARAM_FILTER = "setcontentfilter";
    const STR_FILTER_REDIRECT = "redirect";

    /**
     * Set to true if filter shall be visible initially
     *
     * @var bool
     */
    private $bitInitiallyVisible = false;

    /**
     * @param string $strFormname
     * @param FilterBase $objSourceobject
     *
     * @throws Exception
     */
    public function __construct($strFormname, $objSourceobject)
    {
        if (!$objSourceobject instanceof FilterBase) {
            throw new Exception("Source object must be an instance of FilterBase object", Exception::$level_ERROR);
        }

        parent::__construct($strFormname, $objSourceobject);
    }

    /**
     * @return FilterBase
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
     *
     * @return string
     * @throws Exception
     */
    public function renderForm($strTargetURI, $intButtonConfig = 2)
    {
        $objCarrier = Carrier::getInstance();
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $objLang = Carrier::getInstance()->getObjLang();
        $objFilter = $this->getObjSourceobject();

        /* Check if post request was send? */
        if ($objCarrier->getParam($this->getFormElementName(self::STR_FORM_PARAM_FILTER)) == "true") {
            $objCarrier->setParam("pv", "1");

            /* Check if filter was reset? */
            if ($objCarrier->getParam(self::STR_FORM_PARAM_RESET) != "") {
                $this->resetParams();
            }
        }

        /* Init the form */
        $this->generateFieldsFromObject();
        $this->updateSourceObject();
        $this->addField(new FormentryHidden($this->getStrFormname(), self::STR_FORM_PARAM_FILTER))->setStrValue("true");

        /* Update Filterform (specific filter form handling) */
        $objFilter->updateFilterForm($this);

        /* Render filter form. */
        $strReturn = parent::renderForm($strTargetURI, AdminFormgenerator::BIT_BUTTON_SUBMIT | AdminFormgenerator::BIT_BUTTON_RESET);

        /* Display filter active/inactive */
        $bitFilterActive = false;
        foreach ($this->getArrFields() as $objOneField) {
            if (!$objOneField instanceof FormentryHidden) {
                $bitFilterActive = $bitFilterActive || $objOneField->getStrValue() != "";
            }
        }

        /* Render folder toggle*/
        $arrFolder = $objToolkit->getLayoutFolderPic(
            $strReturn,
            $objLang->getLang("filter_show_hide", "system") . ($bitFilterActive ? $objLang->getLang("commons_filter_active", "system") : ""),
            "icon_folderOpen",
            "icon_folderClosed",
            $this->getBitInitiallyVisible()
        );

        return $objToolkit->getContentToolbar([$arrFolder[1]]) . $arrFolder[0];
    }

    /**
     * Removes all parameters so that all form fields are empty
     */
    protected function resetParams()
    {
        $objCarrier = Carrier::getInstance();
        $objFilter = $this->getObjSourceobject();

        // we must work on a new formgenerator since we must initialize the fields before the reset
        $objFormgenerator = new self($objFilter->getFilterId(), $objFilter);
        $objFormgenerator->generateFieldsFromObject();

        $arrParamsSuffix = array_keys($objFormgenerator->getArrFields());

        // clear params
        foreach ($arrParamsSuffix as $strSuffix) {
            $objCarrier->setParam($this->getFormElementName($strSuffix), null);
        }
    }

    /**
     * If a formname is set, this method return <formname>_<fieldname>, else the given <fieldname>
     *
     * @param $strFieldName
     *
     * @return string
     */
    private function getFormElementName($strFieldName)
    {
        $strName = $strFieldName;

        if ($this->getStrFormname() != "") {
            $strName = $this->getStrFormname() . "_" . $strFieldName;
        }

        return $strName;
    }

    /**
     * @return boolean
     */
    public function getBitInitiallyVisible()
    {
        return $this->bitInitiallyVisible;
    }

    /**
     * @param boolean $bitInitiallyVisible
     */
    public function setBitInitiallyVisible($bitInitiallyVisible)
    {
        $this->bitInitiallyVisible = $bitInitiallyVisible;
    }
}
