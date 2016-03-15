<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

namespace Kajona\Eventmanager\Admin\Elements;
use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Class representing the admin-part of the eventmanager element
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 * @targetTable element_universal.content_id
 */
class ElementEventmanagerAdmin extends ElementAdmin implements AdminElementInterface {


    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_eventmanager
     */
    private $strChar1;

    /**
     * @var int
     * @tableColumn element_universal.int1
     *
     * @fieldType dropdown
     * @fieldLabel eventmanager_order
     * @fieldDDValues [0 => eventmanager_order_desc],[1 => eventmanager_order_asc]
     */
    private $intInt1;

    /**
     * @var int
     * @tableColumn element_universal.int2
     *
     * @fieldType dropdown
     * @fieldLabel eventmanager_mode
     * @fieldDDValues [0 => eventmanager_mode_calendar],[1 => eventmanager_mode_list]
     */
    private $intInt2;

    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1) {
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1() {
        return $this->strChar1;
    }

    /**
     * @param int $intInt2
     */
    public function setIntInt2($intInt2) {
        $this->intInt2 = $intInt2;
    }

    /**
     * @return int
     */
    public function getIntInt2() {
        return $this->intInt2;
    }

    /**
     * @param int $intInt1
     */
    public function setIntInt1($intInt1) {
        $this->intInt1 = $intInt1;
    }

    /**
     * @return int
     */
    public function getIntInt1() {
        return $this->intInt1;
    }





}
