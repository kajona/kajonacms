<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\Admin\AdminModelserializer;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\Exception;
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

        $strSessionKey = $this->getSessionKey($objPage);
        $strObject = Session::getInstance()->getSession($strSessionKey);
        if (!empty($strObject)) {
            $objInstance = AdminModelserializer::unserialize($strObject, AdminModelserializer::STR_ANNOTATION_SERIALIZABLE);
        } else {
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
                $arrData = AdminModelserializer::serialize($objInstance, AdminModelserializer::STR_ANNOTATION_SERIALIZABLE);
                $this->objSession->setSession($strSessionKey, $arrData);

                // if we are at the last step we call each page to persist the entries
                if ($strStep == $this->getLastStep()) {
                    try {
                        $this->objDatabase->transactionBegin();

                        $this->persistSessionObjects();
                        $this->deleteSessionObjects();

                        $this->objDatabase->transactionCommit();

                        $objOnComplete();
                    } catch (Exception $e) {
                        $this->objDatabase->transactionRollback();
                        throw $e;
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
            $strObject = $this->objSession->getSession($this->getSessionKey($objPage));
            if (!empty($strObject)) {
                $objInstance = AdminModelserializer::unserialize($strObject, AdminModelserializer::STR_ANNOTATION_SERIALIZABLE);
                if ($objInstance instanceof Root) {
                    $arrObjects[$strPageStep] = $objInstance;
                    $arrValues[get_class($objPage)] = $objInstance;
                }
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
            $this->objSession->sessionUnset($this->getSessionKey($objPage));
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
     * @return string
     */
    protected function getSessionKey(WizardPageInterface $objPage)
    {
        return self::SESSION_NAMESPACE . substr(md5(get_class($objPage->newObjectInstance())), 0, 8);
    }
}
