<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * Internal represenation of the blocks-wrapper element
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_blocks_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType text
     * @fieldLabel commons_title
     * @fieldReadonly
     *
     * @elementContentTitle
     */
    private $strBlocksName = "";

    /**
     * @return string
     */
    public function getStrBlocksName()
    {
        return $this->strBlocksName;
    }

    /**
     * @param string $strBlocksName
     */
    public function setStrBlocksName($strBlocksName)
    {
        $this->strBlocksName = $strBlocksName;
    }




}
