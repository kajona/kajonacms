<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Mediaplayer\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Class to handle the admin-stuff of the mediaplayer-element
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementMediaplayerAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType file
     * @fieldLabel mediaplayer_file
     * @fieldMandatory
     *
     * @elementContentTitle
     *
     * @addSearchIndex
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType file
     * @fieldLabel mediaplayer_preview
     *
     * @addSearchIndex
     */
    private $strChar2;

    /**
     * @var string
     * @tableColumn element_universal.char3
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_mediaplayer
     */
    private $strChar3;

    /**
     * @var string
     * @tableColumn element_universal.int1
     *
     * @fieldType text
     * @fieldLabel mediaplayer_width
     */
    private $intInt1;

    /**
     * @var string
     * @tableColumn element_universal.int2
     *
     * @fieldType text
     * @fieldLabel mediaplayer_height
     */
    private $intInt2;


    /**
     * @param string $strChar3
     */
    public function setStrChar3($strChar3) {
        $this->strChar3 = $strChar3;
    }

    /**
     * @return string
     */
    public function getStrChar3() {
        return $this->strChar3;
    }

    /**
     * @param string $strChar2
     */
    public function setStrChar2($strChar2) {
        $strChar2 = str_replace(_webpath_, "_webpath_", $strChar2);
        $this->strChar2 = $strChar2;
    }

    /**
     * @return string
     */
    public function getStrChar2() {
        return $this->strChar2;
    }

    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1) {
        $strChar1 = str_replace(_webpath_, "_webpath_", $strChar1);
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1() {
        return $this->strChar1;
    }

    /**
     * @param string $intInt2
     */
    public function setIntInt2($intInt2) {
        $this->intInt2 = $intInt2;
    }

    /**
     * @return string
     */
    public function getIntInt2() {
        return $this->intInt2;
    }

    /**
     * @param string $intInt1
     */
    public function setIntInt1($intInt1) {
        $this->intInt1 = $intInt1;
    }

    /**
     * @return string
     */
    public function getIntInt1() {
        return $this->intInt1;
    }



}
