<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


namespace Kajona\Pages\System;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Exception;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistRestriction;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\TemplateBlockContainer;


/**
 * Model for a element. This is the "raw"-element, not the element on a page
 *
 * @author sidler@mulchprod.de
 * @targetTable element.element_id
 *
 * @module pages_content
 * @moduleId _pages_content_modul_id_
 *
 * @blockFromAutosave
 */
class PagesElement extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, AdminListableInterface
{

    /**
     * @var string
     * @tableColumn element.element_name
     * @tableColumnDatatype char254
     * @tableColumnIndex
     * @listOrder
     *
     * @fieldMandatory
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel commons_name
     *
     * @addSearchIndex
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn element.element_class_portal
     * @tableColumnDatatype char254
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     *
     * @addSearchIndex
     */
    private $strClassPortal = "";

    /**
     * @var string
     * @tableColumn element.element_class_admin
     * @tableColumnDatatype char254
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     *
     * @addSearchIndex
     */
    private $strClassAdmin = "";

    /**
     * @var int
     * @tableColumn element.element_repeat
     * @tableColumnDatatype int
     * @fieldType Kajona\System\Admin\Formentries\FormentryYesno
     */
    private $intRepeat = "";

    /**
     * @var int
     * @tableColumn element.element_cachetime
     * @tableColumnDatatype int
     * @fieldMandatory
     * @fieldValidator Kajona\System\System\Validators\NumericValidator
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     */
    private $intCachetime = "-1";

    /**
     * @var string
     * @tableColumn element.element_version
     * @tableColumnDatatype char20
     */
    private $strVersion = "";

    /**
     * @var string
     * @tableColumn element.element_config1
     * @tableColumnDatatype char254
     */
    private $strConfigVal1 = "";

    /**
     * @var string
     * @tableColumn element.element_config2
     * @tableColumnDatatype char254
     */
    private $strConfigVal2 = "";

    /**
     * @var string
     * @tableColumn element.element_config3
     * @tableColumnDatatype char254
     */
    private $strConfigVal3 = "";

    /**
     * @return bool
     */
    public function deleteObjectFromDatabase()
    {

        //delete elements in the database
        $arrElements = $this->objDB->getPArray("SELECT page_element_id FROM "._dbprefix_."page_element WHERE page_element_ph_element = ?", array($this->getStrName()));
        foreach ($arrElements as $arrOneRow) {
            $objElement = new PagesPageelement($arrOneRow["page_element_id"]);
            $objElement->deleteObjectFromDatabase();
        }

        parent::deleteObjectFromDatabase();
    }


    /**
     * @return bool
     */
    public function rightView()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function rightEdit()
    {
        if($this->getStrName() == "blocks" || $this->getStrName() == "block") {
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function rightDelete()
    {
        if($this->getStrName() == "blocks" || $this->getStrName() == "block") {
            return false;
        }
        return parent::rightDelete();
    }


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        $strName = Carrier::getInstance()->getObjLang()->getLang("element_".$this->getStrName()."_name", "elements");
        if ($strName == "!element_".$this->getStrName()."_name!") {
            $strName = $this->getStrName();
        }
        else {
            $strName .= " (".$this->getStrName().")";
        }
        return $strName;
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_dot";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return " V ".$this->getStrVersion()." (".($this->getIntCachetime() == "-1" ? "<b>".$this->getIntCachetime()."</b>" : $this->getIntCachetime()).")";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        $objAdminInstance = $this->getAdminElementInstance();
        return $objAdminInstance->getElementDescription();
    }

    /**
     * Returns the element using the given element-name
     *
     * @param string $strName
     *
     * @return PagesElement
     */
    public static function getElement($strName)
    {
        $objORM = new OrmObjectlist();
        $objORM->addWhereRestriction(new OrmObjectlistRestriction("AND element_name = ?", array($strName)));
        return $objORM->getSingleObject(get_called_class());
    }


    /**
     * Factory method, creates an instance of the admin-element represented by this page-element.
     * The admin-element won't get initialized by a systemid, so you shouldn't retrieve
     * it for further usings.
     *
     * @throws Exception
     * @return AdminElementInterface|ElementAdmin An instance of the admin-class linked by the current element
     */
    public function getAdminElementInstance()
    {
        $strFilename = Resourceloader::getInstance()->getPathForFile("/admin/elements/".$this->getStrClassAdmin());
        $objInstance = Classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin", null, array(), true);

        //legacy handling
        if ($objInstance == null) {
            $strFilename = Resourceloader::getInstance()->getPathForFile("/legacy/".$this->getStrClassAdmin());
            $objInstance = Classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");

        }

        if ($objInstance != null) {
            return $objInstance;
        }
        else {
            throw new Exception("element class ".$this->getStrClassAdmin()." not existing", Exception::$level_FATALERROR);
        }
    }

    /**
     * Factory method, creates an instance of the portal-element represented by this page-element.
     * The admin-element won't get initialized by a systemid, so you shouldn't retrieve
     * it for further usings.
     *
     * @throws Exception
     * @return PortalElementInterface|ElementPortal An instance of the portal-class linked by the current element
     */
    public function getPortalElementInstance()
    {
        $strFilename = Resourceloader::getInstance()->getPathForFile("/portal/elements/".$this->getStrClassPortal());
        $objInstance = Classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Portal\\ElementPortal", null, array(new PagesPageelement()), true);

        if ($objInstance == null) {
            //legacy handling
            $strFilename = Resourceloader::getInstance()->getPathForFile("/legacy/".$this->getStrClassPortal());
            $objInstance = Classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Portal\\ElementPortal", null, array(new PagesPageelement()), true);
        }


        //Build the class-name
        //and finally create the object
        if ($objInstance != null) {
            return $objInstance;
        }
        else {
            throw new Exception("element class ".$this->getStrClassPortal()." not existing", Exception::$level_FATALERROR);
        }
    }

    /**
     * Searches all elements from a given block currently not installed.
     * Useful to trigger/block new element actions.
     * @param TemplateBlockContainer $objBlock
     *
     * @return String[]
     */
    public static function getElementsNotInstalledFromBlock(TemplateBlockContainer $objBlock)
    {
        $arrNotPresent = array();
        foreach($objBlock->getArrPlaceholder() as $arrOnePlaceholder) {
            foreach($arrOnePlaceholder["elementlist"] as $arrElement) {
                if(PagesElement::getElement($arrElement["element"]) === null) {
                    $arrNotPresent[] = $arrElement["element"];
                }
            }
        }
        
        return $arrNotPresent;
    }
    

    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    /**
     * @return string
     */
    public function getStrClassPortal()
    {
        return $this->strClassPortal;
    }

    /**
     * @return string
     */
    public function getStrClassAdmin()
    {
        return $this->strClassAdmin;
    }

    /**
     * @return int
     */
    public function getIntRepeat()
    {
        return (int)$this->intRepeat;
    }

    /**
     * @return int
     */
    public function getIntCachetime()
    {
        return $this->intCachetime;
    }

    /**
     * Returns a readable representation of the current elements' name.
     * Searches the lang-file for an entry element_NAME_name.
     *
     * @return string
     * @deprecated use getStrDisplayName()
     * @fixme remove me
     */
    public function getStrReadableName()
    {
        $strName = Carrier::getInstance()->getObjLang()->getLang("element_".$this->getStrName()."_name", "elemente");
        if ($strName == "!element_".$this->getStrName()."_name!") {
            $strName = $this->getStrName();
        }
        return $strName;
    }


    /**
     * @param string $strName
     *
     * @return void
     */
    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @param string $strClassPortal
     *
     * @return void
     */
    public function setStrClassPortal($strClassPortal)
    {
        $this->strClassPortal = $strClassPortal;
    }

    /**
     * @param string $strClassAdmin
     *
     * @return void
     */
    public function setStrClassAdmin($strClassAdmin)
    {
        $this->strClassAdmin = $strClassAdmin;
    }

    /**
     * @param int $intRepeat
     *
     * @return void
     */
    public function setIntRepeat($intRepeat)
    {
        $this->intRepeat = $intRepeat;
    }

    /**
     * @param int $intCachetime
     *
     * @return void
     */
    public function setIntCachetime($intCachetime)
    {
        $this->intCachetime = $intCachetime;
    }

    /**
     * @return string
     */
    public function getStrVersion()
    {
        return $this->strVersion;
    }

    /**
     * @param string $strVersion
     *
     * @return void
     */
    public function setStrVersion($strVersion)
    {
        $this->strVersion = $strVersion;
    }

    /**
     * @param string $strConfigVal1
     *
     * @return void
     */
    public function setStrConfigVal1($strConfigVal1)
    {
        $this->strConfigVal1 = $strConfigVal1;
    }

    /**
     * @return string
     */
    public function getStrConfigVal1()
    {
        return $this->strConfigVal1;
    }

    /**
     * @param string $strConfigVal2
     *
     * @return void
     */
    public function setStrConfigVal2($strConfigVal2)
    {
        $this->strConfigVal2 = $strConfigVal2;
    }

    /**
     * @return string
     */
    public function getStrConfigVal2()
    {
        return $this->strConfigVal2;
    }

    /**
     * @param string $strConfigVal3
     *
     * @return void
     */
    public function setStrConfigVal3($strConfigVal3)
    {
        $this->strConfigVal3 = $strConfigVal3;
    }

    /**
     * @return string
     */
    public function getStrConfigVal3()
    {
        return $this->strConfigVal3;
    }

}
