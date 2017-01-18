<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

namespace Kajona\Sourcecode\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\SystemModule;


/**
 * Installer to install a sourcecode-element to use in the portal
 *
 * @package element_sourcecode
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class InstallerElementSourcecode extends InstallerBase implements InstallerInterface {

	public function install() {
		//register the module
		$this->registerModule($this->objMetadata->getStrTitle(), _sourcecode_module_id_, "", "", $this->objMetadata->getStrVersion(), false);


		$strReturn = "";
		//Register the element
		$strReturn .= "Registering sourcecode-element...\n";
		//check, if not already existing
		$objElement = null;
		$objElement = PagesElement::getElement("sourcecode");
		if($objElement == null) {
			$objElement = new PagesElement();
			$objElement->setStrName("sourcecode");
			$objElement->setStrClassAdmin('ElementSourcecodeAdmin.php');
			$objElement->setStrClassPortal('ElementSourcecodePortal.php');
			$objElement->setIntCachetime(60);
			$objElement->setIntRepeat(0);
			$objElement->setStrVersion($this->objMetadata->getStrVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";

			if ($objElement->getStrVersion() < 5) {
				$strReturn .= "Updating element version!...\n";
				$objElement->setStrVersion("5.0");
				$objElement->updateObjectToDb();
			}
		}

		return $strReturn;
	}


	public function update() {
		$strReturn = "";

		$arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
		if ($arrModule["module_version"] == "5.0") {
			$strReturn .= "Updating 5.0 to 5.1...\n";
			$this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1");
			$this->updateElementVersion("sourcecode", "5.1");
		}
		$arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
		if ($arrModule["module_version"] == "5.1") {
			$strReturn .= "Updating 5.1 to 6.2...\n";
			$this->updateModuleVersion($this->objMetadata->getStrTitle(), "6.2");
			$this->updateElementVersion("sourcecode", "6.2");
		}

		return $strReturn;
	}
	
	


}
