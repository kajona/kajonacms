<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\Admin;

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
     * @return string
     * @throws Exception
     */
    public function renderForm($strTargetURI, $intButtonConfig = 2)
    {
        $objCarrier = Carrier::getInstance();
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $objLang = Carrier::getInstance()->getObjLang();
        $objFilter = $this->getObjSourceobject();

        //1. Check if post request was send?
        if ($objCarrier->getParam("{$this->getStrFormname()}setcontentfilter") == "true") {
            $objCarrier->setParam("pv", "1");

            // 1.2 Check if filter was reset?
            if ($objCarrier->getParam("reset") != "") {
                $this->resetParams();
            }
        }

        // 2. Init the form
        $this->generateFieldsFromObject();
        $this->updateSourceObject();
        $this->addField(new FormentryHidden($this->getStrFormname(), "setcontentfilter"))->setStrValue("true");

        // 3 Update session with filter object
        Session::getInstance()->setSession($objFilter->getFilterId(), $this->getObjSourceobject());

        // 4. Update Filterform (specific filter form handling)
        $objFilter->updateFilterForm($this);

        // 5. Set form method to GET
        $this->setStrMethod(self::STR_METHOD_GET);

        // 6. Render filter form.
        $strReturn = parent::renderForm($strTargetURI, AdminFormgenerator::BIT_BUTTON_SUBMIT | AdminFormgenerator::BIT_BUTTON_RESET);

        // 7. Display filter active/inactive
        $bitFilterActive = false;
        foreach ($this->getArrFields() as $objOneField) {
            if (!$objOneField instanceof FormentryHidden) {
                $bitFilterActive = $bitFilterActive || $objOneField->getStrValue() != "";
            }
        }

        // 8. Render folder toggle
        $arrFolder = $objToolkit->getLayoutFolderPic($strReturn, $objLang->getLang("filter_show_hide", "system").($bitFilterActive ? $objLang->getLang("commons_filter_active", "system") : ""), "icon_folderOpen", "icon_folderClosed", false);
        $strReturn = $objToolkit->getFieldset($arrFolder[1], $arrFolder[0]);

        return $strReturn;
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

        Session::getInstance()->sessionUnset($objFilter->getFilterId());
        $arrParamsSuffix = array_keys($objFormgenerator->getArrFields());

        // clear params
        foreach($arrParamsSuffix as $strSuffix) {
            $objCarrier->setParam("{$this->getStrFormname()}{$strSuffix}", "");
        }
    }
}
