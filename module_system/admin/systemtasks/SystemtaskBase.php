<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                               *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryButton;
use Kajona\System\Admin\Formentries\FormentryHeadline;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Database;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\SystemCommon;


/**
 * Base class for all systemtasks. Provides a few methods to be used by the concrete tasks.
 *
 * @package module_system
 * @autor sidler@mulchprod.de
 */
abstract class SystemtaskBase
{

    private $strTextbase = "system";

    /**
     * Instance of class_db
     *
     * @var Database
     */
    private $objDB;

    /**
     * Instance of class_text
     *
     * @var Lang
     */
    private $objLang;

    /**
     * Instance of class_toolkit
     *
     * @var ToolkitAdmin
     */
    protected $objToolkit;

    /**
     * URL used to trigger a reload, e.g. during long tasks
     *
     * @var string
     */
    private $strReloadParam = "";

    /**
     * Infos regarding the current process
     *
     * @var string
     */
    private $strProgressInformation = "";

    /**
     * @var SystemCommon
     */
    private $objSystemCommon;

    /**
     * Indicates, wether the form to set up the task is a multipart-form or not (e.g.
     * for fileuploads)
     *
     * @var bool
     */
    private $bitMultipartform = false;

    /**
     * Default constructor
     */
    public function __construct()
    {

        //load the external objects
        $this->objDB = Carrier::getInstance()->getObjDB();
        $this->objLang = Carrier::getInstance()->getObjLang();
        $this->objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $this->objSystemCommon = new SystemCommon();

    }

    /**
     * A helper to fetch instances of all systemtasks found in the current installation
     *
     * @return SystemtaskBase[]|AdminSystemtaskInterface[]
     */
    public static function getAllSystemtasks()
    {
        $arrFiles = Resourceloader::getInstance()->getFolderContent("/admin/systemtasks/", array(".php"), false, null, function (&$strOneFile, $strPath) {

            $objInstance = Classloader::getInstance()->getInstanceFromFilename($strPath, "Kajona\\System\\Admin\\Systemtasks\\SystemtaskBase");

            if ($objInstance instanceof AdminSystemtaskInterface) {
                $strOneFile = $objInstance;
            }
            else {
                $strOneFile = null;
            }

        });

        return array_filter($arrFiles, function ($objTask) {
            return $objTask != null;
        });
    }

    /**
     * Delegate requests for strings to the text-subsystem
     *
     * @param string $strLangKey
     * @param array $arrParameters
     *
     * @return string
     */
    protected function getLang($strLangKey, $arrParameters = array())
    {
        return $this->objLang->getLang($strLangKey, $this->strTextbase, $arrParameters);
    }

    /**
     * Method invoking the hook-methods to generate a form.
     *
     * @param string $strTargetModule
     * @param string $strTargetAction
     * @param string|AdminFormgenerator $objAdminForm
     *
     * @return string
     */
    public final function generateAdminForm($strTargetModule = "system", $strTargetAction = "systemTasks", $objAdminForm = null)
    {
        $strReturn = "";
        $objAdminForm = $objAdminForm == null ? $this->getAdminForm() : $objAdminForm;

        if ($objAdminForm instanceof AdminFormgenerator) {
            $objAdminForm->addField(new FormentryHidden("", "execute"))->setStrValue("true");
            $objAdminForm->addField(new FormentryButton("", "systemtask_run"))->setStrLabel($this->objLang->getLang("systemtask_run", "system"))->setStrValue("submit");

            if ($this->bitMultipartform) {
                $objAdminForm->setStrFormEncoding(AdminFormgenerator::FORM_ENCTYPE_MULTIPART);
            }

            $strLink = Link::getLinkAdminHref($strTargetModule, $strTargetAction, "task=".$this->getStrInternalTaskName());
            $strReturn = $objAdminForm->renderForm($strLink, 0);
        }
        elseif ($objAdminForm != "") {
            if ($this->bitMultipartform) {
                $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($strTargetModule, $strTargetAction, "task=".$this->getStrInternalTaskName()), "taskParamForm", AdminFormgenerator::FORM_ENCTYPE_MULTIPART);
            }
            else {
                $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($strTargetModule, $strTargetAction, "task=".$this->getStrInternalTaskName()), "taskParamForm");
            }
            $strReturn .= $objAdminForm;
            $strReturn .= $this->objToolkit->formInputHidden("execute", "true");
            $strReturn .= $this->objToolkit->formInputSubmit($this->objLang->getLang("systemtask_run", "system"));
            $strReturn .= $this->objToolkit->formClose();

        }

        return $strReturn;
    }

    /**
     * Sets the current textbase, so a module.
     * If your textfiles are coming along with a module different than module system, pass the name here
     * to enable a proper text-loading.
     *
     * @param string $strModulename
     *
     * @return void
     */
    protected function setStrTextBase($strModulename)
    {
        $this->strTextbase = $strModulename;
    }

    /**
     * Empty implementation, override in subclass!
     *
     * @return AdminFormgenerator
     */
    public function getAdminForm()
    {
    }

    /**
     * Empty implementation, override in subclass!
     *
     * @return string[]
     */
    public function getSubmitParams()
    {
        return "";
    }

    /**
     * Empty implementation, oveerride in subclass!
     *
     * @return string
     */
    public function getStrInternalTaskName()
    {
    }

    /**
     * @param string $strReloadParam
     */
    public function setStrReloadParam($strReloadParam)
    {
        $this->strReloadParam = $strReloadParam;
    }

    /**
     * @return string
     */
    public function getStrReloadParam()
    {
        return $this->strReloadParam;
    }

    /**
     * @return string
     */
    public function getStrReloadUrl()
    {
        if ($this->strReloadParam != "") {
            return getLinkAdminHref("system", "systemTasks", "&task=".$this->getStrInternalTaskName().$this->strReloadParam);
        }
        else {
            return "";
        }
    }

    /**
     * @param string $strProgressInformation
     *
     * @return void
     */
    public function setStrProgressInformation($strProgressInformation)
    {
        $this->strProgressInformation = $strProgressInformation;
    }

    /**
     * @return string
     */
    public function getStrProgressInformation()
    {
        return $this->strProgressInformation;
    }

    /**
     * Delegate to system-kernel, used to read from params.
     * Provides acces to the GET and POST params
     *
     * @param string $strKey
     *
     * @return mixed
     */
    public function getParam($strKey)
    {
        return $this->objSystemCommon->getParam($strKey);
    }

    /**
     * Delegate to system-kernel, used to write to params
     *
     * @param string $strKey
     * @param mixed $strValue
     *
     * @return void
     */
    public function setParam($strKey, $strValue)
    {
        $this->objSystemCommon->setParam($strKey, $strValue);
    }

    /**
     * Indicates, wether the form to set up the task is a multipart-form or not (e.g.
     * for fileuploads)
     *
     * @param bool $bitMultipartform
     */
    public function setBitMultipartform($bitMultipartform)
    {
        $this->bitMultipartform = $bitMultipartform;
    }

}
