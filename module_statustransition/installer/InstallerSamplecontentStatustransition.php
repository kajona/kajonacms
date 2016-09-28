<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\Installer;

use Kajona\News\Admin\Elements\ElementNewsAdmin;
use Kajona\News\System\NewsCategory;
use Kajona\News\System\NewsFeed;
use Kajona\News\System\NewsNews;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\SamplecontentContentHelper;
use Kajona\Statustransition\System\StatustransitionFlow;
use Kajona\Statustransition\System\StatustransitionFlowStep;
use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\Rights;
use Kajona\System\System\SamplecontentInstallerInterface;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\UserGroup;

/**
 * Installer
 */
class InstallerSamplecontentStatustransition implements SamplecontentInstallerInterface
{
    public function isInstalled()
    {
        return StatustransitionFlow::getObjectCountFiltered() > 0;
    }

    public function install()
    {
        $objAdminGroup = UserGroup::getGroupByName("Admins");

        $objFlow = new StatustransitionFlow();
        $objFlow->setStrName("Workflow A");
        $objFlow->updateObjectToDb();

        $objStepA = new StatustransitionFlowStep();
        $objStepA->setStrName("Step A");
        $objStepA->setStrIcon("icon_flag_red");
        $objStepA->setStrUserGroup($objAdminGroup->getSystemid());
        $objStepA->updateObjectToDb($objFlow->getSystemid());

        $objStepB = new StatustransitionFlowStep();
        $objStepB->setStrName("Step B");
        $objStepB->setStrIcon("icon_flag_blue");
        $objStepB->setStrUserGroup($objAdminGroup->getSystemid());
        $objStepB->updateObjectToDb($objFlow->getSystemid());

        $objStepC = new StatustransitionFlowStep();
        $objStepC->setStrName("Step C");
        $objStepC->setStrIcon("icon_flag_purple");
        $objStepC->setStrUserGroup($objAdminGroup->getSystemid());
        $objStepC->updateObjectToDb($objFlow->getSystemid());

        $objStepD = new StatustransitionFlowStep();
        $objStepD->setStrName("Step D");
        $objStepD->setStrIcon("icon_flag_green");
        $objStepD->setStrUserGroup($objAdminGroup->getSystemid());
        $objStepD->updateObjectToDb($objFlow->getSystemid());

        $objStepA->setArrTransitions(array($objStepB));
        $objStepA->updateObjectToDb($objFlow->getSystemid());

        $objStepB->setArrTransitions(array($objStepC, $objStepD));
        $objStepB->updateObjectToDb($objFlow->getSystemid());

        $objStepC->setArrTransitions(array($objStepD));
        $objStepC->updateObjectToDb($objFlow->getSystemid());
    }

    public function setObjDb($objDb)
    {
    }

    public function setStrContentlanguage($strContentlanguage)
    {
    }

    public function getCorrespondingModule()
    {
        return "statustransition";
    }
}

