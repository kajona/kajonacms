<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Abstract entry which shares the common properties of an event and todo entry
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
abstract class class_entry_abstract implements interface_admin_listable, \Kajona\System\System\ModelInterface
{
    /**
     * @var string
     */
    protected $strCategory;

    /**
     * @var string
     */
    protected $strSystemId;

    /**
     * @var string
     */
    protected $strIcon;

    /**
     * @var string
     */
    protected $strDisplayName;

    /**
     * @var string
     */
    protected $strAdditionalInfo;

    /**
     * @var string
     */
    protected $strLongDescription;

    /**
     * @var \Kajona\System\System\Date
     */
    protected $objValidDate;

    /**
     * @return string
     */
    public function getStrCategory()
    {
        return $this->strCategory;
    }

    /**
     * @return string
     */
    public function getSystemid()
    {
        return $this->strSystemId;
    }

    /**
     * @return string
     */
    public function getStrSystemId()
    {
        return $this->strSystemId;
    }

    /**
     * @return string
     */
    public function getStrIcon()
    {
        return $this->strIcon;
    }

    /**
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return $this->strAdditionalInfo;
    }

    /**
     * @return string
     */
    public function getStrLongDescription()
    {
        return $this->strLongDescription;
    }

    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->strDisplayName;
    }

    /**
     * @param string $strSystemId
     */
    public function setStrSystemId($strSystemId)
    {
        $this->strSystemId = $strSystemId;
    }

    /**
     * @param string $strIcon
     */
    public function setStrIcon($strIcon)
    {
        $this->strIcon = $strIcon;
    }

    /**
     * @return \Kajona\System\System\Date
     */
    public function getObjValidDate()
    {
        return $this->objValidDate;
    }

    /**
     * @param string $strCategory
     */
    public function setStrCategory($strCategory)
    {
        $this->strCategory = $strCategory;
    }

    /**
     * @param string $strDisplayName
     */
    public function setStrDisplayName($strDisplayName)
    {
        $this->strDisplayName = $strDisplayName;
    }

    /**
     * @param string $strAdditionalInfo
     */
    public function setStrAdditionalInfo($strAdditionalInfo)
    {
        $this->strAdditionalInfo = $strAdditionalInfo;
    }

    /**
     * @param string $strLongDescription
     */
    public function setStrLongDescription($strLongDescription)
    {
        $this->strLongDescription = $strLongDescription;
    }

    /**
     * @param \Kajona\System\System\Date $objValidDate
     */
    public function setObjValidDate(\Kajona\System\System\Date $objValidDate = null)
    {
        $this->objValidDate = $objValidDate;
    }
}
