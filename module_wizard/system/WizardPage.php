<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Model;

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
    public function persistObject($objInstance, array $arrObjects)
    {
        if ($objInstance instanceof Model) {
            $objInstance->updateObjectToDb();
        }
    }

    /**
     * @inheritdoc
     */
    public function newObjectInstance()
    {
        $strClass = $this->strModelClass;
        return new $strClass();
    }
}
