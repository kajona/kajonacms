<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A single value holder for a single block
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 */
class class_template_block_container
{

    private $strName = "";
    private $strType = "";
    private $strTag = "";
    private $strContent = "";
    private $strFullSection = "";

    private $arrBlocks = array();
    private $arrPlaceholder = array();

    /**
     * class_template_block_container constructor.
     *
     * @param string $strType
     * @param string $strContent
     * @param string $strName
     * @param string $strTag
     */
    public function __construct($strType, $strName, $strTag, $strContent, $strFullSection)
    {
        $this->strType = $strType;
        $this->strContent = $strContent;
        $this->strName = $strName;
        $this->strTag = $strTag;
        $this->strFullSection = $strFullSection;
    }

    /**
     * @return class_template_block_container[]
     */
    public function getArrBlocks()
    {
        return $this->arrBlocks;
    }

    /**
     * @param array $arrBlocks
     */
    public function setArrBlocks($arrBlocks)
    {
        $this->arrBlocks = $arrBlocks;
    }

    /**
     * @return string
     */
    public function getStrType()
    {
        return $this->strType;
    }

    /**
     * @param string $strType
     */
    public function setStrType($strType)
    {
        $this->strType = $strType;
    }

    /**
     * @return string
     */
    public function getStrContent()
    {
        return $this->strContent;
    }

    /**
     * @param string $strContent
     */
    public function setStrContent($strContent)
    {
        $this->strContent = $strContent;
    }

    /**
     * @return string
     */
    public function getStrFullSection()
    {
        return $this->strFullSection;
    }

    /**
     * @param string $strFullSection
     */
    public function setStrFullSection($strFullSection)
    {
        $this->strFullSection = $strFullSection;
    }

    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    /**
     * @param string $strName
     */
    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @return string
     */
    public function getStrTag()
    {
        return $this->strTag;
    }

    /**
     * @param string $strTag
     */
    public function setStrTag($strTag)
    {
        $this->strTag = $strTag;
    }

    /**
     * @return array
     */
    public function getArrPlaceholder()
    {
        return $this->arrPlaceholder;
    }

    /**
     * @param array $arrPlaceholder
     */
    public function setArrPlaceholder($arrPlaceholder)
    {
        $this->arrPlaceholder = $arrPlaceholder;
    }



}
