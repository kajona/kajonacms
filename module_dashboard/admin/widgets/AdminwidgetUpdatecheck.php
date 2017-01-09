<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\Admin\Widgets;

use Kajona\Packagemanager\System\PackagemanagerManager;
use Kajona\System\System\Link;
use Kajona\System\System\SystemModule;

/**
 * A widget to check the availability of possible updates
 *
 * @package module_dashboard
 */
class AdminwidgetUpdatecheck extends Adminwidget implements AdminwidgetInterface
{

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct()
    {
        parent::__construct();
        //register the fields to be persisted and loaded
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm()
    {
        return "";
    }

    /**
     * This method is called, when the widget should generate it's content.
     * Return the complete content using the methods provided by the base class.
     * Do NOT use the toolkit right here!
     *
     * @return string
     */
    public function getWidgetOutput()
    {
        $strReturn = "";

        if (!SystemModule::getModuleByName("packagemanager")->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        $objManager = new PackagemanagerManager();
        $arrRemotePackages = $objManager->scanForUpdates();

        $strSystemVersion = "n.a.";
        if (isset($arrRemotePackages["system"])) {
            $strSystemVersion = $arrRemotePackages["system"];
        }

        $arrUpdates = array();
        $arrLocalPackages = $objManager->getAvailablePackages();
        foreach ($arrLocalPackages as $objOneMetadata) {
            if (isset($arrRemotePackages[$objOneMetadata->getStrTitle()])) {
                if ($arrRemotePackages[$objOneMetadata->getStrTitle()] != null && version_compare($arrRemotePackages[$objOneMetadata->getStrTitle()], $objOneMetadata->getStrVersion(), ">")) {
                    $arrUpdates[$objOneMetadata->getStrTitle()] = $arrRemotePackages[$objOneMetadata->getStrTitle()];
                }
            }
        }


        $strReturn .= $this->widgetText($this->getLang("sysinfo_kajona_version")." ".SystemModule::getModuleByName("system")->getStrVersion());
        $strReturn .= $this->widgetText($this->getLang("sysinfo_kajona_versionAvail")." ".$strSystemVersion);
        $strReturn .= $this->widgetSeparator();
        if (count($arrUpdates) > 0) {
            $strReturn .= $this->widgetText($this->getLang("updatecheck_versionAvail"));
        }
        foreach ($arrUpdates as $strPackage => $intVersion) {
            $strReturn .= $this->widgetText(Link::getLinkAdmin("packagemanager", "list", "&packagelist_filter=".$strPackage."&doFilter=1", $strPackage." (".$intVersion.")"));
        }


        return $strReturn;
    }

    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName()
    {
        return $this->getLang("updatecheck_name");
    }

}

