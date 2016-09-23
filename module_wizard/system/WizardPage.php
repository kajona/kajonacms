<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Wizard\System;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\AdminFormgeneratorFactory;
use Kajona\System\System\Carrier;
use Kajona\System\System\Lang;
use Kajona\System\System\Root;

/**
 * WizardPage
 *
 * @author christoph.kappestein@artemeon.de
 * @module wizard
 */
class WizardPage implements WizardPageInterface
{
    /**
     * @var string
     */
    protected $strModelClass;

    /**
     * @var integer
     */
    protected $intButtonConfig;

    /**
     * @var string
     */
    protected $strTitle;

    /**
     * WizardPage constructor.
     *
     * @param string $strModelClass
     * @param int $intButtonConfig
     * @param string $strTitle
     */
    public function __construct($strModelClass, $intButtonConfig, $strTitle)
    {
        $this->strModelClass = $strModelClass;
        $this->intButtonConfig = $intButtonConfig;
        $this->strTitle = $strTitle;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->strTitle;
    }

    /**
     * @inheritdoc
     */
    public function getButtonConfig()
    {
        return $this->intButtonConfig;
    }

    /**
     * @inheritdoc
     */
    public function newObjectInstance()
    {
        $strClass = $this->strModelClass;
        return new $strClass();
    }

    /**
     * @inheritdoc
     */
    public function getAdminForm(Root $objModel)
    {
        return AdminFormgeneratorFactory::createByModel($objModel);
    }

    /**
     * @inheritdoc
     */
    public function onSave(Root $objModel, AdminFormgenerator $objForm)
    {
    }

    /**
     * @inheritdoc
     */
    public function onPersist(Root $objModel, array $arrObjects)
    {
        $objModel->updateObjectToDb();
    }

    /**
     * @param string $strKey
     * @return string
     */
    protected function getParam($strKey)
    {
        return Carrier::getInstance()->getParam($strKey);
    }

    /**
     * @param string $strText
     * @param string $strModule
     * @param array $arrParameters
     * @return string
     */
    protected function getLang($strText, $strModule, array $arrParameters = array())
    {
        return Lang::getInstance()->getLang($strText, $strModule, $arrParameters);
    }
}
