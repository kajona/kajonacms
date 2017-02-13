<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\Installer;

use Kajona\Flow\System\FlowConfig;
use Kajona\Flow\System\FlowStatus;
use Kajona\Flow\System\FlowTransition;
use Kajona\News\System\NewsNews;
use Kajona\System\System\SamplecontentInstallerInterface;

/**
 * Installer
 */
class InstallerSamplecontentFlow implements SamplecontentInstallerInterface
{
    public function isInstalled()
    {
        return false;
    }

    public function install()
    {
        /*
        $objFlow = FlowConfig::getByModelClass(NewsNews::class);

        $objStepA = new FlowStatus();
        $objStepA->setStrName("Step A");
        $objStepA->setStrIcon("icon_flag_red");
        $objStepA->updateObjectToDb($objFlow->getSystemid());

        $objStepB = new FlowStatus();
        $objStepB->setStrName("Step B");
        $objStepB->setStrIcon("icon_flag_blue");
        $objStepB->updateObjectToDb($objFlow->getSystemid());

        $objStepC = new FlowStatus();
        $objStepC->setStrName("Step C");
        $objStepC->setStrIcon("icon_flag_purple");
        $objStepC->updateObjectToDb($objFlow->getSystemid());

        $objStepD = new FlowStatus();
        $objStepD->setStrName("Step D");
        $objStepD->setStrIcon("icon_flag_green");
        $objStepD->updateObjectToDb($objFlow->getSystemid());

        $objStepTransition = new FlowTransition();
        $objStepTransition->setStrTargetStatus($objStepB->getSystemid());
        $objStepA->addTransition($objStepTransition);

        $objStepTransition = new FlowTransition();
        $objStepTransition->setStrTargetStatus($objStepC->getSystemid());
        $objStepB->addTransition($objStepTransition);

        $objStepTransition = new FlowTransition();
        $objStepTransition->setStrTargetStatus($objStepD->getSystemid());
        $objStepB->addTransition($objStepTransition);

        $objStepTransition = new FlowTransition();
        $objStepTransition->setStrTargetStatus($objStepD->getSystemid());
        $objStepC->addTransition($objStepTransition);
        */
    }

    public function setObjDb($objDb)
    {
    }

    public function setStrContentlanguage($strContentlanguage)
    {
    }

    public function getCorrespondingModule()
    {
        return "flow";
    }
}

