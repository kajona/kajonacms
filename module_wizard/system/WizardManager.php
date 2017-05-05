<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Wizard\System;

use Kajona\System\Admin\AdminModelserializer;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\Exception;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Root;
use Kajona\System\System\Session;

/**
 * WizardManager
 *
 * @author christoph.kappestein@artemeon.de
 * @module wizard
 */
class WizardManager
{
    const SESSION_NAMESPACE = "wizard_page_";
    const PARAMETER_STEP = "wizardstep";

    /**
     * @var WizardPageInterface[]
     */
    protected $arrPages = array();

    /**
     * @var Database
     */
    protected $objDatabase;

    /**
     * @var Session
     */
    protected $objSession;

    /**
     * @var ToolkitAdmin
     */
    protected $objToolkit;

    /**
     * @var string
     */
    protected $strNamespace;

    /**
     * WizardManager constructor.
     *
     * @param Database $objDatabase
     * @param Session $objSession
     * @param ToolkitAdmin $objToolkit
     */
    public function __construct(Database $objDatabase, Session $objSession, ToolkitAdmin $objToolkit)
    {
        $this->objDatabase = $objDatabase;
        $this->objSession = $objSession;
        $this->objToolkit = $objToolkit;
    }

    /**
     * @param string $strStep
     * @param WizardPageInterface $objPage
     */
    public function addPage($strStep, WizardPageInterface $objPage)
    {
        $this->arrPages[$strStep] = $objPage;
    }

    /**
     * @param string $strNamespace
     */
    public function setNamespace($strNamespace)
    {
        $this->strNamespace = $strNamespace;
    }

    /**
     * @param string $strUrl
     * @param \Closure $objOnRedirect
     * @param \Closure $objOnComplete
     * @return string
     * @throws Exception
     */
    public function actionWizard($strUrl, \Closure $objOnRedirect, \Closure $objOnComplete)
    {
        $strStep = Carrier::getInstance()->getParam(self::PARAMETER_STEP);
        if (empty($strStep)) {
            $strStep = key($this->arrPages);
        }

        if (!isset($this->arrPages[$strStep])) {
            throw new Exception("Invalid step", Exception::$level_ERROR);
        }

        /** @var WizardPageInterface $objPage */
        $objPage = $this->arrPages[$strStep];

        $objInstance = self::getSessionModel($this->strNamespace, $objPage);
        if ($objInstance === null) {
            $objInstance = $objPage->newObjectInstance();
        }

        $objForm = $objPage->getAdminForm($objInstance);
        $strForm = "";

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER["REQUEST_METHOD"] == "POST") {
            // cancel button
            $strCanceled = Carrier::getInstance()->getParam("backbtn");
            if (!empty($strCanceled)) {
                $objOnRedirect($strUrl."?".self::PARAMETER_STEP."=".$this->getPreviousStep($strStep));
                return "";
            }

            if (!$objForm->validateForm()) {
                // add current step to form
                $objForm->addField(new FormentryHidden("", self::PARAMETER_STEP))
                    ->setStrValue($strStep);

                $strForm = $objForm->renderForm($strUrl, $objPage->getButtonConfig());
            } else {
                // update source object
                $objForm->updateSourceObject();

                // call page on save
                $objPage->onSave($objInstance, $objForm);

                // save in session
                self::setSessionModel($this->strNamespace, $objInstance);

                // if we are at the last step we call each page to persist the entries
                if ($strStep == $this->getLastStep()) {
                    try {
                        $this->objDatabase->transactionBegin();

                        $this->persistSessionObjects();
                        $this->deleteSessionObjects();

                        $this->objDatabase->transactionCommit();

                        $objOnComplete();
                    } catch (Exception $objE) {
                        $this->objDatabase->transactionRollback();
                        throw $objE;
                    }
                } else {
                    // redirect to next step
                    $objOnRedirect($strUrl."?".self::PARAMETER_STEP."=".$this->getNextStep($strStep));
                }

                return "";
            }
        } else {
            // add current step to form
            $objForm->addField(new FormentryHidden("", self::PARAMETER_STEP))
                ->setStrValue($strStep);

            $strForm = $objForm->renderForm($strUrl, $objPage->getButtonConfig());
        }

        return $this->renderNavigation($strStep).$strForm;
    }

    /**
     * Returns the previous step
     *
     * @param string $strStep
     * @return string
     */
    protected function getPreviousStep($strStep)
    {
        $strLastKey = null;
        $bitFound = false;
        foreach ($this->arrPages as $strKey => $objPage) {
            if ($strKey == $strStep) {
                $bitFound = true;
                break;
            }
            $strLastKey = $strKey;
        }
        if ($bitFound && !empty($strLastKey)) {
            return $strLastKey;
        } else {
            return $this->getFirstStep();
        }
    }

    /**
     * Returns the next step
     *
     * @param string $strStep
     * @return string
     */
    protected function getNextStep($strStep)
    {
        $strLastKey = null;
        $bitFound = false;
        foreach ($this->arrPages as $strKey => $objPage) {
            if ($bitFound === true) {
                $strLastKey = $strKey;
                break;
            }
            if ($strKey == $strStep) {
                $bitFound = true;
            }
        }
        if ($bitFound && !empty($strLastKey)) {
            return $strLastKey;
        } else {
            return $this->getLastStep();
        }
    }

    /**
     * Returns the first step
     *
     * @return string
     */
    protected function getFirstStep()
    {
        reset($this->arrPages);
        return key($this->arrPages);
    }

    /**
     * Returns the last step
     *
     * @return string
     */
    protected function getLastStep()
    {
        end($this->arrPages);
        return key($this->arrPages);
    }

    /**
     * We call the persistObject method on every page. Each page receives also as second parameter an array with all
     * objects from the session
     */
    protected function persistSessionObjects()
    {
        $arrObjects = array();
        $arrValues = array();
        foreach ($this->arrPages as $strPageStep => $objPage) {
            $objInstance = self::getSessionModel($this->strNamespace, $objPage);
            if ($objInstance instanceof Root) {
                $arrObjects[$strPageStep] = $objInstance;
                $arrValues[get_class($objPage)] = $objInstance;
            }
        }

        foreach ($arrObjects as $strPageStep => $objInstance) {
            $this->arrPages[$strPageStep]->onPersist($objInstance, $arrValues);
        }
    }

    /**
     * Removes all object session values
     */
    protected function deleteSessionObjects()
    {
        foreach ($this->arrPages as $strPageStep => $objPage) {
            $this->objSession->sessionUnset(self::getSessionKey($this->strNamespace, $objPage));
        }
    }

    /**
     * Renders the html navigation
     *
     * @param string $strStep
     * @return string
     */
    protected function renderNavigation($strStep)
    {
        $arrSteps = array();
        foreach ($this->arrPages as $strPageStep => $objPage) {
            $arrSteps[$strPageStep] = '<a>'.$objPage->getTitle().'</a>';
        }
        return $this->objToolkit->getContentToolbar($arrSteps, $strStep);
    }

    /**
     * Returns the session model of a specific wizard page. Can be used to read the model outside of the wizard
     *
     * @param string $strNamespace
     * @param string|WizardPageInterface $strModelClass
     * @return \Kajona\System\System\ModelInterface|null
     */
    public static function getSessionModel($strNamespace, $strModelClass)
    {
        $strSessionKey = self::getSessionKey($strNamespace, $strModelClass);
        $strObject = Session::getInstance()->getSession($strSessionKey);
        if (!empty($strObject)) {
            return AdminModelserializer::unserialize($strObject, AdminModelserializer::STR_ANNOTATION_SERIALIZABLE);
        } else {
            return null;
        }
    }

    /**
     * Sets the session model of a specific wizard page. Can be used to change the model outside of the wizard
     *
     * @param string $strNamespace
     * @param ModelInterface $objModel
     */
    public static function setSessionModel($strNamespace, ModelInterface $objModel)
    {
        $strSessionKey = self::getSessionKey($strNamespace, get_class($objModel));
        $arrData = AdminModelserializer::serialize($objModel, AdminModelserializer::STR_ANNOTATION_SERIALIZABLE);

        Session::getInstance()->setSession($strSessionKey, $arrData);
    }

    /**
     * @param string $strNamespace
     * @param string $strModelClass
     * @return string
     */
    protected static function getSessionKey($strNamespace, $strModelClass)
    {
        if ($strModelClass instanceof WizardPageInterface) {
            $strModelClass = get_class($strModelClass->newObjectInstance());
        } elseif (is_string($strModelClass)) {
        } else {
            throw new \InvalidArgumentException("Model class must be either a page or string");
        }

        return self::SESSION_NAMESPACE.$strNamespace.substr(md5($strModelClass), 0, 8);
    }
}
